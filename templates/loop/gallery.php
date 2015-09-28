<?php
/**
 * The Template for displaying all single products.
 *
 * Override this template by copying it to yourtheme/tp-hotel-booking/templates/single-product.php
 *
 * @author 		ThimPress
 * @package 	tp-hotel-booking/templates
 * @version     0.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$room = HB_Room::instance(get_the_ID());
$galeries = $room->get_gallery(false);

?>

<div class="hb_room_gallery camera_wrap camera_emboss" id="camera_wrap_<?php the_ID() ?>">
	<?php foreach ($galeries as $key => $gallery): ?>
	    <div data-thumb="<?php echo esc_attr( $gallery['thumb'] ); ?>" data-src="<?php echo esc_attr( $gallery['src'] ); ?>"></div>
	<?php endforeach; ?>
</div><!-- #camera_wrap_ -->
<script type="text/javascript">
	(function($){
		"use strict";
		$(document).ready(function(){
			$('#camera_wrap_<?php the_ID() ?>').camera({
				height: '56%',
				pagination: false,
				thumbnails: true
			});
		});
	})(jQuery);
</script>