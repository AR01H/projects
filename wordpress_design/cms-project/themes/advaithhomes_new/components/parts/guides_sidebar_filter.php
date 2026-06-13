<?php
/**
 * components/parts/guides_sidebar_filter.php - Left sidebar for guides listing.
 *
 * Props: $sidebar { browse_cats[], level_filters[], format_filters[], help_cta{title,text,button_label,button_url} }
 * Usage: adn_component( 'parts/guides_sidebar_filter', array( 'sidebar' => $ctx['sidebar'] ) );
 */

defined( 'ABSPATH' ) || exit;

$sidebar        = isset( $sidebar ) && is_array( $sidebar ) ? $sidebar : array();
$browse_cats    = isset( $sidebar['browse_cats'] )    ? (array) $sidebar['browse_cats']    : array();
$level_filters  = isset( $sidebar['level_filters'] )  ? (array) $sidebar['level_filters']  : array();
$format_filters = isset( $sidebar['format_filters'] ) ? (array) $sidebar['format_filters'] : array();
$help_cta       = isset( $sidebar['help_cta'] )       ? (array) $sidebar['help_cta']       : array();
?>
<aside class="guides-sidebar">

	<?php /* Browse categories */ ?>
	<?php if ( ! empty( $browse_cats ) ) : ?>
		<div class="guides-sidebar-box">
			<div class="sidebar-box-title"><?php echo esc_html__( 'Browse Guides', ADN_TEXT_DOMAIN ); ?></div>
			<?php foreach ( $browse_cats as $cat ) :
				$active = ! empty( $cat['active'] );
			?>
				<button
					class="sidebar-cat-item<?php echo $active ? ' active' : ''; ?>"
					data-cat="<?php echo esc_attr( isset( $cat['label'] ) ? $cat['label'] : '' ); ?>"
					type="button"
				>
					<span><?php echo esc_html( isset( $cat['label'] ) ? $cat['label'] : '' ); ?></span>
					<?php if ( ! empty( $cat['count'] ) ) : ?>
						<span class="cat-count"><?php echo esc_html( (string) (int) $cat['count'] ); ?></span>
					<?php endif; ?>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php /* Level filter */ ?>
	<?php if ( ! empty( $level_filters ) ) : ?>
		<div class="filter-box">
			<div class="filter-box-title"><?php echo esc_html__( 'Filter by Level', ADN_TEXT_DOMAIN ); ?></div>
			<?php foreach ( $level_filters as $filter ) :
				$checked = ! empty( $filter['checked'] );
				$label   = isset( $filter['label'] ) ? (string) $filter['label'] : '';
				$id      = 'glf-level-' . sanitize_html_class( strtolower( str_replace( ' ', '-', $label ) ) );
			?>
				<label class="filter-option" for="<?php echo esc_attr( $id ); ?>">
					<input
						type="checkbox"
						id="<?php echo esc_attr( $id ); ?>"
						class="guides-level-filter"
						data-level="<?php echo esc_attr( $label ); ?>"
						<?php checked( $checked ); ?>
					/>
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php /* Format filter */ ?>
	<?php if ( ! empty( $format_filters ) ) : ?>
		<div class="filter-box">
			<div class="filter-box-title"><?php echo esc_html__( 'Filter by Format', ADN_TEXT_DOMAIN ); ?></div>
			<?php foreach ( $format_filters as $filter ) :
				$checked = ! empty( $filter['checked'] );
				$label   = isset( $filter['label'] ) ? (string) $filter['label'] : '';
				$id      = 'glf-fmt-' . sanitize_html_class( strtolower( str_replace( array( ' ', '(', ')' ), array( '-', '', '' ), $label ) ) );
			?>
				<label class="filter-option" for="<?php echo esc_attr( $id ); ?>">
					<input
						type="checkbox"
						id="<?php echo esc_attr( $id ); ?>"
						class="guides-format-filter"
						data-format="<?php echo esc_attr( $label ); ?>"
						<?php checked( $checked ); ?>
					/>
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php /* Expert help CTA */ ?>
	<?php if ( ! empty( $help_cta ) ) : ?>
		<div class="help-box">
			<?php if ( ! empty( $help_cta['title'] ) ) : ?>
				<div class="help-box-title"><?php echo esc_html( $help_cta['title'] ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $help_cta['text'] ) ) : ?>
				<p class="help-box-text"><?php echo esc_html( $help_cta['text'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $help_cta['button_label'] ) ) : ?>
				<a
					href="<?php echo esc_url( adn_link( isset( $help_cta['button_url'] ) ? $help_cta['button_url'] : '' ) ); ?>"
					class="btn btn-primary btn-sm help-box-btn"
				>
					<?php echo esc_html( $help_cta['button_label'] ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

</aside>
