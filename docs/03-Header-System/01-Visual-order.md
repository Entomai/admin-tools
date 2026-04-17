# Visual order

Admin Tools renders one movable header-left zone and then moves it beside the Botble logo with JavaScript.

The admin layout hook still renders in the original top header area, but Admin Tools moves the generated block to the left side after the page loads.

## Final order

The visual order is fixed by section:

1. Fast menu.
2. Header menus.
3. Header notifications.
4. Custom header-left items.

Within `Header menus`, `Header notifications`, and `Custom header-left items`, each item can use `priority`.

## Why sections exist

Without sections, a notification with a low priority could appear between menus. The section system prevents that.

Menus stay beside menus. Notifications stay beside notifications. Custom items stay after the structured elements.

## Moving behavior

The generated wrapper uses:

```html
data-entomai-header-left
```

The JavaScript finds `header.navbar .navbar-brand`, moves the wrapper after it, and adds the class:

```html
entomai-header-left-navbar
```

The CSS then narrows the brand area only when Admin Tools is active in that header.
