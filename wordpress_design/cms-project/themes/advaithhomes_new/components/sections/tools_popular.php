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
<section class="popular-tools-section">
	<div class="container">
		<div class="popular-tools-header">
			<h2><?php esc_html_e( 'Popular Calculators', ADN_TEXT_DOMAIN ); ?></h2>
		</div>
		<div class="popular-tools-grid">
			<?php foreach ( $popular_tools as $calc ) : ?>
				<?php adn_component( 'cards/tool_card', array( 'card' => array(
					'icon'      => isset( $calc['icon'] ) ? $calc['icon'] : '🧮',
					'name'      => isset( $calc['title'] ) ? $calc['title'] : '',
					'desc'      => isset( $calc['desc'] ) ? $calc['desc'] : '',
					'url'       => isset( $calc['url'] ) ? $calc['url'] : '',
					'thumbnail' => isset( $calc['thumbnail'] ) && '' !== $calc['thumbnail'] ? (string) $calc['thumbnail'] : '',
					'highlight' => isset( $calc['highlight'] ) && $calc['highlight'] ? (string) $calc['highlight'] : '',
				) ) ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
