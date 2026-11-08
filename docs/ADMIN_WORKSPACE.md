# Admin workspace

Sign in through `/login` with a user whose role is `admin`. The workspace opens at `/admin/dashboard`.

All active admin pages use `layouts.admin-app`, a floating circular navigation menu, and the shared top bar. Local assets include `public/css/admin-workspace.css`, `public/css/admin-navigation.css`, and `public/js/admin-workspace.js`. Bootstrap, fonts, and icons are served locally. The updated screens do not require a Vite build or another database migration.

## Pages and actions

| Area | Features |
| --- | --- |
| Overview | Today's collected payments, orders in progress, ready orders, customers, a 7/30-day payment chart, service mix, and recent orders |
| Orders | Search and progress/payment/service filters, customer selection, live price review, order details, next-step actions, pickup rescheduling, payment recording, receipts, and refund recording |
| Customers | Search, profiles, permanent order history, contact editing, account creation, and new orders with the customer selected |
| Employees | Search, access filters, modal staff creation, profiles, recent attendance, and account activation/deactivation |
| Revenue | Paid transactions, daily totals, service and payment-method breakdowns, customer totals, and PDF export |
| Services | Order volume, weight, booked value, and order share for each service |
| Attendance | Today's check-ins and recorded attendance for a selected month |
| Account settings | Admin profile, contact details, and password changes requiring the current password |

Tables become cards on smaller screens so actions remain visible. Click the floating logo to expand or collapse the circular navigation. Drag the logo with a mouse or touch to snap to one of eight docks: the four corners and the middle of each edge. The dock is remembered in browser storage, and the expanded circle stays inside the visible viewport. Focus the logo and use the arrow keys to change docks; Tab enters the selections, arrow keys navigate them, and Escape closes the menu. The menu also closes when focus or a pointer moves outside it.

On the Employees page, **Add employee** opens a scrollable modal. Validation errors reopen the modal with non-secret fields preserved; successful creation returns to the team list with a profile link. The previous `/admin/employees/add` address redirects to the same modal using `?add=1`.

## Data rules

Customer, staff, and admin order creation use `LaundryOrderService` and `LaundryWorkflow`. Prices are calculated on the server and include optional extras. Status actions advance one step and reject stale forms. Pickup rescheduling is available before collection.

Laundry progress and payment status are separate. Payment recording documents money already received; it does not contact a payment gateway. Recording an existing payment again preserves its original details. Refund recording supports full refunds already issued outside the app, closes the order, and records the reason in the admin activity log.

`AdminReportService` calculates collected payments from paid, non-refunded orders. Totals include the service subtotal and extras. The reporting date is `paid_at`, with the order's creation date used for older paid records without a payment timestamp. Daily charts include days with no payments. Service booked values include paid and unpaid orders and exclude refunded orders.

Attendance uses recorded check-ins and check-outs. It does not infer leave, absence, or scheduled working days from the calendar. Deactivating an employee preserves their existing records.

## Verification

Run `php artisan test` for customer/staff regression checks and admin page, action, reporting, and PDF tests. Tests use an isolated SQLite memory database; normal application connections continue to use `.env`.

Run `php artisan view:cache` to compile Blade templates. Legacy admin component paths are registered and the old profile entry uses the new account settings view.

Chrome checks covered every admin page on desktop and phone widths, the complete admin order flow, customer and employee management, chart and report filters, PDF output, and mobile navigation. The revenue, customer, service, and attendance queries were also checked read-only against MySQL.
