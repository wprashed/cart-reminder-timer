# Cart Reminder Timer for WooCommerce

A powerful WooCommerce plugin to reduce cart abandonment and boost conversions with an interactive countdown timer.

## Features

- **Interactive Countdown Timer** with smooth animations and progress bar
- **Auto-Apply Discounts** when timer expires
- **Email Reminders** before cart items are released
- **A/B Testing** to optimize your messaging
- **Cart Abandonment Tracking** for analytics
- **Responsive Design** for all devices
- **Sound Alerts** and visual urgency indicators
- **Easy Configuration** with professional admin panel

## Installation

1. Download and extract the plugin
2. Upload to `/wp-content/plugins/`
3. Activate from WordPress Plugins menu
4. Configure in WooCommerce > Cart Reminder Timer

## Requirements

- WordPress 5.0+
- WooCommerce 3.0+
- PHP 7.4+

## Quick Start

### Basic Configuration

1. Go to **WooCommerce > Cart Reminder Timer**
2. Set timer duration (default: 15 minutes)
3. Choose color scheme (Danger, Warning, Info, or Success)
4. Select where to show timer (Cart, Checkout, or Both)
5. Save settings

### Enable Auto-Coupon

1. Go to **Coupon** tab
2. Enable "Enable Coupon on Timer Expiry"
3. Set coupon type (Percentage or Fixed)
4. Enter discount amount
5. Configure max usage per user

### Enable Email Reminders

1. Go to **Notifications** tab
2. Enable "Enable Email Reminders"
3. Customize messages for different user types
4. Save settings

## Customization

### Messages

You can customize the timer message for:
- Logged-in users
- Guest customers

Different messages for different user types help increase conversions.

### Color Schemes

Choose from:
- **Red (Danger)** - Creates urgency
- **Yellow (Warning)** - Attention-grabbing
- **Blue (Info)** - Professional look
- **Green (Success)** - Positive reinforcement

### A/B Testing

Enable A/B testing to automatically test different messages and track which performs better.

## Hooks & Filters

The plugin provides several filters for developers:

```php
// Modify timer remaining time
apply_filters( 'CRT_timer_remaining', $remaining_seconds );

// Customize coupon settings
apply_filters( 'CRT_coupon_settings', $settings );

// Modify email reminder content
apply_filters( 'CRT_email_reminder_content', $content, $user );
```

## Performance

The plugin is highly optimized for performance:
- Efficient database queries
- Minimal JavaScript footprint
- Lazy loading of assets
- Optional sound alerts
- No impact on page load time

## Security

- All user input is sanitized
- All output is properly escaped
- Nonce verification on admin forms
- SQL injection protection
- XSS protection

## Support & Contributing

For issues, feature requests, or contributions, please visit the plugin repository.

## License

GPL v2 or later. See LICENSE file for details.

---

**Made with ❤️ for WooCommerce stores**
