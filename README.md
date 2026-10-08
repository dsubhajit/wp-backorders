# Advanced WooCommerce Backorders & Lead Times

**Advanced WooCommerce Backorders & Lead Times** (`advanced-woocommerce-backorders`) is an enterprise-grade WordPress and WooCommerce plugin designed to give store owners granular, automated control over backorders, dynamic lead times, multi-tier rule prioritization, and clear customer communications across every stage of the purchase lifecycle.

---

## 🌟 Key Features

- **4-Tier Rule Hierarchy:**
  1. Product-specific rule (Highest Priority)
  2. Category-specific rule
  3. Global rule (enforced when *Global Override* is ON)
  4. Native WooCommerce product setting (Fallback)
- **Granular Lead Times:**
  - Product-level free text or structured range (Min, Max, Days/Weeks/Months).
  - Variation-level lead time overrides for variable products.
  - Category-wide lead time rules.
  - Dynamic lead times based on product stock status (In stock, Low stock, Out of stock, On backorder).
- **Comprehensive Customer-Facing Display:**
  - Single Product Page (configurable placements & priorities).
  - Shop & Category Archive loops (with duplicate notice prevention).
  - Cart item display (after title or below quantity, updates on quantity change).
  - Checkout warning banner & order review table badges.
  - Customer Thank You & My Account order views (historical data frozen at purchase).
  - Transactional customer and admin emails.
- **Theme & Issue #39 Compatibility Shield:**
  - Guaranteed backorder notice display even when theme templates override or strip standard WooCommerce availability HTML.
- **High-Performance Order Storage (HPOS) Certified:**
  - Fully compatible with WooCommerce HPOS (`custom_order_tables`).
- **Professional Admin Suite:**
  - 10 structured admin tabs in WooCommerce → Backorders & Lead Times.
  - Interactive AJAX modal for Category Rules with priority management.
  - Custom sortable and filterable columns in the WooCommerce Products list.
  - Bulk lead time updater and one-click bulk backorder actions.
  - Settings JSON export/import and safe factory reset.
  - WC Logger integration for debugging and event auditing.

---

## 🚀 Installation

1. Upload the plugin files to your `/wp-content/plugins/advanced-woocommerce-backorders` directory (or install via the WordPress Plugins menu).
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Ensure WooCommerce is active.
4. Navigate to **WooCommerce → Backorders & Lead Times** to configure your store policies.

---

## ⚙️ Configuration & Priority Waterfall

### Backorder Rule Hierarchy
When a product's availability is checked, AWBL evaluates rules in the following sequence:

```
[Product Custom Rule] (Priority #1)
         ↓ (if not set or 'Use WC Setting')
[Category Custom Rule] (Priority #2)
         ↓ (if no category rule matches)
[Global Rule] (Priority #3 - Only when 'Global Override' is ON)
         ↓ (if 'Global Override' is OFF)
[Native WooCommerce Setting] (Priority #4 Fallback)
```

### Lead Time Resolution Hierarchy
For product lead times, the resolution order is:
1. **Variation Override:** Specific lead time entered on a variable product's variation row.
2. **Product Rule:** Configured on the product's *Backorders & Lead Time* panel.
3. **Category Rule:** Lead time specified for the product's primary category.
4. **Stock-Status Lead Time:** When enabled, resolves dynamically based on whether the product is in-stock, low-stock, out-of-stock, or on backorder.
5. **Global Default:** Fallback default specified in Lead Times tab (e.g. `2-3 weeks`).

---

## 🧩 Placeholders in Messages

Custom messages for single product pages, cart, checkout, order details, and emails support dynamic placeholders:

- `{product_name}` — The name of the product or variation.
- `{product_sku}` — The SKU of the item.
- `{lead_time}` — The resolved lead time string.
- `{stock_quantity}` — Current quantity in stock.
- `{quantity}` — Quantity in cart or ordered.

---

## 🛠️ Developer Hooks & Filters

Extend or alter plugin behavior cleanly using documented WordPress filters:

### `awbl_backorder_allowed`
Filter whether backorders are allowed for a product.
```php
add_filter( 'awbl_backorder_allowed', function( $is_allowed, $product, $rule ) {
    // Custom logic
    return $is_allowed;
}, 10, 3 );
```

### `awbl_lead_time`
Filter the final resolved lead time string for any product or variation.
```php
add_filter( 'awbl_lead_time', function( $lead_time, $product, $source ) {
    // Modify lead time output
    return $lead_time;
}, 10, 3 );
```

### `awbl_backorder_message`
Filter the processed backorder message string before display.
```php
add_filter( 'awbl_backorder_message', function( $message, $product, $context, $replacements ) {
    return $message;
}, 10, 4 );
```

### `awbl_product_rule`
Filter the product-level rule resolution data.
```php
add_filter( 'awbl_product_rule', function( $rule, $product ) {
    return $rule;
}, 10, 2 );
```

### `awbl_category_rule`
Filter the category-level rule matched for a product.
```php
add_filter( 'awbl_category_rule', function( $rule, $product_id ) {
    return $rule;
}, 10, 2 );
```

### `awbl_display_lead_time`
Filter whether lead time display is enabled for a given context (`single`, `cart`, `checkout`, `archive`, `order`, `email`).
```php
add_filter( 'awbl_display_lead_time', function( $enabled, $context ) {
    return $enabled;
}, 10, 2 );
```

### `awbl_lead_time_html`
Filter the generated HTML markup for the backorder and lead time badge.
```php
add_filter( 'awbl_lead_time_html', function( $html, $product, $context, $lead_time ) {
    return $html;
}, 10, 4 );
```

---

## 🔧 Troubleshooting & Issue #39 Fix

### Issue #39: Stock = 0, Backorders = Allow, but notice does not display
Many custom WooCommerce themes remove or rewrite WooCommerce's core `single-product/stock.php` availability template, causing products with 0 stock and backorders allowed to silently fail to display availability notices.

**How AWBL Solves This:**
1. AWBL automatically hooks directly into `woocommerce_single_product_summary` (at configurable priority), `woocommerce_before_add_to_cart_form`, and `woocommerce_after_add_to_cart_form`, rendering a dedicated `.awbl-wrapper` container regardless of theme templates.
2. AWBL simultaneously filters `woocommerce_get_availability` and `woocommerce_get_availability_text` as a secondary fallback so any theme calling native WooCommerce availability functions automatically receives the backorder status and lead time.

---

## 🗑️ Uninstall Behavior

When deleting the plugin through the WordPress admin:
- Native WooCommerce products, inventories, and orders are **never** deleted.
- Plugin options and category rules are preserved by default.
- If and only if the administrator explicitly enabled **"Delete plugin data on uninstall"** in *Tab 9: Tools*, plugin configuration options will be safely purged from the database.

---

## 📜 License
GPL-2.0-or-later
