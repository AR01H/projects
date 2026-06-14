<?php
/**
 * components/sections/guides_parent_group.php - One Guide parent with its subtopics.
 *
 * Props: $group { name, slug, icon, desc, url, gradient, image_url, topics[] { icon, title, url } }
 */

defined( 'ABSPATH' ) || exit;

$group  = isset( $group ) && is_array( $group ) ? $group : array();
$topics = isset( $group['topics'] ) && is_array( $group['topics'] ) ? $group['topics'] : array();

if ( '' === ( isset( $group['name'] ) ? $group['name'] : '' ) ) { return; }

$name      = isset( $group['name'] )      ? (string) $group['name']      : '';
$icon      = isset( $group['icon'] )      ? (string) $group['icon']      : '📚';
$desc      = isset( $group['desc'] )      ? (string) $group['desc']      : '';
$url       = isset( $group['url'] )       ? (string) $group['url']       : '';
$slug      = isset( $group['slug'] )      ? (string) $group['slug']      : '';
$gradient  = isset( $group['gradient'] )  ? (string) $group['gradient']  : 'linear-gradient(135deg,#1d5c8e,#2d7dd2)';
$image_url = isset( $group['image_url'] ) ? (string) $group['image_url'] : '';
?>
<div class="gpg" id="guides-group-<?php echo esc_attr( $slug ); ?>">

	<?php /* ── Header ─────────────────────────────────────────────── */ ?>
	<div class="gpg-header" style="background:<?php echo esc_attr( $gradient ); ?>;">
		<div class="gpg-header-overlay"></div>
		<div class="gpg-header-content">
			<?php if ( '' !== $image_url ) : ?>
				<img src="<?php echo esc_url( $image_url ); ?>" alt="" class="gpg-logo" aria-hidden="true">
			<?php else : ?>
				<span class="gpg-icon" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
			<?php endif; ?>
			<div class="gpg-header-text">
				<h2 class="gpg-name"><?php echo esc_html( $name ); ?></h2>
				<?php if ( '' !== $desc ) : ?>
					<p class="gpg-desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( '' !== $url ) : ?>
			<a href="<?php echo esc_url( adn_link( $url ) ); ?>" class="gpg-view-all">
				<?php esc_html_e( 'Explore all', ADN_TEXT_DOMAIN ); ?> &rsaquo;
			</a>
		<?php endif; ?>
	</div>

	<?php /* ── Topics grid ─────────────────────────────────────────── */ ?>
	<?php if ( ! empty( $topics ) ) : ?>
		<div class="gpg-grid">
			<?php foreach ( $topics as $topic ) :
				$t_icon  = isset( $topic['icon'] )  ? (string) $topic['icon']  : $icon;
				$t_title = isset( $topic['title'] ) ? (string) $topic['title'] : '';
				$t_url   = isset( $topic['url'] )   ? (string) $topic['url']   : '';
				if ( '' === $t_title ) { continue; }
			?>
				<a href="<?php echo esc_url( adn_link( $t_url ) ); ?>" class="gpg-topic">
					<span class="gpg-topic-icon" aria-hidden="true"><?php echo esc_html( $t_icon ); ?></span>
					<span class="gpg-topic-title"><?php echo esc_html( $t_title ); ?></span>
					<span class="gpg-topic-arrow" aria-hidden="true">›</span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

</div>
