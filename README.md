# Cart Reminder Timer for WooCommerce

A powerful WooCommerce plugin that reduces cart abandonment and boosts conversions with an interactive countdown timer and automatic time-limited discounts.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start Guide](#quick-start-guide)
- [Configuration Steps](#configuration-steps)
- [How It Works](#how-it-works)
- [Settings Explained](#settings-explained)
- [Customization](#customization)
- [Troubleshooting](#troubleshooting)
- [Security](#security)
- [Support](#support)

## Features

### Core Features
- **Interactive Countdown Timer** - Eye-catching animated timer with real-time countdown
- **Time-Limited Discounts** - Automatically apply percentage or fixed discounts to cart items
- **No Coupon Codes** - Discounts are applied directly without showing coupon codes to customers
- **Responsive Design** - Works perfectly on mobile, tablet, and desktop devices
- **Multiple Display Locations** - Show timer on cart page, checkout page, or both
- **Progress Bar** - Visual representation of remaining time with smooth animations

### Advanced Features
- **Customizable Colors** - Choose from 4 professional color schemes (Danger, Warning, Info, Success)
- **Per-Product Timers** - Different timer durations for different products (via product meta)
- **Dismissable Timer** - Allow users to close and reopen the timer with a floating button
- **Sound Alerts** - Optional audio notification when 1 minute remains
- **Email Reminders** - Send automated emails before discount expires
- **Minimum Cart Amount** - Only show timer when cart exceeds a minimum value
- **Customizable Messages** - Different messages for logged-in users vs. guests

## Requirements

- **WordPress** 5.0 or higher
- **WooCommerce** 3.0 or higher
- **PHP** 7.4 or higher
- **MySQL** 5.6 or higher

## Installation

### Step 1: Download the Plugin

Download the latest version of Cart Reminder Timer for WooCommerce from the WordPress Plugin Directory or your custom repository.

### Step 2: Upload to WordPress

1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins > Add New > Upload Plugin**
3. Choose the downloaded ZIP file
4. Click **Install Now**

Alternatively, extract the plugin folder and upload it via FTP to `/wp-content/plugins/`

### Step 3: Activate the Plugin

1. Navigate to **Plugins** in the admin menu
2. Find **Cart Reminder Timer for WooCommerce**
3. Click **Activate**

### Step 4: Verify Installation

After activation, you should see a new **Cart Reminder Timer** option under the **WooCommerce** menu in the admin sidebar.

## Quick Start Guide

### Minimal Setup (2 minutes)

1. Go to **WooCommerce > Cart Reminder Timer**
2. Keep default settings or adjust timer duration (default: 15 minutes)
3. Keep default discount (default: 10% off)
4. Click **Save Settings**
5. Visit your cart page to see the timer in action

### Your First Configuration (5 minutes)

1. Set **Timer Duration** to your desired length (e.g., 30 minutes)
2. Choose discount type: **Percentage** or **Fixed Amount**
3. Enter discount amount (e.g., 10 for 10% off or $5 for fixed discount)
4. Select where to show timer: **Cart Only**, **Checkout Only**, or **Both**
5. Click **Save Settings**

## Configuration Steps

### Step 1: Configure General Settings

1. Navigate to **WooCommerce > Cart Reminder Timer**
2. Click on the **General** tab
3. Adjust the following settings:

#### Timer Duration
- **Default:** 15 minutes
- **Range:** 1-60 minutes
- **Recommendation:** 15-30 minutes for best conversions
- **How it works:** Timer starts when customer adds items to cart

#### Show Timer On
- **Cart Page Only:** Display timer only on the shopping cart
- **Checkout Page Only:** Display timer only on checkout
- **Both (Recommended):** Display on both cart and checkout pages

#### Minimum Cart Amount
- **Default:** 0 (always show)
- **Use case:** Show timer only for orders above a certain amount
- **Example:** Set to $50 to only show timer for orders exceeding $50

#### Timer Position
- **Top:** Display timer at the top of cart/checkout
- **Bottom:** Display timer at the bottom of cart/checkout

4. Scroll to **Appearance** section and configure:

#### Color Scheme
- **Red (Danger)** - Creates sense of urgency, highest conversion
- **Yellow (Warning)** - Attention-grabbing alternative
- **Blue (Info)** - Professional and trustworthy
- **Green (Success)** - Positive reinforcement approach

#### Additional Appearance Options
- **Show Progress Bar:** Enable to display visual countdown progress
- **Allow Dismiss:** Let users close and reopen the timer

5. Click **Save Settings**

### Step 2: Configure Discount Settings

1. Click on the **Discount** tab
2. Configure your time-limited discount:

#### Discount Type
- **Percentage (%):** Discount as a percentage of cart total
  - Example: 10% off means 10% reduction on final price
  - Best for: High-value carts where percentage discount feels substantial
- **Fixed Amount:** Discount as a fixed currency amount
  - Example: $5 off means exactly $5 reduction
  - Best for: Creating consistent discount value

#### Discount Amount
- Enter the discount value based on selected type
- **For percentage:** 5-30% typically converts best
- **For fixed amount:** $5-$20 based on your average order value

#### How Discounts Work
- Timer starts when customer adds items to cart
- Discount is **automatically applied** to all products in cart
- No coupon codes are shown to customers
- Customer sees reduced price on all items
- Discount is **only valid** if customer completes checkout before timer expires
- After timer expires, discount is automatically removed

3. Click **Save Settings**

### Step 3: Customize Messages

1. Click on the **Messages** tab
2. Configure customer-facing messages:

#### Message for Logged-In Users
- **Default:** "Hurry! You have a special discount - complete your purchase before time expires!"
- **Tip:** Use personalization like "Hi [Name]" if available
- **Best practice:** Keep it under 150 characters

#### Message for Guests
- **Default:** "Limited time offer! Get your discount - checkout now!"
- **Tip:** More generic message for non-registered users
- **Best practice:** Emphasize urgency and action

#### Audio & Email Alerts

**Sound Alerts:**
- Enable to play notification sound when 1 minute remains
- Great for mobile visitors who leave the page open
- Can increase conversions for time-sensitive offers

**Email Reminders:**
- Enable to send email before discount expires
- Only sent to logged-in users (requires email)
- Includes direct link to cart with active discount

3. Click **Save Settings**

### Step 4: Review Advanced Settings

1. Click on the **Advanced** tab
2. Review the "Getting Started" checklist
3. Access documentation links if needed

## How It Works

### Timeline of Events

1. **Customer adds items to cart**
   - Timer starts immediately
   - Discount is applied to all items
   - Customer sees reduced prices

2. **Timer counts down**
   - Real-time countdown updates
   - Progress bar shows time remaining (if enabled)
   - Dismissable timer (if enabled)

3. **With 1 minute remaining**
   - Optional sound alert plays
   - Message becomes more urgent
   - Email reminder sent to logged-in users (if enabled)

4. **Timer expires**
   - Discount is automatically removed
   - Prices revert to original values
   - New timer is created if customer adds more items

5. **Customer checks out before expiry**
   - Discount is locked in at checkout
   - Invoice shows discounted amount
   - Order is marked with discount applied

## Settings Explained

### General Tab

| Setting | Default | Options | Purpose |
|---------|---------|---------|---------|
| Timer Duration | 15 | 1-60 minutes | How long discount is valid |
| Show Timer On | Both | Cart/Checkout/Both | Where to display timer |
| Min Cart Amount | 0 | Any number | Minimum cart value to show timer |
| Timer Position | Top | Top/Bottom | Display position on page |
| Color Scheme | Red | 4 options | Timer appearance |
| Show Progress Bar | Enabled | Yes/No | Visual countdown bar |
| Allow Dismiss | Disabled | Yes/No | Users can hide/show timer |

### Discount Tab

| Setting | Default | Options | Purpose |
|---------|---------|---------|---------|
| Discount Type | Percentage | % or Fixed | Discount calculation method |
| Discount Amount | 10 | Any number | Discount value |

### Messages Tab

| Setting | Default | Purpose |
|---------|---------|---------|
| Message (Logged-In) | Hurry! ... | Shown to registered customers |
| Message (Guests) | Limited time... | Shown to guest customers |
| Sound Alert | Disabled | Notification when 1 min left |
| Email Reminder | Disabled | Email before expiry |

## Customization

### Adjust Timer Duration for Specific Products

To set different timer durations for specific products:

1. Edit the product
2. Go to **Product Meta** section
3. Add custom field: `_crt_duration` with value in minutes
4. Save product

### Custom CSS Modifications

Add custom CSS in your theme's `style.css` or Customizer:

```css
/* Change timer colors */
.crt-timer-box.crt-danger {
  background-color: #your-color !important;
}

/* Adjust timer size */
.crt-timer-content {
  font-size: 32px !important;
}

/* Position timer differently */
.crt-timer-box {
  margin-bottom: 30px !important;
}
```

### Hooks for Developers

Customize behavior with WordPress hooks:

```php
// Modify discount amount before application
apply_filters( 'crt_discount_amount', $amount, $cart );

// Modify timer duration
apply_filters( 'crt_timer_duration', $duration, $product_id );

// Modify timer message
apply_filters( 'crt_timer_message', $message, $user_type );

// Customize email reminder content
apply_filters( 'crt_email_reminder_content', $content, $user );
```

## Troubleshooting

### Timer Not Showing

**Problem:** Timer doesn't appear on cart or checkout page

**Solutions:**
1. Verify plugin is activated: **Plugins > Cart Reminder Timer** should show "Deactivate"
2. Check minimum cart amount setting - may be too high
3. Verify timer is enabled for current page:
   - Go to **Settings > Show Timer On**
   - Select appropriate option (Cart/Checkout/Both)
4. Check if using custom cart template - may need code adjustment
5. Clear WordPress cache if using caching plugin

### Discount Not Applied

**Problem:** Discount doesn't appear in cart total

**Solutions:**
1. Verify discount is enabled in **Discount tab**
2. Check discount amount is not zero
3. Ensure minimum cart amount is met
4. Clear browser cache and reload cart page
5. Check if using page caching - temporarily disable to test
6. Verify WooCommerce tax settings not interfering

### Timer Resets on Page Reload

**Problem:** Timer countdown restarts instead of continuing

**Solutions:**
1. This should NOT happen - timer persists via session
2. Try clearing browser cache and cookies
3. Check server session settings:
   - Contact hosting provider to verify sessions are enabled
   - Session timeout may be too short
4. Disable all browser extensions and try again
5. Test in private/incognito browser window

### CSS Styles Broken

**Problem:** Timer displays but styling looks wrong

**Solutions:**
1. Go to **WooCommerce > Cart Reminder Timer**
2. Check that color scheme is selected
3. Clear all caches (WordPress, browser, caching plugins)
4. Disable all CSS minification plugins temporarily
5. Check if theme has conflicting CSS
6. Test with default WordPress theme to isolate issue

### High Server Memory Usage

**Problem:** Website becomes slow after activating plugin

**Solutions:**
1. The plugin is lightweight - likely not the cause
2. Check if other plugins are conflicting
3. Deactivate all plugins except WooCommerce and Cart Reminder Timer
4. Reactivate plugins one by one to find conflict
5. Contact hosting provider to increase PHP memory limit

## Security

The plugin implements comprehensive security measures:

### Input Protection
- All user input is sanitized using WordPress functions
- CSRF protection with nonce verification on settings
- XSS protection with proper output escaping

### Output Protection
- All dynamic content is escaped:
  - HTML content: `esc_html()`
  - Attributes: `esc_attr()`
  - URLs: `esc_url_raw()`
- Scripts use `wp_json_encode()` instead of `json_encode()`

### Database Protection
- Parameterized queries prevent SQL injection
- Option values are properly escaped before storage
- No direct database queries to user input

### Admin Protection
- Capability checks: Only administrators can change settings
- Nonce verification on all form submissions
- Permission checks on all AJAX requests

## Support & Contact

For support, feature requests, or bug reports:

1. **Documentation:** Check the full documentation at your plugin dashboard
2. **FAQ:** Review frequently asked questions in Settings > Advanced
3. **Contact:** Reach out via the support form on the plugin page
4. **Issues:** Report bugs with detailed reproduction steps

## Frequently Asked Questions

### Q: Does the timer reset when customer reloads the page?
**A:** No. The timer persists using PHP sessions and continues counting down accurately.

### Q: Can I have different discounts for different products?
**A:** Currently, the plugin applies the same discount to all cart items. Future versions may support product-specific discounts.

### Q: Does the plugin work with subscription products?
**A:** Yes. The timer and discount apply to all product types including subscriptions.

### Q: Can customers use the discount without buying?
**A:** No. The discount is only applied to the cart. Once the timer expires, the discount is automatically removed.

### Q: Is the discount shown as a coupon code?
**A:** No. The discount is applied directly to product prices. Customers see the discounted price but no coupon code appears.

### Q: What happens if customer abandons cart?
**A:** The discount expires after the timer runs out. If they return, a new timer starts when they add items again.

### Q: Can I customize the timer appearance?
**A:** Yes. The plugin includes 4 color schemes and position options. Advanced users can add custom CSS.

### Q: Does the plugin track abandoned carts?
**A:** The current version applies discounts. Tracking features may be added in future updates.

## Version History

### Version 6.0
- Redesigned discount system (no more coupon codes)
- Product-specific timer support
- Improved security with WordPress.org standards
- Professional admin interface
- Comprehensive documentation
- Direct discount application without coupons

### Version 5.0
- Initial release
- Basic timer functionality
- Coupon-based discounts

## License

This plugin is licensed under the GPL v2 or later. See LICENSE file for details.

## Credits

Developed by Rashed Hossain - WooCommerce Optimization Specialist

---

**Made with ❤️ to help WooCommerce stores boost conversions**