<?php

class HB_Extra_Cart
{
	/**
	 * instead of new class()
	 * @var null
	 */
	static $_instance = null;

	function __construct( $cart_id = null )
	{
		/**
		 * cart add more somethings
		 */
		add_filter( 'tp_hotel_booking_append_cart_data', array( $this, 'add_extra_to_cart' ), 10, 1 );
		/**
		 * return session cart
		 */
		add_filter( 'tp_hb_session_cart_id', array( $this, 'append_session' ), 10, 3 );

		/**
		 * wp.template layout filter
		 */
		add_filter( 'hb_get_template', array( $this, 'mini_cart_layout' ), 10, 5 );

		/**
		 * after mini cart item loop
		 */
		add_action( 'hotel_booking_before_mini_cart_loop_price', array( $this, 'mini_cart_loop' ), 10, 1 );

		/**
		 * profilter ro0m item price in minicart
		 */
		add_filter( 'hotel_booking_room_total_price', array( $this, 'filter_price' ), 10, 4 );

		/**
		 * add filter add to cart results array
		 * render object build mini cart
		 */
		add_filter( 'tp_hb_add_to_cart_results', array( $this, 'add_to_cart_results' ), 10, 2 );

		/**
		 * booking params save _hb_booking_params
		 */
		add_filter( 'hotel_booking_booking_params', array( $this, 'booking_info' ) );

		/**
		 * ajax remove packages
		 */
		add_action( 'wp_ajax_tp_hotel_booking_remove_package', array( $this, 'remove_package' ) );
		add_action( 'wp_ajax_nopriv_tp_hotel_booking_remove_package', array( $this, 'remove_package' ) );

		/**
		 * append package into cart
		 */
		add_action( 'hotel_booking_cart_after_item', array( $this, 'cart_package_after_item' ) );

		add_filter( 'tp_hb_extra_cart_input', array( $this, 'check_respondent' ) );

		add_filter( 'hotel_booking_update_cart', array( $this, 'update_cart_package' ), 10, 4 );


		/**
		 * filter price by extra package type. For ex: number, trip type
		 */
		add_filter( 'tp_hb_extra_package_price', array( $this, 'addition_package_price' ), 10, 5 );
	}

	/**
	 * [addition_package_price description]
	 * @param  [float] $price
	 * @param  [text] $respondent
	 * @param  [int] $quantity
	 * @param  [int] $night
	 * @param  [boolean] $tax
	 * @return [float]
	 */
	function addition_package_price( $price, $respondent, $quantity, $night, $tax )
	{
		if( $respondent === 'number' )
		{
			$price = $price * $quantity * $night;
		}

		if( $tax && hb_price_including_tax() )
		{
			return $price + $price * hb_get_tax_settings();
		}
		return $price;
	}

	/**
	 * $_POST
	 * @param $posts
	 * @return  $posts
	 */
	public function add_extra_to_cart( $posts )
	{
		// if( ! defined( 'DOING_AJAX' ) ) return $posts;

		if( ! isset( $_POST ) || empty( $_POST ) ) return $posts;

		if( isset( $_POST['action'] ) && $_POST['action'] === 'hotel_booking_ajax_add_to_cart' )
		{
			foreach ( $_POST as $key => $_post ) {
				$posts[ $key ] = $_post;
			}
		}

		return $posts;
	}

	/**
	 * append session cart item id
	 * @param  array $sessions session storage
	 * @param  array $posts    $_POST param
	 * @return array session
	 */
	public function append_session( $session_cart_id, $sessions, $posts )
	{
		if( ! isset( $posts['hb_optional_quantity_selected'] ) || empty( $posts['hb_optional_quantity_selected'] ) )
		{
			$search_key = isset( $session_cart_id[ 'search_key' ] ) ? $session_cart_id[ 'search_key' ] : false;
			$room_id = isset( $session_cart_id[ 'id' ] ) ? $session_cart_id[ 'id' ] : false;

			if( ! $search_key || ! array_key_exists( $search_key , $sessions ) )
				return $session_cart_id;

			if( ! $room_id || ! array_key_exists( $room_id, $sessions[ $search_key ] ) )
				return $session_cart_id;

			if( ! isset( $sessions[ $search_key ][$room_id]['extra_packages'] ) )
				return $session_cart_id;

			$session_cart_id[ 'extra_packages' ] = $sessions[ $search_key ][$room_id]['extra_packages'];

			return $session_cart_id;
		}
		else
		{
			if( ! isset( $posts['hb_optional_quantity'] ) || empty( $posts['hb_optional_quantity'] ) )
				return $session_cart_id;

			$extras = array();
			foreach ( $posts['hb_optional_quantity'] as $extra_id => $quantity ) {
				if( ! array_key_exists( $extra_id, $posts['hb_optional_quantity_selected'] ) )
					continue;

				unset( $session_cart_id[ 'extra_packages' ][ $extra_id  ] );
				$extras[ $extra_id ] = $quantity;
			}

			if( ! empty( $extras ) )
				$session_cart_id[ 'extra_packages' ] = $extras;

			return $session_cart_id;
		}
		return $session_cart_id;
	}

