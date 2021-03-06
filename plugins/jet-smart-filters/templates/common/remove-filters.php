<?php
/**
 * Remove filters button
 */

$plugin = Elementor\Plugin::instance();
$extra_classes = '';

if ( !$plugin->editor->is_edit_mode() ) {
	$extra_classes = 'hide';
}

?>
<div class="jet-remove-all-filters <?php echo $extra_classes; ?>">
	<button
		type="button"
		class="jet-remove-all-filters__button"
		data-apply-provider="<?php echo $settings['content_provider']; ?>"
		data-apply-type="<?php echo $settings['apply_type']; ?>"
		data-query-id="<?php echo $query_id; ?>"
	>
		<?php echo $settings['remove_filters_text']; ?>
	</button>
</div>