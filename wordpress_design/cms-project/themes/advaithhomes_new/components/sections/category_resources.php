<?php
/**
 * components/sections/category_resources.php - PDFs, external links, YouTube videos.
 *
 * Props:
 *   $resources array {
 *     pdfs[]   { title, desc, file_url }
 *     links[]  { title, desc, url, icon }
 *     videos[] { title, vid_id }
 *   }
 *   $heading string  optional section heading (default: SITE_LABEL_USEFUL_RESOURCES)
 */

defined( 'ABSPATH' ) || exit;

$resources = isset( $resources ) && is_array( $resources ) ? $resources : array();
$heading   = isset( $heading )   && '' !== $heading ? (string) $heading
	: ( defined( 'SITE_LABEL_USEFUL_RESOURCES' ) ? SITE_LABEL_USEFUL_RESOURCES : 'Useful Resources' );

$_pdfs  = array_filter( (array) ( $resources['pdfs']   ?? array() ), function( $p ) { return ! empty( $p['file_url'] ) && ! empty( $p['title'] ); } );
$_links = array_filter( (array) ( $resources['links']  ?? array() ), function( $l ) { return ! empty( $l['title'] ); } );
$_vids  = array_filter( (array) ( $resources['videos'] ?? array() ), function( $v ) { return ! empty( $v['url'] ); } );

if ( ! $_pdfs && ! $_links && ! $_vids ) { return; }
?>
<div class="category-resources">

	<?php adn_component( 'parts/section_headers/section_header', array(
		'heading' => array( 'title' => $heading, 'link_label' => '', 'link_url' => '' ),
		'tag'     => 'h3',
	) ); ?>

	<?php if ( $_pdfs ) : ?>
	<div class="res-subsection">
		<p class="res-sub-label"><?php esc_html_e( 'PDF Documents', ADN_TEXT_DOMAIN ); ?></p>
		<div class="res-grid">
			<?php foreach ( $_pdfs as $pdf ) : ?>
			<a href="<?php echo esc_url( $pdf['file_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="res-card">
				<div class="display-flex-wrap">
					<div class="res-card-icon">
						<?= adn_icon('📥');?>
					</div>
				<strong class="res-card-title"><?php echo esc_html( $pdf['title'] ); ?></strong>
				</div>
				<div class="res-card-body">
					<?php if ( ! empty( $pdf['desc'] ) ) : ?>
					<p class="res-card-desc"><?php echo esc_html( $pdf['desc'] ); ?></p>
					<?php endif; ?>
				</div>
				<span class="btn btn-primary btn-sm"><?php esc_html_e( 'Download', ADN_TEXT_DOMAIN ); ?></span>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( $_links ) : ?>
	<div class="res-subsection">
		<p class="res-sub-label"><?php esc_html_e( 'External Links', ADN_TEXT_DOMAIN ); ?></p>
		<div class="res-grid">
			<?php foreach ( $_links as $lnk ) :
				$_raw_icon = isset( $lnk['icon'] ) ? trim( $lnk['icon'] ) : '';
			?>
			<a href="<?php echo esc_url( $lnk['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="res-card">
				<div class="res-card-icon res-icon--link">
					<span class="res-icon-emoji"><?php echo adn_icon( $_raw_icon ); ?></span>
				</div>
				<div class="res-card-body">
					<strong class="res-card-title"><?php echo esc_html( $lnk['title'] ); ?></strong>
					<?php if ( ! empty( $lnk['desc'] ) ) : ?>
					<p class="res-card-desc"><?php echo esc_html( $lnk['desc'] ); ?></p>
					<?php endif; ?>
				</div>
				<span class="btn btn-primary btn-sm"><?php esc_html_e( 'Visit', ADN_TEXT_DOMAIN ); ?> </span>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( $_vids ) : ?>
	<div class="res-subsection">
		<p class="res-sub-label"><?php esc_html_e( 'Videos', ADN_TEXT_DOMAIN ); ?></p>
		<div class="res-grid res-grid-youtube">
			<?php foreach ( $_vids as $vid ) : ?>
			<div class="res-video-item">
				<div class="res-video-embed">
					<iframe
						src="<?php echo esc_url( 'https://www.youtube.com/embed/' . $vid['vid_id'] . '?rel=0&modestbranding=1' ); ?>"
						title="<?php echo esc_attr( $vid['title'] ); ?>"
						loading="lazy"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
						allowfullscreen></iframe>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

</div>
