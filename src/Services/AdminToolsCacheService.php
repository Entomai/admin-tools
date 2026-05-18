<?php

namespace Botble\AdminTools\Services;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Services\ClearCacheService;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class AdminToolsCacheService
{
    public const TYPE_CLEAR_ALL = 'clear_all';

    public function __construct(protected ClearCacheService $clearCacheService) {}

    public function commands(): array
    {
        $commands = [
            'clear_cms_cache' => [
                'type' => 'clear_cms_cache',
                'title' => trans('core/base::cache.commands.clear_cms_cache.title'),
                'description' => trans('core/base::cache.commands.clear_cms_cache.description'),
                'icon' => 'ti ti-database',
                'color' => 'primary',
                'button_icon' => 'ti ti-trash',
                'button_label' => trans('core/base::cache.clear_button'),
                'show_size' => true,
            ],
            'refresh_compiled_views' => [
                'type' => 'refresh_compiled_views',
                'title' => trans('core/base::cache.commands.refresh_compiled_views.title'),
                'description' => trans('core/base::cache.commands.refresh_compiled_views.description'),
                'icon' => 'ti ti-file-code',
                'color' => 'warning',
                'button_icon' => 'ti ti-refresh',
                'button_label' => trans('core/base::cache.refresh_button'),
                'show_size' => false,
            ],
            'clear_config_cache' => [
                'type' => 'clear_config_cache',
                'title' => trans('core/base::cache.commands.clear_config_cache.title'),
                'description' => trans('core/base::cache.commands.clear_config_cache.description'),
                'icon' => 'ti ti-settings',
                'color' => 'info',
                'button_icon' => 'ti ti-refresh',
                'button_label' => trans('core/base::cache.clear_button'),
                'show_size' => false,
            ],
            'clear_route_cache' => [
                'type' => 'clear_route_cache',
                'title' => trans('core/base::cache.commands.clear_route_cache.title'),
                'description' => trans('core/base::cache.commands.clear_route_cache.description'),
                'icon' => 'ti ti-route',
                'color' => 'success',
                'button_icon' => 'ti ti-refresh',
                'button_label' => trans('core/base::cache.clear_button'),
                'show_size' => false,
            ],
        ];

        if (admin_tools_setting_bool('fast_cache_clear_log_enabled', false)) {
            $commands['clear_log'] = [
                'type' => 'clear_log',
                'title' => trans('core/base::cache.commands.clear_log.title'),
                'description' => trans('core/base::cache.commands.clear_log.description'),
                'icon' => 'ti ti-file-text',
                'color' => 'danger',
                'button_icon' => 'ti ti-trash',
                'button_label' => trans('core/base::cache.clear_button'),
                'show_size' => false,
            ];
        }

        return $commands;
    }

    public function types(): array
    {
        return array_merge([self::TYPE_CLEAR_ALL], array_keys($this->commands()));
    }

    public function clear(string $type): string
    {
        if ($type === self::TYPE_CLEAR_ALL) {
            foreach (array_keys($this->commands()) as $command) {
                $this->runCommand($command);
            }

            return trans('plugins/admin-tools::admin-tools.fast_cache_clear_all_success');
        }

        $this->runCommand($type);

        return trans("core/base::cache.commands.$type.success_msg");
    }

    public function cacheSize(): int
    {
        $cachePath = storage_path('framework/cache');

        if (! File::isDirectory($cachePath)) {
            return 0;
        }

        return $this->calculateDirectorySize($cachePath);
    }

    public function formattedCacheSize(): string
    {
        return BaseHelper::humanFilesize($this->cacheSize());
    }

    protected function runCommand(string $type): void
    {
        match ($type) {
            'clear_cms_cache' => $this->clearCmsCache(),
            'refresh_compiled_views' => $this->clearCacheService->clearCompiledViews(),
            'clear_config_cache' => $this->clearCacheService->clearConfig(),
            'clear_route_cache' => $this->clearCacheService->clearRoutesCache(),
            'clear_log' => admin_tools_setting_bool('fast_cache_clear_log_enabled', false)
                ? $this->clearCacheService->clearLogs()
                : throw new InvalidArgumentException(trans('plugins/admin-tools::admin-tools.fast_cache_invalid_type')),
            default => throw new InvalidArgumentException(trans('plugins/admin-tools::admin-tools.fast_cache_invalid_type')),
        };
    }

    protected function clearCmsCache(): void
    {
        $this->clearCacheService->clearFrameworkCache();
        $this->clearCacheService->clearGoogleFontsCache();
        $this->clearCacheService->clearPurifier();
        $this->clearCacheService->clearDebugbar();
    }

    protected function calculateDirectorySize(string $directory): int
    {
        $size = 0;

        foreach (File::glob(rtrim($directory, '/').'/*', GLOB_NOSORT) as $path) {
            $size += File::isFile($path) ? File::size($path) : $this->calculateDirectorySize($path);
        }

        return $size;
    }
}
