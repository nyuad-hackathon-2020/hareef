<?php
/**
 * Active filter control
 */

if ( isset( $filter ) ) {
	$verbosed_val = $filter->get_verbosed_val( $value, $filter_id, $hierarchical );
}

if ( ! $verbosed_val ) {
	return;
}

if ( isset( $filter ) ) {

	$title = get_post_meta( $filter_id, '_active_label', true );

	if ( ! $title ) {
		$title = get_the_title( $filter_id );
	}

}

?>
<div class="jet-active-filter" data-filter="<?php echo htmlspecialchars( json_encode( $active_filter ) ); ?>">
	<div class="jet-active-filter__label"><?php
		echo $title . ':';
	?></div>
	<div class="jet-active-filter__val"><?php
		echo $verbosed_val;
	?></div>
	<div class="jet-active-filter__remove">&times;</div>
</div>