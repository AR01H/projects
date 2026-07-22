<?php
/**
 * templates/admin/partials/card-grid.php - reusable stat-card grid.
 * Used by every admin page that shows a row of summary numbers (Dashboard,
 * Reader, Knowledge Base, ...) so the markup only exists once.
 *
 * @var array<int, array{title:string, value:string, sub?:string}> $cards
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="csb-card-grid">
	<?php foreach ( $cards as $card ) : ?>
		<div class="card csb-card">
			<h2 class="title"><?php echo esc_html( $card['title'] ); ?></h2>
			<p class="csb-card-value"><?php echo wp_kses_post( $card['value'] ); ?></p>
			<?php if ( ! empty( $card['sub'] ) ) : ?>
				<p class="csb-card-sub"><?php echo esc_html( $card['sub'] ); ?></p>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
<style>
.csb-card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; margin-top: 20px; }
.csb-card .title { margin-top: 0; }
.csb-card-value { font-size: 1.4em; font-weight: 600; margin: 4px 0; }
.csb-card-sub { color: #646970; font-size: 0.85em; margin: 0; }
</style>
