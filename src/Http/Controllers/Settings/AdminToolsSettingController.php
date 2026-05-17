<?php

namespace Botble\AdminTools\Http\Controllers\Settings;

use Botble\AdminTools\Forms\Settings\AdminToolsSettingForm;
use Botble\AdminTools\Http\Requests\Settings\AdminToolsSettingRequest;
use Botble\AdminTools\Services\AdminToolsUpdateService;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\Setting\Http\Controllers\SettingController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminToolsSettingController extends SettingController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('core/base::base.panel.others'));
    }

    public function edit(): View|string
    {
        $this->pageTitle(trans('plugins/admin-tools::admin-tools.settings_title'));

        return AdminToolsSettingForm::create()->renderForm();
    }

    public function update(AdminToolsSettingRequest $request): BaseHttpResponse
    {
        $data = $request->validated();
        $data = array_diff_key($data, array_flip($this->inactivePluginSettingKeys()));

        $data['admin_tools_hidden_header_hook_items'] = $request->input('admin_tools_hidden_header_hook_items', []);

        foreach ($this->activePluginArraySettings() as $key) {
            $data["admin_tools_$key"] = $request->input("admin_tools_$key", []);
        }

        return $this->performUpdate($data);
    }

    protected function inactivePluginSettingKeys(): array
    {
        $settings = [
            'ecommerce' => [
                'admin_tools_ecommerce_header_menu_enabled',
                'admin_tools_ecommerce_notifications_enabled',
                'admin_tools_ecommerce_notification_statuses',
            ],
            'contact' => [
                'admin_tools_contact_notifications_enabled',
                'admin_tools_contact_notification_statuses',
            ],
            'payment' => [
                'admin_tools_payment_notifications_enabled',
                'admin_tools_payment_notification_statuses',
            ],
            'hotel' => [
                'admin_tools_hotel_booking_notification_statuses',
            ],
        ];

        $inactive = [];

        foreach ($settings as $plugin => $keys) {
            if (! $this->isPluginActive($plugin)) {
                $inactive = array_merge($inactive, $keys);
            }
        }

        return $inactive;
    }

    protected function activePluginArraySettings(): array
    {
        $settings = array_keys(array_filter([
            'ecommerce_notification_statuses' => $this->isPluginActive('ecommerce'),
            'contact_notification_statuses' => $this->isPluginActive('contact'),
            'payment_notification_statuses' => $this->isPluginActive('payment'),
            'hotel_booking_notification_statuses' => $this->isPluginActive('hotel'),
        ]));

        foreach (admin_tools_active_notification_setting_definitions() as $definition) {
            $key = $definition['status_key'] ?? '';

            if ($key && ($definition['status_choices'] ?? [])) {
                $settings[] = $key;
            }
        }

        return array_values(array_unique($settings));
    }

    protected function isPluginActive(string $plugin): bool
    {
        return function_exists('is_plugin_active') && is_plugin_active($plugin);
    }

    public function updateSelected(Request $request, AdminToolsUpdateService $updateService): RedirectResponse
    {
        $request->validate([
            'updates' => ['nullable', 'array'],
            'updates.*' => ['string'],
        ]);

        $result = $updateService->updateSelected($request->input('updates', []));

        $redirect = redirect()->back();

        if ($result['updated']) {
            $redirect->with('success_msg', implode('<br>', $result['updated']));
        }

        if ($result['failed']) {
            $redirect->with('error_msg', implode('<br>', $result['failed']));
        }

        return $redirect;
    }
}
