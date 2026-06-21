<?php
/**
 * components/sections/guides_parent_group.php - One Guide parent with its subtopics.
 *
 * Props: $group { name, slug, icon, desc, url, gradient, image_url, topics[] { icon, title, url } }
 */

defined( 'ABSPATH' ) || exit;

$group        = isset( $group ) && is_array( $group ) ? $group : array();
$topics       = isset( $group['topics'] )       && is_array( $group['topics'] )       ? $group['topics']       : array();
$latest_posts = isset( $group['latest_posts'] ) && is_array( $group['latest_posts'] ) ? $group['latest_posts'] : array();

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
				<img src="<?php echo esc_url( $image_url ); ?>" alt="" class="gpg-logo" aria-hidden="true"
					onerror="this.style.display='none';this.nextElementSibling.removeAttribute('hidden');">
				<span class="gpg-icon" aria-hidden="true" hidden><?php echo adn_icon( $icon, 'gpg-ico' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php else : ?>
				<span class="gpg-icon" aria-hidden="true"><?php echo adn_icon( $icon, 'gpg-ico' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
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

	<div class="gpg-body">

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
					<span class="gpg-topic-icon" aria-hidden="true"><i class="fa-solid fa-tag" aria-hidden="true"></i></span>
					<span class="gpg-topic-title"><?php echo esc_html( $t_title ); ?></span>
					<span class="gpg-topic-arrow" aria-hidden="true">›</span>
				</a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php /* ── Latest posts ────────────────────────────────────────── */ ?>
		<?php if ( ! empty( $latest_posts ) ) : ?>
		<ul class="gpg-posts">
			<?php foreach ( $latest_posts as $_lp ) :
				$_lp_title = isset( $_lp['title'] ) ? (string) $_lp['title'] : '';
				$_lp_url   = isset( $_lp['url'] )   ? (string) $_lp['url']   : '';
				$_lp_date  = isset( $_lp['date'] )  ? (string) $_lp['date']  : '';
				$_lp_tag   = isset( $_lp['tag'] )   ? (string) $_lp['tag']   : '';
				if ( '' === $_lp_title ) { continue; }
			?>
			<li class="gpg-post">
				<a href="<?php echo esc_url( adn_link( $_lp_url ) ); ?>" class="gpg-post-link">
					<i class="fa-regular fa-file-lines gpg-post-icon" aria-hidden="true"></i>
					<span class="gpg-post-title"><?php echo esc_html( $_lp_title ); ?></span>
					<span class="gpg-post-arrow" aria-hidden="true">›</span>
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>

	</div>

</div>
