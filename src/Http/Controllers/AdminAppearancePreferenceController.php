<?php

namespace Botble\AdminTools\Http\Controllers;

use Botble\AdminTools\Forms\AdminAppearancePreferenceForm;
use Botble\AdminTools\Http\Requests\AdminAppearancePreferenceRequest;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;

class AdminAppearancePreferenceController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/admin-tools::admin-tools.admin_appearance_preferences_title'), route('admin-tools.admin-appearance.edit'));
    }

    public function edit(): string
    {
        abort_unless(admin_tools_setting_bool('admin_appearance_per_user_enabled', false), 404);

        $this->pageTitle(trans('plugins/admin-tools::admin-tools.admin_appearance_preferences_title'));

        return AdminAppearancePreferenceForm::createFromModel(auth()->user())->renderForm();
    }

    public function update(AdminAppearancePreferenceRequest $request): BaseHttpResponse
    {
        abort_unless(admin_tools_setting_bool('admin_appearance_per_user_enabled', false), 404);

        $user = $request->user();
        $data = $request->validated();
        $data[admin_tools_admin_appearance_meta_key('show_menu_item_icon')] = $request->boolean(admin_tools_admin_appearance_meta_key('show_menu_item_icon')) ? '1' : '0';

        foreach (admin_tools_admin_appearance_fields() as $key) {
            $inputKey = admin_tools_admin_appearance_meta_key($key);

            if ($key === 'locale' && ! array_key_exists($inputKey, $data)) {
                continue;
            }

            $metaKey = $key === 'theme_mode' ? 'theme_mode' : $inputKey;
            $value = $data[$inputKey] ?? null;

            if ($value !== null) {
                $user->setMeta($metaKey, $value);
            }
        }

        if (method_exists($user, 'loadMeta')) {
            $user->loadMeta(true);
        }

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('admin-tools.admin-appearance.edit'))
            ->setNextUrl(route('admin-tools.admin-appearance.edit'))
            ->setMessage(trans('plugins/admin-tools::admin-tools.admin_appearance_save_success'));
    }
}
