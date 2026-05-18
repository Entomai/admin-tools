@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @if ($showStart)
        {!! Form::open(Arr::except($formOptions, ['template'])) !!}
    @endif

    @php
        do_action(BASE_ACTION_TOP_FORM_CONTENT_NOTIFICATION, request(), $form->getModel());

        $exclude ??= [];
        $customNotificationFields = $form->getFormOption('admin_tools_custom_notification_setting_fields', []);
        $customNotificationFields = is_array($customNotificationFields) ? $customNotificationFields : [];

        $tabs = [
            [
                'id' => 'admin-tools-settings-shortcuts',
                'label' => trans('plugins/admin-tools::admin-tools.settings_tab_shortcuts'),
                'fields' => [
                    'admin_tools_fast_menu_enabled',
                    'admin_tools_entomai_plugins_menu_enabled',
                    'admin_tools_ecommerce_header_menu_enabled',
                ],
            ],
            [
                'id' => 'admin-tools-settings-cache',
                'label' => trans('plugins/admin-tools::admin-tools.settings_tab_cache'),
                'fields' => [
                    'admin_tools_fast_cache_cleaner_enabled',
                    'admin_tools_fast_cache_clear_log_enabled',
                ],
            ],
            [
                'id' => 'admin-tools-settings-notifications',
                'label' => trans('plugins/admin-tools::admin-tools.settings_tab_notifications'),
                'fields' => array_merge(
                    [
                        'admin_tools_ecommerce_notifications_enabled',
                        'admin_tools_ecommerce_notification_statuses[]',
                        'admin_tools_contact_notifications_enabled',
                        'admin_tools_contact_notification_statuses[]',
                        'admin_tools_payment_notifications_enabled',
                        'admin_tools_payment_notification_statuses[]',
                        'admin_tools_hotel_booking_notification_statuses[]',
                    ],
                    $customNotificationFields,
                    [
                        'admin_tools_update_header_widget_enabled',
                    ]
                ),
            ],
            [
                'id' => 'admin-tools-settings-header',
                'label' => trans('plugins/admin-tools::admin-tools.settings_tab_header'),
                'fields' => [
                    'admin_tools_sticky_header_enabled',
                    'admin_tools_compact_brand_enabled',
                    'admin_tools_remove_logo_enabled',
                    'admin_tools_brand_custom_text',
                    'admin_tools_hide_view_website_button',
                    'admin_tools_hide_global_search',
                    'admin_tools_hide_botble_notification',
                    'admin_tools_hidden_header_hook_items[]',
                ],
            ],
            [
                'id' => 'admin-tools-settings-appearance',
                'label' => trans('plugins/admin-tools::admin-tools.settings_tab_appearance'),
                'fields' => [
                    'admin_tools_admin_appearance_per_user_enabled',
                ],
            ],
        ];

        $renderFields = function (array $names) use (&$fields, $exclude): string {
            $html = '';

            foreach ($names as $name) {
                foreach ($fields as $key => $field) {
                    if ($field->getName() !== $name || in_array($field->getName(), $exclude)) {
                        continue;
                    }

                    $html .= $field->render();
                    unset($fields[$key]);

                    break;
                }
            }

            return $html;
        };

        $renderRemainingFields = function () use (&$fields, $exclude): string {
            $html = '';

            foreach ($fields as $key => $field) {
                if (in_array($field->getName(), $exclude)) {
                    continue;
                }

                $html .= $field->render();
                unset($fields[$key]);
            }

            return $html;
        };
    @endphp

    <x-core-setting::section
        :title="$formOptions['section_title'] ?? ''"
        :description="$formOptions['section_description'] ?? ''"
    >
        @if ($showFields)
            <x-core::tab class="mb-3">
                @foreach ($tabs as $tab)
                    <x-core::tab.item
                        :id="$tab['id']"
                        :is-active="$loop->first"
                        :label="$tab['label']"
                    />
                @endforeach
            </x-core::tab>

            <x-core::tab.content>
                @foreach ($tabs as $tab)
                    <x-core::tab.pane
                        :id="$tab['id']"
                        :is-active="$loop->first"
                    >
                        {!! $renderFields($tab['fields']) !!}
                    </x-core::tab.pane>
                @endforeach
            </x-core::tab.content>

            @php
                $remainingFields = $renderRemainingFields();
            @endphp

            @if ($remainingFields)
                <div class="mt-3">
                    {!! $remainingFields !!}
                </div>
            @endif
        @endif
    </x-core-setting::section>

    {!! $form->getActionButtons() !!}

    @foreach ($form->getMetaBoxes() as $key => $metaBox)
        {!! $form->getMetaBox($key) !!}
    @endforeach

    @php
        do_action(BASE_ACTION_META_BOXES, 'advanced', $form->getModel());
        do_action(BASE_ACTION_META_BOXES, 'top', $form->getModel());
        do_action(BASE_ACTION_META_BOXES, 'side', $form->getModel());
    @endphp

    @yield('form_main_end')

    @if ($showEnd)
        {!! Form::close() !!}
    @endif

    @yield('form_end')
@endsection

@pushif($form->getValidatorClass(), 'footer')
{!! $form->renderValidatorJs() !!}
@endpushif
