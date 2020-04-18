<?php
/**
 * Active tag clear control
 */

if ( isset( $clear_item ) && ! $clear_item ) {
	return;
}

$clear_title = ! empty( $clear_title ) ? $clear_title : __( 'Clear', 'jet-smart-filters' );

?>
<div class="jet-active-tag jet-active-tag--clear" data-filter="">
	<div class="jet-active-tag__val"><?php echo $clear_title; ?></div>
	<div class="jet-active-tag__remove">&times;</div>
</div>