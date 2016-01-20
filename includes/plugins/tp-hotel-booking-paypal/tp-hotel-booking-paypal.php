<?php
/*
    Plugin Name: TP Hotel Booking PayPal Payment
    Plugin URI: http://thimpress.com/
    Description: Payment PayPal TP Hotel Booking Addon
    Author: ThimPress
    Version: 1.0
    Author URI: http://thimpress.com
*/

define( 'TP_HB_PAYPAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'TP_HB_PAYPAL_URI', plugins_url( '', __FILE__ ) );
define( 'TP_HB_PAYPAL_VER', 1.0 );

class TP_Hotel_Booking_Payment_PayPal
{

	public $is_hotel_active = false;

	public $slug = 'paypal';

	function __construct()
	{
		add_action( 'plugins_loaded', array( $this, 'is_hotel_active' ) );
	}

	/**
	 * is hotel booking activated
	 * @return boolean
	 */
	function is_hotel_active()
	{
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if( class_exists( 'TP_Hotel_Booking' ) && is_plugin_active( 'tp-hotel-booking/tp-hotel-booking.php' ) )
		{
			$this->is_hotel_active = true;
		}

		if( ! $this->is_hotel_active ) {
			add_action( 'admin_notices', array( $this, 'add_notices' ) );
		}
		else
		{
			// add payment
			add_filter( 'hb_payment_gateways', array( $this, 'add_payment_classes' ) );
			if( $this->is_hotel_active )
			{
				require_once TP_HB_PAYPAL_DIR . '/inc/class-hb-payment-gateway-paypal.php';
			}
		}
	}

	/**
	 * filter callback add payments
	 * @param array
	 */
	function add_payment_classes( $payments )
	{
		if( array_key_exists( $this->slug, $payments ) )
			return $payments;

		$payments[ $this->slug ] = new HB_Payment_Gateway_Paypal();
		return $payments;
	}

	/**
	 * notices missing tp-hotel-booking plugin
	 */
	function add_notices()
	{
		?>
			<div class="error">
				<p><?php _e( 'The <strong>TP Hotel Booking</strong> is not installed and/or activated. Please install and/or activate before you can using <strong>TP Hotel Booking PayPal</strong> add-on');?></p>
			</div>
		<?php
	}

}

new TP_Hotel_Booking_Payment_PayPal();