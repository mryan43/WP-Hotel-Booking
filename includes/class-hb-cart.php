<?php

/**
 * Class HB_Cart
 *
 * Simple Cart object for now. Maybe need to expand later
 */
class HB_Cart{
    /**
     * @var bool
     */
    private static $instance = false;

    protected $_options = array();

    /**
     * Construction
     */
    function __construct(){
        if( self::$instance ) return;

        if( !session_id() ) session_start();
        if( empty( $_SESSION['hb_cart'] ) ){
            $_SESSION['hb_cart'] = array(
                'cart_id'   => $this->generate_cart_id(),
                'options'   => array(),
                'products'  => array()
            );
        }
    }

    /**
     * Magic function for get a property
     *
     * @param string
     * @return mixed
     */
    function __get( $key ){
        $value = null;
        switch( $key ){
            case 'total_rooms':
                $value = $this->get_total_rooms();
                break;
            case 'total_nights':
                $value = $this->get_total_nights();
                break;
            case 'check_in_date':
            case 'check_out_date':
                $value = $this->get_option( $key );
                break;
            case 'sub_total':
                $value = $this->get_sub_total();
                break;
            case 'total':
                $value = $this->get_total();
                break;
            case 'advance_payment':
                $value = $this->get_advance_payment();
        }
        return $value;
    }

    function set_option( $name, $value = null ){
        if( is_array( $name ) ){
            foreach( $name as $k => $v ){
                $this->set_option( $k, $v );
            }
        }else {
            $_SESSION['hb_cart']['options'][$name] = $value;
        }
        return $this;
    }

    /**
     * Get total rooms
     *
     * @return int
     */
    function get_total_rooms(){
        $total_rooms = 0;
        if( $rooms = $this->get_products() ){
            foreach( $rooms as $id => $num_of_rooms ){
                $total_rooms += intval( $num_of_rooms );
            }
        }
        return apply_filters( 'hb_cart_total_rooms', $total_rooms );
    }

    /**
     * Get total nights
     *
     * @return int
     */
    function get_total_nights(){
        $start_date = $this->get_option( 'check_in_date' );
        $end_date = $this->get_option( 'check_out_date' );
        $total_nights = hb_count_nights_two_dates( $end_date, $start_date );
        return apply_filters( 'hb_cart_total_nights', $total_nights );
    }

    function get_option( $name ){
        return ! empty(  $_SESSION['hb_cart']['options'][ $name ] ) ? $_SESSION['hb_cart']['options'][ $name ] : false;
    }

    function get_cart_id(){
        return $_SESSION['hb_cart']['cart_id'];
    }

    function get_products(){
        return $_SESSION['hb_cart']['products'];
    }

    function get_sub_total(){
        $sub_total = 0;
        if( $rooms = $this->get_rooms() ) foreach( $rooms as $room_id => $room ) {
            $sub_total += $room->get_total( $this->check_in_date, $this->check_out_date, $room->get_data( 'num_of_rooms' ), false );
        }

        return apply_filters( 'hb_cart_sub_total', $sub_total );
        $total_nights = hb_count_nights_two_dates( $end_date, $start_date );
        $tax = hb_get_tax_settings();
        if( $tax > 0 ) {
            $grand_total = $total + $total * $tax;
        }else{
            $grand_total = $total;
        }

        $sub_total = 0;
        $products = $this->get_products();
        if( $products ) foreach( $products as $product ){
            $sub_total += learn_press_is_free_course( $product['id'] ) ? 0 : floatval( learn_press_get_course_price( $product['id'] ) );
        }
        learn_press_format_price( $sub_total );
        return apply_filters( 'learn_press_get_cart_subtotal', $sub_total, $this->get_cart_id() );
    }

    function get_total(){
        $sub_total  = $this->get_sub_total();

        $tax = hb_get_tax_settings();
        if( $tax > 0 ) {
            $grand_total = $sub_total + $sub_total * $tax;
        }else{
            $grand_total = $sub_total;
        }

        return apply_filters( 'learn_press_get_cart_total', $grand_total, $this->get_cart_id() );
    }

    function get_advance_payment(){
        $total = 0;
        if( $advance_payment = hb_get_advance_payment() ) {
            $total = $this->get_total() * $advance_payment / 100;
        }
        return $total;
    }

