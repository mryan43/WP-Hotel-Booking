<?php

/**
 * The template for displaying booking thank you page.
 *
 * This template can be overridden by copying it to yourtheme/wp-hotel-booking/checkout/thank-you.php.
 *
 * @version     2.0
 * @package     WP_Hotel_Booking/Templates
 * @category    Templates
 * @author      Thimpress, leehld
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;
?>

<?php get_header(); ?>

<?php
$id      = get_transient( 'wphb_booking_transient' );
$booking = WPHB_Booking::instance( $id );
$rooms   = hb_get_order_items( $id );
?>

    <div id="booking-details">
        <div class="booking-user-data">
            <div class="user-avatar">
				<?php echo get_avatar( $booking->user_id, 120 ); ?>
            </div>
            <div class="order-user-meta">
				<?php if ( $user = get_userdata( $booking->user_id ) ) { ?>
                    <div class="user-display-name">
						<?php echo sprintf( '<a href="%s">%s</a>', get_edit_user_link( $booking->user_id ), $user->user_login ); ?>
                    </div>
                    <div class="user-email">
						<?php echo $user->user_email ? $user->user_email : ''; ?>
                    </div>
				<?php } else {
					echo __( '[Guest]', 'wp-hotel-booking' );
				} ?>
            </div>
        </div>
        <div class="booking-data">
            <h3 class="booking-data-number"><?php echo sprintf( esc_attr__( 'Order %s', 'wp-hotel-booking' ), hb_format_order_number( $id ) ); ?></h3>
            <div class="booking-date">
				<?php echo sprintf( __( 'Date %s', 'wp-hotel-booking' ), get_the_date( '', $id ) ); ?>
            </div>
        </div>
    </div>

    <div id="booking-items">

        <h3><?php echo __( 'Booking Items', 'wp-hotel-booking' ); ?></h3>

        <table cellpadding="0" cellspacing="0" class="booking_item_table">
            <thead>
            <tr>
                <th><?php _e( 'Item', 'wp-hotel-booking' ); ?></th>
                <th><?php _e( 'Check in - Checkout', 'wp-hotel-booking' ) ?></th>
                <th><?php _e( 'Night', 'wp-hotel-booking' ); ?></th>
                <th><?php _e( 'Qty', 'wp-hotel-booking' ); ?></th>
                <th><?php _e( 'Total', 'wp-hotel-booking' ); ?></th>
            </tr>
            </thead>
            <tbody>

			<?php foreach ( $rooms as $k => $room ) { ?>

                <tr>
                    <td>
						<?php printf( '<a href="%s">%s</a>', get_edit_post_link( hb_get_order_item_meta( $room->order_item_id, 'product_id', true ) ), $room->order_item_name ) ?>
                    </td>
                    <td>
						<?php printf( '%s - %s', date_i18n( hb_get_date_format(), hb_get_order_item_meta( $room->order_item_id, 'check_in_date', true ) ), date_i18n( hb_get_date_format(), hb_get_order_item_meta( $room->order_item_id, 'check_out_date', true ) ) ) ?>
                    </td>
                    <td>
						<?php printf( '%d', hb_count_nights_two_dates( hb_get_order_item_meta( $room->order_item_id, 'check_out_date', true ), hb_get_order_item_meta( $room->order_item_id, 'check_in_date', true ) ) ) ?>
                    </td>
                    <td>
						<?php printf( '%s', hb_get_order_item_meta( $room->order_item_id, 'qty', true ) ) ?>
                    </td>
                    <td>
						<?php printf( '%s', hb_format_price( hb_get_order_item_meta( $room->order_item_id, 'subtotal', true ), hb_get_currency_symbol( $booking->currency ) ) ); ?>
                    </td>
                </tr>

				<?php $packages = hb_get_order_items( $booking->id, 'sub_item', $room->order_item_id ); ?>
				<?php if ( $packages ) { ?>
					<?php foreach ( $packages as $package ) { ?>
						<?php $extra = hotel_booking_get_product_class( hb_get_order_item_meta( $package->order_item_id, 'product_id', true ) ); ?>
                        <tr data-order-parent="<?php echo esc_attr( $room->order_item_id ); ?>">
                            <td><input type="checkbox" name="book_item[]"
                                       value="<?php echo esc_attr( $package->order_item_id ); ?>"/></td>
                            <td colspan="3">
								<?php echo esc_html( $package->order_item_name ); ?>
                            </td>
                            <td>
								<?php echo esc_html( hb_get_order_item_meta( $package->order_item_id, 'qty', true ) ); ?>
                            </td>
                            <td>
								<?php echo esc_html( hb_format_price( hb_get_order_item_meta( $package->order_item_id, 'subtotal', true ), hb_get_currency_symbol( $booking->currency ) ) ); ?>
                            </td>
                            <td class="actions">
								<?php if ( $extra->respondent === 'number' ) { ?>
                                    <a href="#" class="edit" data-booking-id="<?php echo esc_attr( $booking->id ); ?>"
                                       data-booking-item-id="<?php echo esc_attr( $package->order_item_id ); ?>"
                                       data-booking-item-type="sub_item"
                                       data-booking-item-parent="<?php echo esc_attr( $package->order_item_parent ); ?>">
                                        <i class="fa fa-pencil"></i>
                                    </a>
								<?php } ?>
                                <a href="#" class="remove" data-booking-id="<?php echo esc_attr( $booking->id ); ?>"
                                   data-booking-item-id="<?php echo esc_attr( $package->order_item_id ); ?>"
                                   data-booking-item-type="sub_item"
                                   data-booking-item-parent="<?php echo $package->order_item_parent; ?>">
                                    <i class="fa fa-times-circle"></i>
                                </a>
                            </td>
                        </tr>
					<?php } ?>
				<?php } ?>
			<?php } ?>

            <tr>
                <td colspan="4"><?php _e( 'Sub Total', 'wp-hotel-booking' ) ?></td>
                <td>
					<?php printf( '%s', hb_format_price( hb_booking_subtotal( $booking->id ), hb_get_currency_symbol( $booking->currency ) ) ); ?>
                </td>
            </tr>
            <tr>
                <td colspan="4"><?php _e( 'Tax', 'wp-hotel-booking' ) ?></td>
                <td>
					<?php printf( '%s', apply_filters( 'hotel_booking_admin_booking_details', hb_format_price( hb_booking_tax_total( $booking->id ), hb_get_currency_symbol( $booking->currency ) ), $booking ) ); ?>
                </td>
            </tr>
            <tr>
                <td colspan="4"><?php _e( 'Grand Total', 'wp-hotel-booking' ) ?></td>
                <td>
					<?php printf( '%s', hb_format_price( hb_booking_total( $booking->id ), hb_get_currency_symbol( $booking->currency ) ) ) ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div id="booking-customer">

        <div class="customer-details">
            <ul class="hb-form-table">

                <li>
                    <label for="_hb_customer_title"><?php echo __( 'Title:', 'wp-hotel-booking' ); ?></label>
					<?php echo esc_html( $booking->customer_title ); ?>
                </li>

                <li>
                    <label for="_hb_customer_postal_code"><?php echo __( 'Postal Code:', 'wp-hotel-booking' ); ?></label>
					<?php echo esc_html( $booking->customer_postal_code ); ?>
                </li>

                <li>
                    <label for="_hb_customer_first_name"><?php echo __( 'First Name:', 'wp-hotel-booking' ); ?></label>
					<?php echo esc_html( $booking->customer_first_name ); ?>
                </li>

                <li>
                    <label for="_hb_customer_country"><?php echo __( 'Country:', 'wp-hotel-booking' ); ?></label>
					<?php echo esc_html( $booking->customer_country ); ?>
                </li>

                <li>
                    <label for="_hb_customer_last_name"><?php echo __( 'Last Name:', 'wp-hotel-booking' ); ?></label>
					<?php echo esc_html( $booking->customer_last_name ); ?>
                </li>

                <li>
                    <label for="_hb_customer_phone"><?php echo __( 'Phone:', 'wp-hotel-booking' ); ?></label>
					<?php echo esc_html( $booking->customer_phone ); ?>
                </li>

                <li>
                    <label for="_hb_customer_address"><?php echo __( 'Address:', 'wp-hotel-booking' ); ?></label>
					<?php echo esc_html( $booking->customer_address ); ?>
                </li>

                <li>
                    <label for="_hb_customer_email"><?php echo __( 'Email:', 'wp-hotel-booking' ); ?></label>
					<?php echo esc_html( $booking->customer_email ); ?>
                </li>

                <li>
                    <label for="_hb_customer_city"><?php echo __( 'City:', 'wp-hotel-booking' ); ?></label>
					<?php echo esc_html( $booking->customer_city ); ?>
                </li>

                <li>
                    <label for="_hb_customer_fax"><?php echo __( 'Fax:', 'wp-hotel-booking' ); ?></label>
					<?php echo esc_html( $booking->customer_tax ); ?>
                </li>

                <li>
                    <label for="_hb_customer_state"><?php echo __( 'State:', 'wp-hotel-booking' ); ?></label>
					<?php echo esc_html( $booking->customer_state ); ?>
                </li>

            </ul>
        </div>

        <div class="booking-notes">
            <label for="_hb_customer_notes"><?php echo __( 'Booking Notes:', 'wp-hotel-booking' ); ?></label>
			<?php echo esc_html( $booking->post->post_content ); ?>
        </div>

    </div>


<?php get_footer(); ?>