<?php
/**
 * components/tabs.php - tabbed content panels.
 *
 * GENERIC: any set of parallel topics that would otherwise be five long
 * sections - delivery vs collection, three franchise formats, a spec sheet
 * per product, this year vs last year.
 *
 * Progressive by design: with JS off every panel renders stacked with its own
 * heading, so nothing is trapped behind a script. assets/js/ui-kit.js
 * (NTTabs) upgrades it to real tabs with arrow-key navigation.
 *
 * Data: admin/data/<source>.json (default `tabs`)
 *   { tag, title, sub, tabs[] {
 *       key, label, icon, heading, text (string or array),
 *       image, image_alt, points[], link_label, link_url
 *   } }
 *
 * Args:
 *   source string  Which JSON file to read.
 */

defined( 'ABSPATH' ) || exit;

$nt_src  = ( isset( $source ) && $source ) ? (string) $source : 'tabs';
$nt_data = App_Data_Provider::get( $nt_src );

$nt_tabs = array();
foreach ( (array) ( $nt_data['tabs'] ?? array() ) as $nt_i => $nt_tab ) {
	$nt_tab   = (array) $nt_tab;
	$nt_label = trim( (string) ( $nt_tab['label'] ?? '' ) );
	if ( '' === $nt_label ) {
		continue;
	}
	$nt_tab['key']   = sanitize_html_class( (string) ( $nt_tab['key'] ?? 'tab-' . $nt_i ) );
	$nt_tab['label'] = $nt_label;
	$nt_tabs[]       = $nt_tab;
}
if ( empty( $nt_tabs ) ) {
	return;
}

$nt_scope = sanitize_html_class( $nt_src );
$nt_tag   = (string) ( $nt_data['tag'] ?? '' );
$nt_title = (string) ( $nt_data['title'] ?? '' );
$nt_sub   = (string) ( $nt_data['sub'] ?? '' );
?>
<section class="app-tabs" id="<?php echo esc_attr( $nt_scope ); ?>">
	<div class="container">

		<?php if ( $nt_tag || $nt_title || $nt_sub ) : ?>
			<div class="app-section-center">
				<?php if ( $nt_tag ) : ?><div class="app-section-tag"><?php echo esc_html( $nt_tag ); ?></div><?php endif; ?>
				<?php if ( $nt_title ) : ?><h2 class="section-title"><?php echo wp_kses( $nt_title, array( 'em' => array() ) ); ?></h2><?php endif; ?>
				<?php if ( $nt_sub ) : ?><p class="section-body"><?php echo esc_html( $nt_sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="app-tabs__scope" data-nt-tabs>

			<div class="app-tabs__strip" role="tablist" aria-label="<?php echo esc_attr( NT_Ui::aria( 'tabs', 'Section tabs' ) ); ?>">
				<?php foreach ( $nt_tabs as $nt_i => $nt_tab ) : ?>
					<button type="button"
					        class="app-tabs__tab<?php echo 0 === $nt_i ? ' is-active' : ''; ?>"
					        data-nt-tab="<?php echo esc_attr( $nt_tab['key'] ); ?>"
					        role="tab"
					        id="<?php echo esc_attr( $nt_scope . '-tab-' . $nt_tab['key'] ); ?>"
					        aria-controls="<?php echo esc_attr( $nt_scope . '-panel-' . $nt_tab['key'] ); ?>"
					        aria-selected="<?php echo 0 === $nt_i ? 'true' : 'false'; ?>">
						<?php if ( ! empty( $nt_tab['icon'] ) ) : ?>
							<span class="app-tabs__icon" aria-hidden="true">
								<?php echo NT_Icons::get_or_text( (string) $nt_tab['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
							</span>
						<?php endif; ?>
						<span><?php echo esc_html( $nt_tab['label'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<?php foreach ( $nt_tabs as $nt_i => $nt_tab ) : ?>
				<div class="app-tabs__panel<?php echo 0 === $nt_i ? ' is-active' : ''; ?>"
				     data-nt-tab-panel="<?php echo esc_attr( $nt_tab['key'] ); ?>"
				     id="<?php echo esc_attr( $nt_scope . '-panel-' . $nt_tab['key'] ); ?>"
				     role="tabpanel"
				     aria-labelledby="<?php echo esc_attr( $nt_scope . '-tab-' . $nt_tab['key'] ); ?>"
				     tabindex="0">

					<?php // Only visible when JS has NOT upgraded the block - see .is-enhanced in CSS. ?>
					<h3 class="app-tabs__fallback-heading"><?php echo esc_html( $nt_tab['label'] ); ?></h3>

					<div class="app-tabs__inner">
						<?php if ( ! empty( $nt_tab['image'] ) ) : ?>
							<figure class="app-tabs__figure">
								<img src="<?php echo esc_url( App_Helpers::link( (string) $nt_tab['image'] ) ); ?>"
								     alt="<?php echo esc_attr( $nt_tab['image_alt'] ?? '' ); ?>"
								     loading="lazy" decoding="async">
							</figure>
						<?php endif; ?>

						<div class="app-tabs__copy">
							<?php if ( ! empty( $nt_tab['heading'] ) ) : ?>
								<h3 class="app-tabs__heading"><?php echo esc_html( $nt_tab['heading'] ); ?></h3>
							<?php endif; ?>

							<?php foreach ( (array) ( $nt_tab['text'] ?? array() ) as $nt_para ) : ?>
								<p class="app-tabs__text"><?php echo esc_html( $nt_para ); ?></p>
							<?php endforeach; ?>

							<?php if ( ! empty( $nt_tab['points'] ) ) : ?>
								<ul class="app-tabs__points">
									<?php foreach ( (array) $nt_tab['points'] as $nt_point ) : ?>
										<li>
											<?php NT_Icons::render( 'check' ); ?>
											<span><?php echo esc_html( $nt_point ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>

							<?php if ( ! empty( $nt_tab['link_label'] ) && ! empty( $nt_tab['link_url'] ) ) : ?>
								<a class="app-tabs__link" href="<?php echo esc_url( App_Helpers::link( (string) $nt_tab['link_url'] ) ); ?>">
									<?php echo esc_html( $nt_tab['link_label'] ); ?>
									<?php NT_Icons::render( 'arrow-right' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

		</div>
	</div>
</section>