    function get_rooms( ){
        $rooms = array();
        if( $_rooms = $this->get_products() ){

            foreach( $_rooms as $room ){
                $rooms[ $room['id'] ] = HB_Room::instance( $room['id'] );
                $rooms[ $room['id'] ]->set_data( 'num_of_rooms', $room['quantity'] );
            }
        }
        return $rooms;
    }

    function generate_cart_id(){
        return md5( time() );
    }

    function add_to_cart( $room_id, $quantity = 1 ){
        $room = HB_Room::instance( $room_id );
        $price = $room->get_price();
        $_SESSION['hb_cart']['products'][$room_id] = array(
            'id'        => $room_id,
            'quantity'  => $quantity,
            'price'     => $price
        );
        return $this;
    }

    function empty_cart(){
        unset( $_SESSION['hb_cart']['products'] );
        return $this;
    }

    function destroy(){
        unset( $_SESSION['hb_cart'] );
    }

    static function instance( $prop = false, $args = false ){
        if( !self::$instance ){
            self::$instance = new self();
        }
        $ins = self::$instance;
        if( $prop ) {
            $prop = 'get_' . $prop;
        }
        return $prop && is_callable( array( $ins, $prop ) ) ? call_user_func_array( array( $ins, $prop ), (array)$args ) : $ins;
    }
}
if( !is_admin() ) {
    $GLOBALS['hb_cart'] = HB_Cart::instance();
}

function hb_get_cart( $prop = null ){
    return HB_Cart::instance( $prop );
}


function hb_get_cart_total( $pre_paid = false ){
    $cart = HB_Cart::instance();
    if( $pre_paid ){
        $total = $cart->get_advance_payment();
    }else{
        $total = $cart->get_total();
    }
    return $total;
}

function hb_uniqid(){
    $hash = str_replace( '.', '', microtime( true ) . uniqid() );
    return apply_filters( 'hb_generate_unique_hash', $hash );
}

function hb_get_cart_description(){
    $cart = HB_Cart::instance();
    $description = array();
    foreach( $cart->get_rooms() as $room ){
        $description[] = sprintf( '%s (x %d)', $room->name, $room->num_of_rooms );
    }
    return join( ', ', $description );
}


function hb_get_return_url(){
    $url = get_site_url();
    return apply_filters( 'hb_return_url', $url );
}

function hb_generate_transaction_object( $customer_id ){
    $customer = hb_get_customer( $customer_id );
    $cart = HB_Cart::instance();

    $rooms = array();
    if( $_rooms = $cart->get_rooms() ){
        foreach( $_rooms as $key => $room ) {
            $rooms[ $key ] = array(
                'id'                => $room->post->ID,
                'base_price'        => $room->get_price(),
                'quantity'          => $room->num_of_rooms,
                'name'              => $room->name,
                'sub_total'         => $room->get_total( $cart->check_in_date, $cart->check_out_date, $room->num_of_rooms )
            );
        }
    }

    $transaction_object = new stdClass();
    $transaction_object->cart_id                = $cart->get_cart_id();
    $transaction_object->total                  = round( $cart->get_total(), 2 );
    $transaction_object->sub_total              = $cart->get_sub_total();
    $transaction_object->advance_payment        = hb_get_cart_total( ! hb_get_request( 'pay_all' ) );
    $transaction_object->currency               = hb_get_currency();
    $transaction_object->description            = hb_get_cart_description();
    $transaction_object->rooms                  = $rooms;
    $transaction_object->coupons                = '';
    $transaction_object->coupons_total_discount = '';
    $transaction_object->tax                    = hb_get_tax_settings();
    $transaction_object->price_including_tax    = hb_price_including_tax();
    $transaction_object->check_in_date          = $cart->check_in_date;
    $transaction_object->check_out_date         = $cart->check_out_date;
    $transaction_object->addition_information   = hb_get_request( 'addition_information' );
    $transaction_object->total_nights           = $cart->total_nights;
    $transaction_object->currency               = hb_get_currency();

    $transaction_object = apply_filters( 'hb_generate_transaction_object', $transaction_object );

    return $transaction_object;
}

function hb_set_transient_transaction( $method, $temp_id, $customer_id, $transaction ){
    // store booking info in a day
    set_transient( $method . '-' . $temp_id, array( 'customer_id' => $customer_id, 'transaction_object' => $transaction ), 60 * 60 * 24 );
}

