<?php
/**
 * Template Name: Guides Archive
 *
 * Non-filtered  → Knowledge-Hub "Property Guides" layout (mockup #2):
 *                 inline title + sidebar (search/categories/topics) + featured + grid.
 * Filtered (?category= / ?parent_term=) → existing category browse layout.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/guides.php';

$active_pt      = $data['active_pt'];
$active_cat     = $data['active_cat'];
$active_cat_obj = $data['active_cat_obj'];
$is_filtered    = $data['is_filtered'];
$base_url       = $data['base_url'];

if ( ! $is_filtered ) :
	/* ── HUB VIEW (mockup #2) ──────────────────────────────────────────────── */
	?>
	<section class="ghub">
	  <div class="container">
	    <header class="ghub-head">
	      <h1 class="ghub-head__title">Property Guides</h1>
	      <p class="ghub-head__sub">Step-by-step guidance for every stage of your property journey.</p>
	    </header>
	    <div class="ghub-layout">
	      <aside class="ghub-aside">
	        <?php get_template_part( 'components/guides/hub-sidebar', null, $data ); ?>
	      </aside>
	      <?php get_template_part( 'components/guides/hub-main', null, $data ); ?>
	    </div>
	  </div>
	</section>
	<?php

else :
	/* ── FILTERED CATEGORY / PARENT-TERM VIEW (unchanged) ──────────────────── */
	if ( $active_pt && ! $active_cat ) {
		$ph = [
			'eyebrow'  => 'Guides',
			'title'    => esc_html( $active_pt->name ),
			'title_em' => '',
			'desc'     => ! empty( $active_pt->description ) ? esc_html( $active_pt->description ) : '',
		];
	} else {
		$ph = [
			'eyebrow'  => ! empty( $active_pt ) ? esc_html( $active_pt->name ) : 'Guides',
			'title'    => esc_html( $active_cat_obj['title'] ?? $active_cat ),
			'title_em' => '',
			'desc'     => ! empty( $active_cat_obj['desc'] ) ? esc_html( $active_cat_obj['desc'] ) : '',
		];
	}
	$ph['breadcrumb'] = array_filter( [
		[ 'Home',   home_url( '/' ) ],
		[ 'Guides', esc_url( $base_url ) ],
		$active_cat_obj ? [ esc_html( $active_cat_obj['title'] ?? $active_cat ), '' ] : null,
		$active_pt && ! $active_cat ? [ esc_html( $active_pt->name ?? $data['active_pt_slug'] ), '' ] : null,
	] );
	get_template_part( 'components/page-header', null, $ph );
	?>
	<div class="gc-portal-bg">
	  <div class="container">
	    <div class="gc-portal-layout">
	      <main class="gc-portal-main">
	        <?php get_template_part( 'components/guides/filtered-main', null, $data ); ?>
	      </main>
	      <aside class="gc-portal-sidebar">
	        <?php get_template_part( 'components/guides/sidebar', null, $data ); ?>
	      </aside>
	    </div>
	  </div>
	</div>
	<?php
endif;

get_template_part( 'components/cta-section' );
get_footer();