	/**
	 * wp.template hook template
	 * @param  [type] $located       [description]
	 * @param  [type] $template_name [description]
	 * @param  [type] $args          [description]
	 * @param  [type] $template_path [description]
	 * @param  [type] $default_path  [description]
	 * @return [type]                [description]
	 */
	public function mini_cart_layout( $located, $template_name, $args, $template_path, $default_path )
	{
		if( $template_name === 'shortcodes/mini_cart_layout.php' )
		{
			return tp_hb_extra_locate_template( 'shortcodes/mini_cart_layout.php' );
		}

		return $located;
	}

	/**
	 * extra package each cart item
	 * @param  [type] $room [description]
	 * @return [type]       [description]
	 */
	public function mini_cart_loop( $room )
	{
		if( ! $room ) return;

		ob_start();

		tp_hb_extra_get_template( 'loop/mini-cart-extra.php',
			array(
				'extra_packages' 	=> $room->extra_packages,
				'check_in'			=> $room->check_in_date,
				'check_out'			=> $room->check_out_date,
				'room_quantity' 	=> $room->quantity
		) );

		echo ob_get_clean();
	}

	/**
	 * filter price in minicart
	 * @param  [type] $price [description]
	 * @param  [type] $room  [description]
	 * @return [type]        [description]
	 */
	public function filter_price( $price, $room, $tax, $singular )
	{
		remove_filter( 'hotel_booking_room_total_price', array( $this, 'filter_price' ), 10, 4 );

		if( $room->extra_packages )
		{
			foreach ( $room->extra_packages as $package_id => $quanity ) {
				if( ! $singular )
					continue;

				$package = HB_Extra_Package::instance( $package_id, $room->check_in_date, $room->check_out_date, $room->quantity, $quanity );
				if( $tax )
				{
					$price = $price + $package->price_tax;
				}
				else
				{
					$price = $price + $package->price;
				}
			}
		}

		add_filter( 'hotel_booking_room_total_price', array( $this, 'filter_price' ), 10, 4 );
		return $price;
	}

	function remove_package()
	{
		if( ! isset( $_POST ) || ! defined( 'HB_BLOG_ID' ) )
			return;

		if( ! isset( $_POST['room_id'] ) || ! isset( $_POST['package_id'] ) || ! isset( $_POST['time_key'] ) )
			return;

		$room_id = $_POST['room_id'];
		$time_key = $_POST['time_key'];
		$package_id = $_POST['package_id'];

		if( ! isset( $_SESSION['hb_cart'.HB_BLOG_ID]['products'][$time_key] ) || ! isset( $_SESSION['hb_cart'.HB_BLOG_ID]['products'][$time_key][$room_id] ) )
			return;

		if( isset( $_SESSION['hb_cart'.HB_BLOG_ID]['products'][$time_key][$room_id]['extra_packages'] )
			&& isset( $_SESSION['hb_cart'.HB_BLOG_ID]['products'][$time_key][$room_id]['extra_packages'][ $package_id ] )
		)
		{
			unset( $_SESSION['hb_cart'.HB_BLOG_ID]['products'][$time_key][$room_id]['extra_packages'][ $package_id ] );
		}

		$hb_cart = HB_Cart::instance();

		$room = $hb_cart->get_room( $room_id, $time_key );

		$results =  array(
                    'status'    		=> 'success',
                    'id'        		=> $room_id,
                    'permalink' 		=> get_permalink( $room_id ),
                    'search_key'		=> $time_key,
                    'name'      		=> sprintf( '%s (%s)', $room->name, $room->capacity_title ),
                    'quantity'  		=> $room->quantity,
                    'total'     		=> hb_format_price( $room->total_price ),
                    // use to cart table
                    'package_id'		=> $package_id,
                    'item_total'		=> hb_format_price( $room->total ),
                    'sub_total'  		=> hb_format_price( $hb_cart->sub_total ),
	                'grand_total'   	=> hb_format_price( $hb_cart->total ),
	                'advance_payment' 	=> hb_format_price( $hb_cart->advance_payment )
            );

		$extraRoom = $room->extra_packages;
		$extra_packages = array();
		if( $extraRoom )
		{
			foreach ( $extraRoom as $id => $quantt ) {
				$extra = HB_Extra_Package::instance( $id );
				$extra_packages[] = array(
						'package_title'				=> sprintf( '%s (%s)', $extra->title, hb_format_price( $extra->regular_price_tax ) ),
						'package_id'				=> $extra->ID,
						'package_quantity'			=> sprintf( 'x%s', $quantt ),
						'package_respondent'		=> $extra->respondent
					);
			}
		}
		$results['extra_packages'] = $extra_packages;

		$results = apply_filters( 'hb_remove_package_results', $results, $room_id );
        hb_send_json( $results );
	}

