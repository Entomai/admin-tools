# Header Update Widget

The update UI lives in the admin header, beside the notification group. It is intentionally separate from Admin Tools Settings.

Admin Tools Settings controls whether the widget appears. The widget itself is a quick operational control: it lists available plugin and theme updates, lets an administrator select one or more updates, and submits them without opening the Installed Plugins screen.

## Built-in Providers

Admin Tools checks three sources:

- Botble Marketplace plugins, using the same marketplace update action as the Installed Plugins screen.
- Entomai private plugin updates, when a private updater package is active.
- Botble Marketplace themes, when installed themes declare both `id` and `version` in `theme.json`.

The widget only appears when at least one provider returns an update or a provider message needs attention.

## Theme Requirements

Theme updates are only listed when the theme has enough metadata to identify a marketplace product:

```json
{
    "id": "vendor/theme-name",
    "name": "Theme Name",
    "version": "1.0.0"
}
```

Admin Tools validates the downloaded package before replacing the theme:

- The ZIP must be valid and must not contain unsafe paths.
- The package must include a matching `theme.json`.
- The new version must be newer than the installed version.
- The current theme folder is backed up before replacement.
- Theme assets are published after replacement.

## Permissions

The widget uses the `admin-tools.settings` permission.

Provider actions also respect related Botble permissions:

- Marketplace plugin updates require `plugins.marketplace`.
- Private plugin updates require `plugins.index`.
- Theme updates require `theme.index`.
