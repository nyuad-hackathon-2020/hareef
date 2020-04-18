<?php
/**
 * Apply filters button
 */

$apply_redirect = ! empty( $settings['apply_redirect'] ) ? $settings['apply_redirect'] : false;
$redirect_path  = ! empty( $settings['redirect_path'] ) ? $settings['redirect_path'] : false;
$query_id       = ! empty( $settings['query_id'] ) ? $settings['query_id'] : 'default';

?>
<div class="apply-filters">
	<button
		type="button"
		class="apply-filters__button"
		data-apply-provider="<?php echo $settings['content_provider']; ?>"
		data-apply-type="<?php echo $settings['apply_type']; ?>"
		data-query-id="<?php echo $query_id; ?>"
		data-redirect="<?php echo $apply_redirect; ?>"
		data-redirect-path="<?php echo $redirect_path; ?>"
	>
		<?php echo $settings['apply_button_text']; ?>
	</button>
</div>