function hb_get_transient_transaction( $method, $temp_id ){
    return get_transient( $method . '-' . $temp_id );
}

function hb_delete_transient_transaction( $method, $temp_id ) {
    return delete_transient( $method . '-' . $temp_id );
}

function hb_add_transaction( $transaction ){
    return hb_add_booking( $transaction );
}

function hb_add_booking( $transaction ){
    /*$transaction = array(
        'method'    => 'paypal-standard',
        'method_id' => $transaction_id,
        'status'    => $transaction_status,
        'customer_id'   => $transaction_object['customer_id'],
        'transaction_object' => $transaction_object['transaction_object']
    )*/
    TP_Hotel_Booking::instance()->_include( 'includes/class-hb-room.php' );

    $transaction_object = $transaction['transaction_object'];

    $check_in               = $transaction_object->check_in_date; //('check_in_date');
    $check_out              = $transaction_object->check_out_date; //('check_out_date');
    $tax                    = $transaction_object->tax;// hb_get_tax_settings();
    $price_including_tax    = $transaction_object->price_including_tax; // hb_price_including_tax();
    $rooms                  = $transaction_object->rooms;// hb_get_request( 'num_of_rooms' );
    /*$total = 0;
    foreach( $rooms as $id => $num_of_rooms ){
        $room = HB_Room::instance( $id );
        $total += $room->get_total( $check_in, $check_out, $num_of_rooms, false );
    }
    if( ! $price_including_tax ){
        $grand_total = $total + $total * $tax;
    }else{
        $grand_total = $total;
    }
    $request = maybe_unserialize( base64_decode( $_POST['sig'] ) );
*/
    $booking = HB_Booking::instance( 0 );
    $booking->post->post_title      = sprintf( __( 'Booking from %s to %s', 'tp-hotel-booking' ), $check_in, $check_out );
    $booking->post->post_content    = $transaction_object->addition_information;
    $booking->post->post_status     = 'pending';

    /*$booking->set_customer(
        'data',
        array(
            '_hb_title'         => hb_get_request( 'title' ),
            '_hb_first_name'    => hb_get_request( 'first_name' ),
            '_hb_last_name'     => hb_get_request( 'last_name' ),
            '_hb_address'       => hb_get_request( 'address' ),
            '_hb_city'          => hb_get_request( 'city' ),
            '_hb_state'         => hb_get_request( 'state' ),
            '_hb_postal_code'   => hb_get_request( 'postal_code' ),
            '_hb_country'       => hb_get_request( 'country' ),
            '_hb_phone'         => hb_get_request( 'phone' ),
            '_hb_email'         => hb_get_request( 'email' ),
            '_hb_fax'           => hb_get_request( 'fax' )
        )
    );*/
    $booking_info = array(
        '_hb_check_in_date'         => strtotime( $check_in ),
        '_hb_check_out_date'        => strtotime( $check_out ),
        '_hb_total_nights'          => $transaction_object->total_nights,
        '_hb_tax'                   => $tax,
        '_hb_price_including_tax'   => $price_including_tax ? 1 : 0,
        '_hb_sub_total'             => $transaction_object->sub_total,
        '_hb_total'                 => $transaction_object->total,
        '_hb_advance_payment'       => $transaction_object->advance_payment,
        '_hb_currency'              => $transaction_object->currency,
        '_hb_customer_id'           => $transaction['customer_id'],
        '_hb_method'                => $transaction['method'],
        '_hb_method_id'             => $transaction['method_id'],
        '_hb_booking_status'        => $transaction['status']
    );

    $booking->set_booking_info(
        $booking_info
    );

    $booking_id = $booking->update();
    if( $booking_id ){
        //$booking_rooms = hb_get_request( 'num_of_rooms' );
        foreach( $rooms as $room ){
            $num_of_rooms = $room['quantity'];
            // insert multiple meta value
            for( $i = 0; $i < $num_of_rooms; $i ++ ) {
                add_post_meta( $booking_id, '_hb_room_id', $room['id'] );
            }
        }
        //update_post_meta( $booking_id, '_hb_rooms', $booking_rooms );
    }
    return $booking_id;
}