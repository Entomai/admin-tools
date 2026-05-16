<?php

namespace Botble\AdminTools\Forms;

use Botble\ACL\Models\User;
use Botble\AdminTools\Http\Requests\AdminAppearancePreferenceRequest;
use Botble\Base\Facades\AdminAppearance;
use Botble\Base\Facades\AdminHelper;
use Botble\Base\Forms\FieldOptions\ColorFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffCheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\RadioFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\ColorField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\RadioField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Setting\Forms\SettingForm;

class AdminAppearancePreferenceForm extends SettingForm
{
    public function setup(): void
    {
        $user = $this->getModel();

        if (! $user instanceof User) {
            $user = auth()->user();
        }

        parent::setup();

        $languages = AdminHelper::getAdminLocales();

        $this
            ->setUrl(route('admin-tools.admin-appearance.update'))
            ->setValidatorClass(AdminAppearancePreferenceRequest::class)
            ->setSectionTitle(trans('plugins/admin-tools::admin-tools.admin_appearance_preferences_title'))
            ->setSectionDescription(trans('plugins/admin-tools::admin-tools.admin_appearance_preferences_description'));

        if (count($languages) > 1) {
            $this->add(
                admin_tools_admin_appearance_meta_key('locale'),
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.admin_locale'))
                    ->choices($languages)
                    ->selected(admin_tools_admin_appearance_form_value('locale', null, $user))
                    ->searchable()
            );
        }

        $this
            ->add(
                admin_tools_admin_appearance_meta_key('locale_direction'),
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.admin_locale_direction'))
                    ->choices([
                        'ltr' => trans('core/setting::setting.locale_direction_ltr'),
                        'rtl' => trans('core/setting::setting.locale_direction_rtl'),
                    ])
                    ->selected(admin_tools_admin_appearance_form_value('locale_direction', null, $user))
            )
            ->add(
                admin_tools_admin_appearance_meta_key('layout'),
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.layout'))
                    ->choices(AdminAppearance::getLayouts())
                    ->selected(admin_tools_admin_appearance_form_value('layout', null, $user))
            )
            ->add(
                admin_tools_admin_appearance_meta_key('container_width'),
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.container_width.title'))
                    ->choices(AdminAppearance::getContainerWidths())
                    ->selected(admin_tools_admin_appearance_form_value('container_width', null, $user))
            )
            ->add(
                admin_tools_admin_appearance_meta_key('show_menu_item_icon'),
                OnOffCheckboxField::class,
                OnOffCheckboxFieldOption::make()
                    ->label(trans('plugins/admin-tools::admin-tools.admin_appearance_show_menu_item_icon'))
                    ->value(admin_tools_admin_appearance_form_value('show_menu_item_icon', null, $user))
                    ->helperText(trans('plugins/admin-tools::admin-tools.admin_appearance_show_menu_item_icon_help'))
            )
            ->add(
                admin_tools_admin_appearance_meta_key('theme_mode'),
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.theme_mode'))
                    ->choices([
                        'light' => trans('core/setting::setting.admin_appearance.light'),
                        'dark' => trans('core/setting::setting.admin_appearance.dark'),
                    ])
                    ->selected(admin_tools_admin_appearance_form_value('theme_mode', null, $user))
            );

        foreach ([
            'primary_color' => 'primary_color',
            'secondary_color' => 'secondary_color',
            'heading_color' => 'heading_color',
            'text_color' => 'text_color',
            'link_color' => 'link_color',
            'link_hover_color' => 'link_hover_color',
        ] as $key => $translationKey) {
            $value = admin_tools_admin_appearance_form_value($key, null, $user);

            $this->add(
                admin_tools_admin_appearance_meta_key($key),
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans("core/setting::setting.admin_appearance.form.$translationKey"))
                    ->value($key === 'heading_color' && $value === 'inherit' ? '#182433' : $value)
            );
        }
    }
}
