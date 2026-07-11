<?php
/**
 * components/sections/tools_popular.php
 * Popular calculators — widget card matching site design language.
 * Props: $popular_tools[] { icon, title, desc, url, highlight? }
 */
defined( 'ABSPATH' ) || exit;

$popular_tools = isset( $popular_tools ) && is_array( $popular_tools ) ? $popular_tools : array();
if ( empty( $popular_tools ) ) { return; }
?>
<section class="tc-section tc-popular-section">
	<div class="container">
		<div class="tc-widget">
			<div class="tc-widget-header">
				<div class="tc-widget-title">
					<span class="tc-widget-icon">🔥</span>
					<h2><?php esc_html_e( 'Popular Calculators', ADN_TEXT_DOMAIN ); ?></h2>
				</div>
			</div>
			<div class="tc-popular-grid">
				<?php foreach ( $popular_tools as $calc ) :
					$url   = esc_url( adn_link( isset( $calc['url'] ) ? $calc['url'] : '' ) );
					$icon  = isset( $calc['icon'] ) ? $calc['icon'] : '🧮';
					$title = isset( $calc['title'] ) ? $calc['title'] : '';
					$desc  = isset( $calc['desc'] )  ? $calc['desc']  : '';
					$badge = isset( $calc['highlight'] ) && $calc['highlight'] ? $calc['highlight'] : '';
				?>
					<a href="<?php echo $url; ?>" class="tc-pop-card">
						<div class="tc-pop-card-icon">
							<?php echo adn_icon( $icon ); ?>
						</div>
						<div class="tc-pop-card-body">
							<?php if ( $badge ) : ?>
								<span class="tc-pop-badge"><?php echo esc_html( $badge ); ?></span>
							<?php endif; ?>
							<h3><?php echo esc_html( $title ); ?></h3>
							<?php if ( $desc ) : ?>
								<p><?php echo esc_html( wp_trim_words( $desc, 12, '…' ) ); ?></p>
							<?php endif; ?>
						</div>
						<span class="tc-pop-arrow">&rarr;</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
