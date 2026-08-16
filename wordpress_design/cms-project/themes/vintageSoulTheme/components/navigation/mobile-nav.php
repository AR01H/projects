<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\UrlHelper;

$items = isset( $items ) && is_array( $items ) ? $items : array();
?>
<button type="button" class="mobile-nav-toggle" id="mobile-nav-toggle" aria-expanded="false" aria-controls="mobile-nav"
	aria-label="<?php esc_attr_e( 'Open menu', 'vintagesoul' ); ?>"
	data-label-open="<?php esc_attr_e( 'Open menu', 'vintagesoul' ); ?>"
	data-label-close="<?php esc_attr_e( 'Close menu', 'vintagesoul' ); ?>">
	<span class="mobile-nav-toggle__bar" aria-hidden="true"></span>
	<span class="mobile-nav-toggle__bar" aria-hidden="true"></span>
	<span class="mobile-nav-toggle__bar" aria-hidden="true"></span>
</button>

<div class="mobile-nav-scrim" id="mobile-nav-scrim"></div>

<nav class="mobile-nav" id="mobile-nav" aria-label="<?php esc_attr_e( 'Mobile', 'vintagesoul' ); ?>" inert>
	<ul class="mobile-nav__list">
		<?php foreach ( $items as $item ) :
			$item  = (array) $item;
			$label = trim( (string) ( $item['label'] ?? '' ) );
			if ( '' === $label ) {
				continue;
			}
			$url      = UrlHelper::resolve( (string) ( $item['url'] ?? '#' ) );
			$children = ( isset( $item['children'] ) && is_array( $item['children'] ) ) ? $item['children'] : array();
			$has_kids = ! empty( $children );
		?>
			<li class="mobile-nav__item">
				<div class="mobile-nav__row">
					<a class="mobile-nav__link" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php if ( $has_kids ) : ?>
						<button type="button" class="mobile-nav__toggle" aria-expanded="false"
							aria-label="<?php echo esc_attr( sprintf(  __( '%s submenu', 'vintagesoul' ), $label ) ); ?>">
							<span class="mobile-nav__chevron" aria-hidden="true"></span>
						</button>
					<?php endif; ?>
				</div>
				<?php if ( $has_kids ) : ?>
					<div class="mobile-nav__submenu">
						<ul class="mobile-nav__sublist">
							<?php foreach ( $children as $child ) :
								$child       = (array) $child;
								$child_label = trim( (string) ( $child['label'] ?? '' ) );
								if ( '' === $child_label ) {
									continue;
								}
							?>
								<li><a href="<?php echo esc_url( UrlHelper::resolve( (string) ( $child['url'] ?? '#' ) ) ); ?>"><?php echo esc_html( $child_label ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
