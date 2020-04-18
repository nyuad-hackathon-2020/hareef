<?php
/**
 * Active tag control
 */

if ( isset( $filter ) ) {
	$verbosed_val = $filter->get_verbosed_val( $value, $filter_id, $hierarchical );
}

if ( ! $verbosed_val ) {
	return;
}

?>
<div class="jet-active-tag" data-filter="<?php echo htmlspecialchars( json_encode( $active_tag ) ); ?>">
	<div class="jet-active-tag__val"><?php
		echo $verbosed_val;
	?></div>
	<div class="jet-active-tag__remove">&times;</div>
</div>