# Quality Assurance & Testing Checklist

Comprehensive testing protocols for **Advanced WooCommerce Backorders & Lead Times** (`advanced-woocommerce-backorders`).

---

### Test Scenarios Matrix

| # | Test Scenario | Steps to Execute | Expected Result |
|---|---|---|---|
| **1** | **Product stock = 10** | Set product inventory to `10`, backorders allowed. View single product and catalog. | Product shows "In stock" (or stock count). Backorder notice is **not** displayed. Lead time is only shown if "Display lead times for in-stock products" is enabled. |
| **2** | **Product stock = 0 + backorders disabled** | Set stock to `0`, backorders set to "Do not allow". | Product displays standard "Out of stock" notice. Add to cart button is disabled. Backorder notice is **not** displayed. Lead time displays if enabled for out-of-stock items. |
| **3** | **Product stock = 0 + backorders allowed** | Set stock to `0`, backorders set to "Allow". View product page, cart, and checkout. | Notice displays: `Available on backorder` + configured lead time (e.g. `Estimated dispatch: 2-3 weeks`). Add to cart button remains active. |
| **4** | **Product stock = 0 + notify customer** | Set stock to `0`, backorders set to "Allow, but notify customer". | Display notice `Available on backorder` and customer notification badge. Checkout is permitted. |
| **5** | **Product without stock management** | Disable "Manage stock?" on product. Set stock status to "On backorder". | Product immediately displays backorder notice and lead time badge. Add to cart works properly. |
| **6** | **Simple product** | Configure custom lead time (e.g. `3-4 days`) on Simple Product edit screen. | Lead time displays on single product page according to placement setting (e.g. after price). |
| **7** | **Variable product** | Set parent product lead time to `2-3 weeks`. Create variations. Leave variation lead time empty. | Selecting any variation inherits parent lead time `2-3 weeks`. |
| **8** | **Variation backorder override** | On Variation "Large", enter lead time `4-5 weeks`. On Variation "Medium", enter `1-2 weeks`. | Selecting "Large" instantly changes frontend lead time to `4-5 weeks`. Selecting "Medium" updates to `1-2 weeks` via dynamic JS without reloading. |
| **9** | **Cart page** | Add a backordered item to cart. Open Cart page. Change quantity. | Backorder notice and lead time display beside product name or below quantity. Updating quantity recalculates and persists correctly. |
| **10** | **Checkout page** | Proceed to checkout with backordered item in cart. | Top checkout banner displays: *"Notice: Your order contains items available on backorder."* Item in review order table shows backorder status and lead time. Checkout finishes without blocker. |
| **11** | **Order details (Thank You & My Account)** | Complete an order containing backordered items. View Thank You page and My Account → Orders. | Line item displays frozen backorder status and lead time at time of purchase. Changing product lead time later does not alter historical order. |
| **12** | **Emails (Customer & Admin)** | Trigger order confirmation email. Inspect HTML and plain text formats. | Backorder notice and estimated dispatch appear in order line item details. Admin New Order email includes backorder tag. |
| **13** | **Category rule** | Create Category Rule for "Solar Inverters": Backorders = Allowed, Lead Time = `2-3 weeks`. | All products in "Solar Inverters" category with no product-level rule inherit `2-3 weeks` and backorder permission. |
| **14** | **Product rule overriding category** | Assign product in "Solar Inverters" a custom product rule: Lead Time = `5-6 weeks`. | Product displays `5-6 weeks`, successfully overriding the category rule (#1 > #2). |
| **15** | **Category rule overriding global** | Enable Global Override = ON (Default: `1-2 weeks`). Set Category Rule to `3-4 weeks`. | Products in category display `3-4 weeks`, confirming Category overrides Global (#2 > #3). |
| **16** | **Global override** | Set Global Backorder Behaviour to "Allow backorders" and Global Override to ON. | All products across store without higher custom rules allow backorders. |
| **17** | **HPOS enabled** | Enable High-Performance Order Storage in WooCommerce Settings → Advanced → Features. Place an order. | Orders save cleanly to `wp_wc_orders` and `wp_wc_order_itemmeta` without warnings or compatibility errors. |
| **18** | **Guest checkout** | Place an order containing backorder items as a guest user. | Order completes successfully, customer order email contains backorder notices, and guest Thank You page displays frozen lead time. |
| **19** | **Logged-in checkout** | Place an order containing backorder items as a logged-in customer. | Order details recorded in user account. Viewing order under My Account shows lead time. |
| **20** | **WooCommerce Blocks** | Test on Cart & Checkout blocks if block-based checkout is enabled in theme. | Backorder item notices and metadata remain accessible and compatible with block filters. |
