# Experience Days Gateway

A WooCommerce payment gateway that allows customers to checkout using Experience Days Vouchers.

- **Voucher Checkout**: Customers can enter their Experience Days Voucher code during checkout.
- **Order Management**: Orders are created with "on-hold" status for admin approval and voucher verification.
- **Voucher Storage**: Stores voucher details in order metadata and customer notes.
- **Compatibility**: Works with Classic Checkout and Gutenberg Blocks.

## Usage

Download the latest release [experience-days-gateway.zip](https://github.com/andriussok/experience-days-gateway/releases)
1. Install the plugin via the WordPress admin panel.
2. Configure it in `WooCommerce > Settings > Payments`.
3. Customers select Experience Days Gateway during checkout and enter their voucher code.
4. Orders are created with "on-hold" status for admin review.

## Dependencies
- [WooCommerce](https://en-gb.wordpress.org/plugins/woocommerce/)

## Development

You can modify php files directly in the plugin.

To make changes for Gutenberg Blocks:
1. Clone the repository
2. `npm install` - install dependencies
3. `npm run start` - for development
4. `npm run build` for production
5. `npm run zip` for distribution

## References

- [@wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/)