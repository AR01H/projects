<?php
/**
 * components/legal-document.php - a long-form policy page.
 *
 * GENERIC: privacy policy, cookie policy, terms of sale, delivery terms,
 * accessibility statement. One component, one JSON file per document.
 *
 * It gives a policy the two things a plain wall of text lacks: a "last
 * updated" line the reader can trust, and a contents list that jumps to each
 * clause. Both are generated from the same data as the body, so they can
 * never fall out of step with it.
 *
 * Data: admin/data/<source>.json
 *   {
 *     "title": "…", "updated": "…", "intro": "…",
 *     "toc_heading": "On this page",
 *     "contact": { "heading": "…", "text": "…", "email": "…" },
 *     "sections": [
 *       { "id": "what-we-collect", "heading": "…",
 *         "text": ["…"], "points": ["…"] }
 *     ]
 *   }
 *
 * Args:
 *   source string  Which JSON file to read (required in practice - each
 *                  policy page passes its own).
 */

defined( 'ABSPATH' ) || exit;

$nt_src  = ( isset( $source ) && $source ) ? (string) $source : 'legal_privacy';
$nt_doc  = App_Data_Provider::get( $nt_src );
$nt_secs = ( is_array( $nt_doc ) && ! empty( $nt_doc['sections'] ) ) ? (array) $nt_doc['sections'] : array();
if ( empty( $nt_secs ) ) {
	return;
}

// Give every clause a stable anchor, falling back to a slug of its heading.
foreach ( $nt_secs as $nt_i => $nt_sec ) {
	$nt_sec              = (array) $nt_sec;
	$nt_sec['heading']   = (string) ( $nt_sec['heading'] ?? '' );
	$nt_sec['id']        = sanitize_title( (string) ( $nt_sec['id'] ?? $nt_sec['heading'] ?: 'clause-' . ( $nt_i + 1 ) ) );
	$nt_secs[ $nt_i ]    = $nt_sec;
}
?>
<section class="app-legal">
	<div class="container app-legal__wrap">

		<div class="app-legal__head">
			<?php App_Helpers::component( 'parts/breadcrumbs' ); ?>

			<?php if ( ! empty( $nt_doc['title'] ) ) : ?>
				<h1 class="app-legal__title"><?php echo esc_html( $nt_doc['title'] ); ?></h1>
			<?php endif; ?>

			<?php if ( ! empty( $nt_doc['updated'] ) ) : ?>
				<p class="app-legal__updated">
					<?php NT_Icons::render( 'calendar' ); ?>
					<?php echo esc_html( $nt_doc['updated'] ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $nt_doc['intro'] ) ) : ?>
				<p class="app-legal__intro"><?php echo esc_html( $nt_doc['intro'] ); ?></p>
			<?php endif; ?>
		</div>

		<div class="app-legal__body">

			<?php if ( count( $nt_secs ) > 1 ) : ?>
				<nav class="app-legal__toc" aria-label="<?php echo esc_attr( $nt_doc['toc_heading'] ?? '' ); ?>">
					<?php if ( ! empty( $nt_doc['toc_heading'] ) ) : ?>
						<h2 class="app-legal__toc-title"><?php echo esc_html( $nt_doc['toc_heading'] ); ?></h2>
					<?php endif; ?>
					<ol class="app-legal__toc-list">
						<?php foreach ( $nt_secs as $nt_sec ) : ?>
							<?php if ( '' === $nt_sec['heading'] ) { continue; } ?>
							<li><a href="#<?php echo esc_attr( $nt_sec['id'] ); ?>"><?php echo esc_html( $nt_sec['heading'] ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				</nav>
			<?php endif; ?>

			<div class="app-legal__clauses">
				<?php foreach ( $nt_secs as $nt_n => $nt_sec ) : ?>
					<article class="app-legal__clause" id="<?php echo esc_attr( $nt_sec['id'] ); ?>">
						<?php if ( '' !== $nt_sec['heading'] ) : ?>
							<h2 class="app-legal__clause-title">
								<span class="app-legal__clause-no"><?php echo esc_html( sprintf( '%02d', $nt_n + 1 ) ); ?></span>
								<?php echo esc_html( $nt_sec['heading'] ); ?>
							</h2>
						<?php endif; ?>

						<?php foreach ( (array) ( $nt_sec['text'] ?? array() ) as $nt_para ) : ?>
							<p class="app-legal__text"><?php echo esc_html( $nt_para ); ?></p>
						<?php endforeach; ?>

						<?php if ( ! empty( $nt_sec['points'] ) ) : ?>
							<ul class="app-legal__points">
								<?php foreach ( (array) $nt_sec['points'] as $nt_point ) : ?>
									<li><?php echo esc_html( $nt_point ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>

			<?php if ( ! empty( $nt_doc['contact'] ) ) : ?>
				<?php
				$nt_contact = (array) $nt_doc['contact'];
				app_alert( array(
					'tone'       => 'info',
					'icon'       => 'mail',
					'title'      => $nt_contact['heading'] ?? '',
					'body'       => $nt_contact['text'] ?? '',
					'link_label' => $nt_contact['email'] ?? '',
					'link_url'   => ! empty( $nt_contact['email'] ) ? 'mailto:' . sanitize_email( $nt_contact['email'] ) : '',
					'class'      => 'app-legal__contact',
				) );
				?>
			<?php endif; ?>

		</div>
	</div>
</section>
