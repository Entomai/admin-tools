<?php

namespace Botble\AdminTools\Services;

use Botble\Base\Exceptions\RequiresLicenseActivatedException;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Core;
use Botble\Base\Supports\Helper;
use Botble\Base\Supports\Zipper;
use Botble\PluginManagement\Http\Controllers\MarketplaceController;
use Botble\PluginManagement\Services\MarketplaceService;
use Botble\PluginManagement\Services\PluginService;
use Botble\Theme\Facades\Manager as ThemeManager;
use Botble\Theme\Services\ThemeService;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class AdminToolsUpdateService
{
    protected const CACHE_KEY = 'admin_tools_update_state';

    public function __construct(
        protected MarketplaceService $marketplaceService,
        protected PluginService $pluginService,
        protected ThemeService $themeService
    ) {}

    public function getState(bool $force = false): array
    {
        if ($force) {
            $this->clearCache();
        }

        return Cache::remember(self::CACHE_KEY, now()->addMinutes(15), fn (): array => $this->check());
    }

    public function updateSelected(array $keys): array
    {
        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        $keys = array_values(array_unique(array_filter($keys)));

        if ($keys === []) {
            return [
                'updated' => [],
                'failed' => [
                    trans('plugins/admin-tools::admin-tools.update_selected_required'),
                ],
            ];
        }

        $state = $this->getState();
        $updates = collect($state['items'] ?? [])->keyBy('key');

        $updated = [];
        $failed = [];

        foreach ($keys as $key) {
            $item = $updates->get($key);

            if (! $item) {
                $failed[] = trans('plugins/admin-tools::admin-tools.update_item_unavailable', ['item' => $key]);

                continue;
            }

            try {
                $message = $this->installUpdate($item);
                $updated[] = $message ?: trans('plugins/admin-tools::admin-tools.update_success_item', [
                    'item' => $item['name'],
                ]);
            } catch (Throwable $exception) {
                $failed[] = trans('plugins/admin-tools::admin-tools.update_failed_item', [
                    'item' => $item['name'],
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        $this->clearCache();

        return compact('updated', 'failed');
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('plugin_update_data');
        Cache::forget('entomai_private_plugin_update_data');
    }

    protected function check(): array
    {
        $messages = [];
        $items = [];

        foreach ([
            'marketplace_plugins' => fn (): array => $this->checkMarketplacePluginUpdates(),
            'marketplace_themes' => fn (): array => $this->checkMarketplaceThemeUpdates(),
        ] as $provider => $callback) {
            try {
                $items = array_merge($items, $callback());
            } catch (Throwable $exception) {
                $messages[] = trans('plugins/admin-tools::admin-tools.update_provider_failed', [
                    'provider' => trans("plugins/admin-tools::admin-tools.update_provider_$provider"),
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        $items = apply_filters(ADMIN_TOOLS_FILTER_UPDATE_ITEMS, $items);
        $items = $this->normalizeItems(is_array($items) ? $items : []);

        return [
            'items' => $items,
            'messages' => $messages,
            'checked_at' => now()->toDateTimeString(),
            'counts' => [
                'plugins' => count(array_filter($items, fn (array $item): bool => $item['type'] === 'plugin')),
                'themes' => count(array_filter($items, fn (array $item): bool => $item['type'] === 'theme')),
                'total' => count($items),
            ],
        ];
    }

    protected function checkMarketplacePluginUpdates(): array
    {
        if (
            ! config('packages.plugin-management.general.enable_marketplace_feature', true)
            || ! Route::has('plugins.marketplace.ajax.update')
            || ! $this->userCan('plugins.marketplace')
        ) {
            return [];
        }

        $plugins = $this->installedPluginsByProductId();

        if ($plugins === []) {
            return [];
        }

        $payload = $this->marketplaceCheck(
            collect($plugins)->mapWithKeys(fn (array $plugin, string $productId): array => [
                $productId => $plugin['version'],
            ])->all()
        );

        return collect($payload)
            ->map(function (array $update) use ($plugins): ?array {
                $productId = (string) Arr::get($update, 'name', Arr::get($update, 'product_id', ''));
                $plugin = $plugins[$productId] ?? null;

                if (! $plugin) {
                    return null;
                }

                $latestVersion = $this->getLatestVersionFromPayload($update);

                return [
                    'key' => sprintf('plugin:botble-marketplace:%s', $plugin['path']),
                    'type' => 'plugin',
                    'source' => 'botble_marketplace',
                    'source_label' => trans('plugins/admin-tools::admin-tools.update_source_botble_marketplace'),
                    'slug' => $plugin['path'],
                    'product_id' => $productId,
                    'update_id' => (string) Arr::get($update, 'id', ''),
                    'name' => $plugin['name'],
                    'current_version' => $plugin['version'],
                    'latest_version' => $latestVersion,
                    'summary' => Arr::get($update, 'summary') ?: Arr::get($update, 'message'),
                    'released_at' => Arr::get($update, 'released_at') ?: Arr::get($update, 'last_updated_at'),
                    'icon' => 'ti ti-plug',
                    'url' => Route::has('plugins.index') ? route('plugins.index') : null,
                    'payload' => $update,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function checkMarketplaceThemeUpdates(): array
    {
        if (
            ! config('packages.plugin-management.general.enable_marketplace_feature', true)
            || ! $this->userCan('theme.index')
        ) {
            return [];
        }

        $themes = $this->installedThemesByProductId();

        if ($themes === []) {
            return [];
        }

        $payload = $this->marketplaceCheck(
            collect($themes)->mapWithKeys(fn (array $theme, string $productId): array => [
                $productId => $theme['version'],
            ])->all()
        );

        return collect($payload)
            ->map(function (array $update) use ($themes): ?array {
                $productId = (string) Arr::get($update, 'name', Arr::get($update, 'product_id', ''));
                $theme = $themes[$productId] ?? null;

                if (! $theme) {
                    return null;
                }

                return [
                    'key' => sprintf('theme:botble-marketplace:%s', $theme['path']),
                    'type' => 'theme',
                    'source' => 'botble_marketplace_theme',
                    'source_label' => trans('plugins/admin-tools::admin-tools.update_source_botble_marketplace'),
                    'slug' => $theme['path'],
                    'product_id' => $productId,
                    'update_id' => (string) Arr::get($update, 'id', ''),
                    'name' => $theme['name'],
                    'current_version' => $theme['version'],
                    'latest_version' => $this->getLatestVersionFromPayload($update),
                    'summary' => Arr::get($update, 'summary') ?: Arr::get($update, 'message'),
                    'released_at' => Arr::get($update, 'released_at') ?: Arr::get($update, 'last_updated_at'),
                    'icon' => 'ti ti-palette',
                    'url' => Route::has('theme.index') ? route('theme.index') : null,
                    'payload' => $update,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function marketplaceCheck(array $products): array
    {
        if ($products === []) {
            return [];
        }

        $response = $this->marketplaceService->callApi('post', '/products/check-update', [
            'products' => $products,
        ]);

        $payload = $this->responsePayload($response);

        return is_array($payload['data'] ?? null) ? $payload['data'] : [];
    }

    protected function installUpdate(array $item): ?string
    {
        $customResult = apply_filters(ADMIN_TOOLS_FILTER_INSTALL_UPDATE_ITEM, null, $item);

        if ($customResult !== null) {
            if (is_array($customResult) && ($customResult['error'] ?? false)) {
                throw new RuntimeException($customResult['message'] ?? trans('plugins/admin-tools::admin-tools.update_failed'));
            }

            return is_array($customResult)
                ? ($customResult['message'] ?? null)
                : (string) $customResult;
        }

        return match ($item['source']) {
            'botble_marketplace' => $this->installMarketplacePluginUpdate($item),
            'botble_marketplace_theme' => $this->installMarketplaceThemeUpdate($item),
            default => throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_source_not_supported')),
        };
    }

    protected function installMarketplacePluginUpdate(array $item): ?string
    {
        if (! $this->userCan('plugins.marketplace')) {
            throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_permission_denied'));
        }

        $updateId = (string) ($item['update_id'] ?? '');
        $plugin = (string) ($item['slug'] ?? '');

        if ($updateId === '' || $plugin === '') {
            throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_item_missing_payload'));
        }

        $response = app(MarketplaceController::class)->update($updateId, $plugin);
        $data = $response->getData(true);

        if ($data['error'] ?? false) {
            throw new RuntimeException($data['message'] ?? trans('plugins/admin-tools::admin-tools.update_failed'));
        }

        return $data['message'] ?? null;
    }

    protected function installMarketplaceThemeUpdate(array $item): ?string
    {
        if (! $this->userCan('theme.index')) {
            throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_permission_denied'));
        }

        $theme = (string) ($item['slug'] ?? '');
        $updateId = (string) ($item['update_id'] ?? '');

        if ($theme === '' || $updateId === '') {
            throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_item_missing_payload'));
        }

        $workPath = storage_path('app/admin-tools/theme-updater/'.Str::slug($theme).'/'.now()->format('YmdHis'));
        $extractPath = $workPath.'/extract';

        try {
            $zipPath = $this->downloadMarketplacePackage($updateId, $theme, $workPath);

            $this->validateZip($zipPath);

            if (! (new Zipper)->extract($zipPath, $extractPath)) {
                throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_zip_extract_failed'));
            }

            $packageRoot = $this->findThemePackageRoot($extractPath, $item);
            $backupPath = $this->backupTheme($theme);

            try {
                $this->replaceTheme($theme, $packageRoot);
                ThemeManager::refreshThemes();

                $published = $this->themeService->publishAssets($theme);

                if ($published['error']) {
                    throw new RuntimeException($published['message']);
                }
            } catch (Throwable $exception) {
                $this->restoreThemeBackup($theme, $backupPath);
                ThemeManager::refreshThemes();

                throw $exception;
            }

            File::deleteDirectory($backupPath);
            ThemeManager::refreshThemes();
            Helper::clearCache();

            return trans('plugins/admin-tools::admin-tools.update_theme_success', [
                'theme' => $item['name'],
                'version' => $item['latest_version'],
            ]);
        } finally {
            File::deleteDirectory($workPath);
        }
    }

    protected function responsePayload(Response|JsonResponse $response): array
    {
        if ($response instanceof JsonResponse) {
            return $response->getData(true);
        }

        return $response->json() ?: [];
    }

    protected function installedPluginsByProductId(): array
    {
        $plugins = [];

        foreach (BaseHelper::scanFolder(plugin_path()) as $path) {
            $plugin = $this->pluginService->getPluginInfo($path);
            $productId = (string) Arr::get($plugin, 'id', '');

            if ($productId === '') {
                continue;
            }

            $plugins[$productId] = [
                'path' => $path,
                'name' => (string) Arr::get($plugin, 'name', Str::headline($path)),
                'version' => (string) Arr::get($plugin, 'version', '0.0.0'),
                'manifest' => $plugin,
            ];
        }

        return $plugins;
    }

    protected function installedThemesByProductId(): array
    {
        $themes = [];

        foreach (ThemeManager::getThemes() as $path => $theme) {
            $productId = (string) Arr::get($theme, 'id', '');
            $version = (string) Arr::get($theme, 'version', '');

            if ($productId === '' || $version === '') {
                continue;
            }

            $themes[$productId] = [
                'path' => $path,
                'name' => (string) Arr::get($theme, 'name', Str::headline($path)),
                'version' => $version,
                'manifest' => $theme,
            ];
        }

        return $themes;
    }

    protected function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item) || blank($item['key'] ?? null) || blank($item['name'] ?? null)) {
                continue;
            }

            $type = in_array($item['type'] ?? null, ['plugin', 'theme'], true) ? $item['type'] : 'plugin';

            $normalized[] = [
                ...$item,
                'key' => (string) $item['key'],
                'type' => $type,
                'type_label' => trans("plugins/admin-tools::admin-tools.update_type_$type"),
                'source' => (string) ($item['source'] ?? 'custom'),
                'source_label' => (string) ($item['source_label'] ?? trans('plugins/admin-tools::admin-tools.update_source_custom')),
                'slug' => (string) ($item['slug'] ?? ''),
                'current_version' => (string) ($item['current_version'] ?? ''),
                'latest_version' => (string) ($item['latest_version'] ?? ''),
                'summary' => $item['summary'] ?? null,
                'released_at' => $item['released_at'] ?? null,
                'icon' => $item['icon'] ?? ($type === 'theme' ? 'ti ti-palette' : 'ti ti-plug'),
                'url' => $item['url'] ?? null,
            ];
        }

        usort($normalized, fn (array $first, array $second): int => [
            'plugin' => 10,
            'theme' => 20,
        ][$first['type']] <=> [
            'plugin' => 10,
            'theme' => 20,
        ][$second['type']] ?: strcasecmp($first['name'], $second['name']));

        return $normalized;
    }

    protected function getLatestVersionFromPayload(array $payload): string
    {
        return (string) (
            Arr::get($payload, 'latest_version')
            ?: Arr::get($payload, 'version')
            ?: Arr::get($payload, 'new_version')
            ?: ''
        );
    }

    protected function downloadMarketplacePackage(string $marketplaceProductId, string $name, string $workPath): string
    {
        $core = Core::make();
        $licenseFilePath = $core->getLicenseFilePath();

        if (! File::exists($licenseFilePath)) {
            throw new RequiresLicenseActivatedException;
        }

        $coreData = $core->getCoreFileData();
        $response = $this->marketplaceService->callApi('post', '/products/'.$marketplaceProductId.'/download', [
            'license_url' => $coreData['apiUrl'],
            'license_api_key' => $coreData['apiKey'],
            'license_file' => $core->getLicenseFile(),
        ]);

        if ($response instanceof JsonResponse) {
            $payload = $response->getData(true);

            throw new RuntimeException($payload['message'] ?? trans('plugins/admin-tools::admin-tools.update_download_failed'));
        }

        File::ensureDirectoryExists($workPath, 0775);

        $zipPath = $workPath.'/'.Str::slug($name).'.zip';

        File::put($zipPath, $response->body());

        if (! File::isFile($zipPath) || File::size($zipPath) === 0) {
            throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_download_empty'));
        }

        return $zipPath;
    }

    protected function validateZip(string $zipPath): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_zip_extension_required'));
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_zip_invalid'));
        }

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $path = str_replace('\\', '/', (string) $zip->getNameIndex($i));

                if (
                    Str::startsWith($path, ['/', '../'])
                    || str_contains($path, '/../')
                    || preg_match('/^[A-Za-z]:\//', $path)
                ) {
                    throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_zip_unsafe'));
                }
            }
        } finally {
            $zip->close();
        }
    }

    protected function findThemePackageRoot(string $extractPath, array $item): string
    {
        foreach ($this->themeJsonFiles($extractPath) as $themeJson) {
            $manifest = BaseHelper::getFileData($themeJson);

            if ($this->themePackageMatches($manifest, $item)) {
                return dirname($themeJson);
            }
        }

        throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_theme_manifest_mismatch'));
    }

    protected function themeJsonFiles(string $extractPath): array
    {
        $files = [];

        foreach (File::allFiles($extractPath) as $file) {
            if ($file->getFilename() !== 'theme.json') {
                continue;
            }

            if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'__MACOSX'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        return $files;
    }

    protected function themePackageMatches(array $manifest, array $item): bool
    {
        $installedTheme = ThemeManager::getThemes()[$item['slug']] ?? [];
        $installedId = (string) Arr::get($installedTheme, 'id', $item['product_id'] ?? '');
        $packageId = (string) Arr::get($manifest, 'id', '');
        $installedNamespace = (string) Arr::get($installedTheme, 'namespace', '');
        $packageNamespace = (string) Arr::get($manifest, 'namespace', '');
        $packageVersion = (string) Arr::get($manifest, 'version', '');
        $currentVersion = (string) ($item['current_version'] ?? '');
        $expectedVersion = (string) ($item['latest_version'] ?? '');

        $matches = ($installedId !== '' && $packageId !== '' && $installedId === $packageId)
            || ($installedNamespace !== '' && $packageNamespace !== '' && $installedNamespace === $packageNamespace);

        if (! $matches && $installedId === '') {
            $matches = $this->normalizeThemeName((string) Arr::get($manifest, 'name', '')) === $this->normalizeThemeName($item['slug']);
        }

        if (! $matches || $packageVersion === '') {
            return false;
        }

        if ($currentVersion !== '' && version_compare($this->normalizeVersion($packageVersion), $this->normalizeVersion($currentVersion), '<=')) {
            throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_theme_not_newer'));
        }

        if ($expectedVersion !== '' && version_compare($this->normalizeVersion($packageVersion), $this->normalizeVersion($expectedVersion), '<')) {
            throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_theme_older_than_expected'));
        }

        return true;
    }

    protected function backupTheme(string $theme): string
    {
        $backupPath = storage_path('app/admin-tools/theme-updater/backups/'.Str::slug($theme).'-'.now()->format('YmdHis'));

        File::ensureDirectoryExists(dirname($backupPath), 0775);

        if (! File::copyDirectory(theme_path($theme), $backupPath)) {
            throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_theme_backup_failed'));
        }

        return $backupPath;
    }

    protected function replaceTheme(string $theme, string $packageRoot): void
    {
        $destination = theme_path($theme);

        if (File::isDirectory($destination) && ! File::deleteDirectory($destination)) {
            throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_theme_remove_failed'));
        }

        File::ensureDirectoryExists($destination, 0775);

        if (! File::copyDirectory($packageRoot, $destination)) {
            throw new RuntimeException(trans('plugins/admin-tools::admin-tools.update_theme_copy_failed'));
        }
    }

    protected function restoreThemeBackup(string $theme, string $backupPath): void
    {
        $destination = theme_path($theme);

        if (File::isDirectory($destination)) {
            File::deleteDirectory($destination);
        }

        File::ensureDirectoryExists($destination, 0775);
        File::copyDirectory($backupPath, $destination);
    }

    protected function normalizeThemeName(string $name): string
    {
        return Str::slug(strtolower($name));
    }

    protected function normalizeVersion(string $version): string
    {
        return ltrim(trim($version), 'vV');
    }

    protected function userCan(string $permission): bool
    {
        $user = auth()->guard()->user();

        return $user && $user->hasPermission($permission);
    }
}