	/**
	 * add to cart results
	 * @param [array] $results [results]
	 * @param [object] $room    [room object class]
	 */
	function add_to_cart_results( $results, $room )
	{
		if( $extraRoom = $room->extra_packages )
		{
			if( empty( $extraRoom ) )
				return $results;

			$extra_packages = array();
			foreach ( $extraRoom as $id => $quantt ) {
				$extra = HB_Extra_Package::instance( $id );
				$extra_packages[] = array(
						'package_title'		=> sprintf( '%s (%s)', $extra->title, hb_format_price( $extra->regular_price_tax ) ),
						'package_id'		=> $extra->ID,
						'package_quantity'	=> sprintf( 'x%s', $quantt )
					);
			}
			$results['extra_packages'] = $extra_packages;
		}
		return $results;
	}

	/**
	 * generate room info
	 * @param  [type] $room_info [description]
	 * @param  [type] $room      [description]
	 * @return [type]            [description]
	 */
	function booking_info( $params )
	{
		foreach ( $params as $key => $rooms ) {
			foreach ( $rooms as $room_id => $room_param ) {
				if( isset( $room_param['extra_packages'] ) && ! empty( $room_param['extra_packages'] ) )
				{
					foreach ( $room_param['extra_packages'] as $id => $quantt ) {
						$extra = HB_Extra_Package::instance( $id );
						if( ! isset( $params[ $key ][ $room_id ][ 'extra_packages_details' ] ) )
							$params[ $key ][ $room_id ][ 'extra_packages_details' ] = array();

						$params[ $key ][ $room_id ][ 'extra_packages_details' ][ $id ] = array(
								'package_title'			=> sprintf( '%s (%s)', $extra->title, hb_format_price( $extra->regular_price_tax ) ),
								'package_id'			=> $extra->ID,
								'package_desciprition'	=> $extra->description,
								'package_quantity'		=> $quantt
							);
					}
				}
			}
		}

		return $params;
	}

	function cart_package_after_item( $room )
	{
		if( ! $room->extra_packages )
			return;

		if( is_hb_checkout() )
		{
			$page = 'checkout';
		}
		else
		{
			$page = 'cart';
		}

		tp_hb_extra_get_template( 'loop/addition-services-title.php', array( 'page' => $page, 'room' => $room ) );
		foreach ( $room->extra_packages as $package_id => $quantity )
		{
			$package = HB_Extra_Package::instance( $package_id, $room->check_in_date, $room->check_out_date, $room->quantity, (int)$quantity );
			tp_hb_extra_get_template( 'loop/cart-extra-package.php', array( 'package' => $package, 'room' => $room, 'page' => $page ) );
		}
	}

	function check_respondent( $respondent )
	{
		// remove_filter( 'tp_hb_extra_cart_input', array( $this, 'check_respondent' ) );
		if( is_page( hb_get_page_id( 'checkout' ) ) || hb_get_request( 'hotel-booking' ) === 'checkout' )
			return false;

		if( is_page( hb_get_page_id( 'my-rooms' ) ) || hb_get_request( 'hotel-booking' ) === 'cart' )
		{
			if( $respondent === 'trip' )
				return false;
		}
		// add_filter( 'tp_hb_extra_cart_input', array( $this, 'check_respondent' ) );
		return $respondent;
	}

	/**
	 * submit update package in the cart page
	 * @param  [array] $sessions $_SESSION
	 * @param  [type] $posts    [description]
	 * @return [array]           $_SESSION processed
	 */
	function update_cart_package( $sessions, $posts, $search_key, $room_id )
	{

		if( ! isset( $posts['hotel_booking_cart_package'] ) || empty( $posts['hotel_booking_cart_package'] ) )
			return $sessions;

		if( ! isset( $posts['hotel_booking_cart_package'][ $search_key ] ) )
			return $sessions;

		if( ! isset( $posts['hotel_booking_cart_package'][ $search_key ][ $room_id ] ) )
			return $sessions;

		$sessions['extra_packages'] = $posts['hotel_booking_cart_package'][ $search_key ][ $room_id ];

		return $sessions;
	}

	/**
	 * instead of new class. quickly, helpfully
	 * @param $cart_id [description]
	 * @return object or null
	 */
	static function instance( $cart_id = null )
	{
		if( ! empty( self::$_instance[ $cart_id ] ) )
			return self::$_instance[ $cart_id ];

		return new self();
	}

}
new HB_Extra_Cart();