<?php

/**
 * Admin View: Stripe payment gateway setting page.
 *
 * @version     2.0
 * @package     WP_Hotel_Booking_Stripe/Views
 * @category    View
 * @author      Thimpress, leehld
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

$settings = WPHB_Settings::instance();
$stripe   = $settings->get( 'stripe' );
$stripe   = wp_parse_args(
	$stripe,
	array(
		'enable'           => 'on',
		'test_secret_key'  => '',
		'test_publish_key' => '',
		'live_secret_key'  => '',
		'live_publish_key' => '',
		'test_mode'        => 'on'
	)
);

$field_name = $settings->get_field_name( 'stripe' );
?>

<table class="form-table">
    <tr>
        <th><?php _e( 'Enable', 'wphb-stripe-payment' ); ?></th>
        <td>
            <input type="hidden" name="<?php echo esc_attr( $field_name ); ?>[enable]" value="off"/>
            <input type="checkbox"
                   name="<?php echo esc_attr( $field_name ); ?>[enable]" <?php checked( $stripe['enable'] == 'on' ? 1 : 0, 1 ); ?>
                   value="on"/>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Test Mode Enable', 'wphb-stripe-payment' ); ?></th>
        <td>
            <input type="hidden" name="<?php echo esc_attr( $field_name ); ?>[test_mode]" value="off"/>
            <input type="checkbox"
                   name="<?php echo esc_attr( $field_name ); ?>[test_mode]" <?php checked( $stripe['test_mode'] == 'on' ? 1 : 0, 1 ); ?>
                   value="on"/>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Test Secret Key', 'wphb-stripe-payment' ); ?></th>
        <td>
            <input type="text" name="<?php echo esc_attr( $field_name ); ?>[test_secret_key]"
                   value="<?php echo esc_attr( $stripe['test_secret_key'] ); ?>"/>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Test Publishable Key', 'wphb-stripe-payment' ); ?></th>
        <td>
            <input type="text" name="<?php echo esc_attr( $field_name ); ?>[test_publish_key]"
                   value="<?php echo esc_attr( $stripe['test_publish_key'] ); ?>"/>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Live Secret Key', 'wphb-stripe-payment' ); ?></th>
        <td>
            <input type="text" class="regular-text" name="<?php echo esc_attr( $field_name ); ?>[live_secret_key]"
                   value="<?php echo esc_attr( $stripe['live_secret_key'] ); ?>"/>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Live Publishable Key', 'wphb-stripe-payment' ); ?></th>
        <td>
            <input type="text" class="regular-text" name="<?php echo esc_attr( $field_name ); ?>[live_publish_key]"
                   value="<?php echo esc_attr( $stripe['live_publish_key'] ); ?>"/>
        </td>
    </tr>
</table>