# Shared sign-in and booking products

Customers, administrators, and employees sign in at `/login`. Customers and administrators use their username; employees use the email stored on their employee record. Successful sign-in opens the appropriate workspace. The previous `/staff/login` address redirects to this shared form, and its POST address remains compatible with old clients. Both sign-out addresses clear both guards. Employee access requires an active status and `is_active`; deactivation also blocks remembered sessions and receipt access.

Employee records remain in `employees`. The existing `staff` guard now uses an authenticatable Employee model, including its existing remember token. Existing active staff sessions are upgraded when entering the staff workspace. Customer sessions cannot reuse a leftover staff ID. Identical credentials belonging to two records require an administrator to resolve the username collision.

## Selling rates

These shop rates were supplied in the conversation. They are not manufacturer retail prices.

| Item | Rate |
| --- | --- |
| Liquid detergent | ₱24 per sachet |
| Fabric softener | ₱27 per liter, charged on the measured volume |
| Measuring cup | Exactly 1.5 mL per cup, as explicitly confirmed |
| Scent Boost | ₱25 per order |
| Hang Dry | ₱20 per order |
| With Packaging | ₱35 per order |

The confirmed cup is **1.5 mL**, not 1.5 L. One cup of softener costs ₱0.04 after centavo rounding; ten cups are 15 mL and cost ₱0.41. The calculation rounds the complete selected volume once, rather than multiplying a rounded per-cup price. Cup quantities use half-cup steps. The shop's earlier ₱27/liter rate is retained for softener after detergent changed to sachet pricing.

`App\Support\LaundryProducts` owns the brands, units, and product rates. `LaundryWorkflow::EXTRAS` contains only the three extras above. Liquid products apply to Wash & Fold and Express Service. Detergent can be customer-provided; softener can be omitted. The form shows these choices explicitly and disables the irrelevant quantities.

## Saving and reviewing orders

`LaundryOrderService` calculates product charges on the server. Client-supplied prices, totals, and snapshots are ignored. Orders save a product snapshot containing the brand, quantity, volume, rate basis, and line amount, plus a separate `product_charges` amount. Receipts and customer/staff/admin order views include those charges. Admin revenue queries include products. Existing orders default to no product charge, preserving their previous extras and totals. Staff activity reports display full booked value, including products and extras.

The additive `2026_11_09_000001_add_laundry_products_to_orders` migration creates the two order columns. No employee records are copied or recreated.

The shared form is in `resources/views/components/laundry-booking-form.blade.php`; product choices are in `components/laundry-products`. `public/js/booking.js` handles price previews, hold buttons, cup gestures, and keyboard controls. `public/css/booking.css` styles the form and measuring-cup illustration. Reduced-motion preferences disable the liquid animations while the quantity controls remain functional.

## Logo sources

Brand images are served locally from `public/img/laundry-brands`; exact image URLs are in `SOURCES.json` there.

- [P&G Philippines brands](https://ph.pg.com/brands-and-products/): Ariel and Downy.
- [Unilever Philippines — Breeze](https://www.unilever.com.ph/brands/home-care/breeze/).
- [Unilever Philippines — Surf](https://www.unilever.com.ph/brands/home-care/surf/).

The logos identify the selectable brands; their trademarks belong to their respective owners.
