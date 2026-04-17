# Contact and Payments

Admin Tools includes compact notifications for Contact and Payments.

Both integrations are optional and load only when the related plugin is active, the required classes exist, the route exists, and the current user has permission.

## Contact

Contact notifications use:

- plugin: `contact`
- model: `Botble\Contact\Models\Contact`
- route: `contacts.index`
- permission: `contacts.index`
- status: `ContactStatusEnum::UNREAD`

The notification shows unread messages and links each item to `contacts.edit` when the user can edit contacts.

## Payments

Payment notifications use:

- plugin: `payment`
- model: `Botble\Payment\Models\Payment`
- route: `payment.index`
- permission: `payment.index`
- status: `PaymentStatusEnum::PENDING`

The notification lists pending transactions and links to `payment.show` when available.

## Morph safety

The Payment integration does not eager-load `customer`.

Some payments may reference customer models from inactive plugins. Loading that morph relation can break the header. The notification only reads payment fields that are safe when optional plugins are disabled.

## Notification order

The default notification order is:

1. Ecommerce orders.
2. Contact messages.
3. Payments.

Plugins can still add their own notifications with a custom `priority`.
