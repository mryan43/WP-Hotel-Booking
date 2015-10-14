<?php
global $hb_settings;
$gallery = $room->gallery;
$featured = $gallery ? array_shift( $gallery ) : false;
?>
<li class="hb-room clearfix">

    <form name="hb-search-results" class="hb-search-room-results">

        <div class="hb-room-content">
            <div class="hb-room-thumbnail">
                <?php if( $featured ):?>
                    <a class="hb-room-gallery" rel="hb-room-gallery-<?php echo $room->post->ID;?>" data-lightbox="hb-room-gallery[<?php echo $room->post->ID;?>]" data-title="<?php echo $featured['alt'];?>" href="<?php echo $featured['src'];?>">
                        <?php $room->getImage('catalog'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="hb-room-info">
                <h4 class="hb-room-name">
                    <a href="<?php echo get_the_permalink($room->post->ID) ?>">
                        <?php echo $room->name;?> (<?php echo $room->capacity_title;?>)
                    </a>
                </h4>
                <ul class="hb-room-meta">
                    <li class="hb_search_capacity">
                        <label><?php _e( 'Capacity:', 'tp-hotel-booking' );?></label>
                        <div class=""><?php echo $room->capacity;?></div>
                    </li>
                    <li class="hb_search_max_child">
                        <label><?php _e( 'Max Child:', 'tp-hotel-booking' );?></label>
                        <div><?php echo $room->max_child;?></div>
                    </li>
                    <li class="hb_search_price">
                        <label><?php _e( 'Price:', 'tp-hotel-booking' );?></label>
                        <span class="hb_search_item_price"><?php echo hb_format_price( $room->total_price );?></span>
                        <div class="hb_view_price">
                            <a href="" class="hb-view-booking-room-details"><?php _e( '(View price breakdown)', 'tp-hotel-booking' );?></a>
                            <?php hb_get_template( 'results/booking-room-details.php', array( 'room' => $room ) );?>
                        </div>
                    </li>
                    <li class="hb_search_quantity">
                        <label><?php _e( 'Quantity: ', 'tp-hotel-booking' );?></label>
                        <div>
                            <?php
                                hb_dropdown_numbers(
                                    array(
                                        'name'              => 'hb-num-of-rooms',
                                        'min'               => 1,
                                        'show_option_none'  => __( 'Select', 'tp-hotel-booking' ),
                                        'max'               => $room->post->available_rooms,
                                        'class'             => 'number_room_select'
                                    )
                                );
                            ?>
                        </div>
                    </li>
                    <li class="hb_search_add_to_cart"><button class="hb_add_to_cart"><?php _e( 'Select this room', 'tp-hotel-booking' ) ?></button></li>
                </ul>
            </div>
        </div>

        <?php wp_nonce_field( 'hb_booking_nonce_action', 'nonce' ); ?>
        <input type="hidden" name="check_in_date" value="<?php echo hb_get_request( 'check_in_date' );?>" />
        <input type="hidden" name="check_out_date" value="<?php echo hb_get_request( 'check_out_date' );?>">
        <input type="hidden" name="room-id" value="<?php echo esc_attr( $room->post->ID ); ?>">
        <input type="hidden" name="hotel-booking" value="cart">
        <input type="hidden" name="action" value="hotel_booking_ajax_add_to_cart" />
    </form>
    <?php if( ( isset($atts['gallery']) && $atts['gallery'] === 'true' ) || $hb_settings->get('enable_gallery_lightbox') ): ?>
        <?php hb_get_template('loop/gallery-lightbox.php', array( 'room' => $room )) ?>
    <?php endif; ?>
</li>