<?php

if ( empty( $args ) ) {
	return;
}

$current = $this->get_current_filter_value( $args );

?>
<div class="jet-select" <?php $this->filter_data_atts( $args ); ?>>
	<?php include jet_smart_filters()->get_template( 'common/filter-label.php' ); ?>
	<?php

		$options   = $args['options'];
		$query_var = $args['query_var'];

		$classes = array( 'jet-select__control' );

		if ( isset( $args['depth'] ) ) {
			$classes[] = 'depth-' . $args['depth'];
		}

		?>
		<select
			class="<?php echo implode( ' ', $classes ); ?>"
			name="<?php echo $query_var; ?>"
			<?php $this->control_data_atts( $args ); ?>
		>
		<?php if ( ! empty( $args['placeholder'] ) ) { ?>
			<option value=""><?php echo $args['placeholder']; ?></option>
		<?php } ?>
		<?php

		foreach ( $options as $value => $label ) {

			$selected = '';

			if ( $current ) {

				if ( is_array( $current ) && in_array( $value, $current ) ) {
					$selected = ' selected';
				}

				if ( ! is_array( $current ) && $value == $current ) {
					$selected = ' selected';
				}

			}

			?>
			<option
				value="<?php echo $value; ?>"
				data-label="<?php echo $label; ?>"
				data-counter-prefix="("
				data-counter-suffix=")"
				<?php echo $selected; ?>
			><?php echo $label; ?></option>
			<?php
		}

		?></select>

</div>
