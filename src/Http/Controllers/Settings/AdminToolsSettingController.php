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
        $data['admin_tools_hidden_header_hook_items'] = $request->input('admin_tools_hidden_header_hook_items', []);

        return $this->performUpdate($data);
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
