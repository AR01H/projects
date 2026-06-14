<?php
/**
 * components/sections/post_feedback.php
 *
 * Article footer row: helpful counter + social share links.
 *
 * Props (via extract):
 *   $share = [ 'url' => string, 'title' => string ]
 */

defined( 'ABSPATH' ) || exit;

$_share  = isset( $share ) ? (array) $share : array();
$_url    = esc_url( isset( $_share['url'] ) ? (string) $_share['url'] : '' );
$_title  = rawurlencode( isset( $_share['title'] ) ? (string) $_share['title'] : '' );
$_post   = get_the_ID();
$_count  = max( 0, (int) get_post_meta( $_post, '_adn_helpful_count', true ) );

$_tw = 'https://twitter.com/intent/tweet?url=' . rawurlencode( $_url ) . '&text=' . $_title;
$_li = 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $_url );
$_wa = 'https://wa.me/?text=' . $_title . '%20' . rawurlencode( $_url );
$_em = 'mailto:?subject=' . $_title . '&body=' . rawurlencode( $_url );
?>
<div class="post-feedback-bar">

	<?php /* ── Helpful counter ── */ ?>
	<button class="pf-helpful-btn"
	        type="button"
	        id="pf-helpful-<?php echo esc_attr( (string) $_post ); ?>"
	        data-post="<?php echo esc_attr( (string) $_post ); ?>"
	        data-nonce="<?php echo esc_attr( wp_create_nonce( 'adn_post_helpful' ) ); ?>"
	        data-liked="0"
	        aria-pressed="false">
		<svg class="pf-heart" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
		</svg>
		<span class="pf-helpful-label"><?php esc_html_e( 'Helpful', ADN_TEXT_DOMAIN ); ?></span>
		<span class="pf-helpful-count"><?php echo esc_html( number_format_i18n( $_count ) ); ?></span>
	</button>

	<?php if ( '' !== $_url ) : ?>
	<?php /* ── Share row ── */ ?>
	<div class="pf-share">
		<span class="pf-share-label"><?php esc_html_e( 'Share', ADN_TEXT_DOMAIN ); ?></span>
		<div class="pf-share-btns">
			<a href="<?php echo esc_url( $_tw ); ?>" class="pf-share-btn pf-share-tw" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on X / Twitter', ADN_TEXT_DOMAIN ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.259 5.63 5.905-5.63Zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
			</a>
			<a href="<?php echo esc_url( $_li ); ?>" class="pf-share-btn pf-share-li" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on LinkedIn', ADN_TEXT_DOMAIN ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
			</a>
			<a href="<?php echo esc_url( $_wa ); ?>" class="pf-share-btn pf-share-wa" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on WhatsApp', ADN_TEXT_DOMAIN ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
			</a>
			<a href="<?php echo esc_attr( $_em ); ?>" class="pf-share-btn pf-share-em" aria-label="<?php esc_attr_e( 'Share via Email', ADN_TEXT_DOMAIN ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
			</a>
			<button type="button"
			        class="pf-share-btn pf-share-native"
			        id="pf-native-share-<?php echo esc_attr( (string) $_post ); ?>"
			        style="display:none"
			        data-url="<?php echo esc_attr( $_url ); ?>"
			        data-title="<?php echo esc_attr( isset( $share['title'] ) ? (string) $share['title'] : '' ); ?>"
			        aria-label="<?php esc_attr_e( 'Share', ADN_TEXT_DOMAIN ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
					<polyline points="16 6 12 2 8 6"/>
					<line x1="12" y1="2" x2="12" y2="15"/>
				</svg>
			</button>
		</div>
	</div>
	<?php endif; ?>

</div>

<script>
(function(){
'use strict';
var btn = document.getElementById('pf-helpful-<?php echo esc_js( (string) $_post ); ?>');
if (!btn) return;

// Restore from sessionStorage
var stored = sessionStorage.getItem('adn_helpful_<?php echo esc_js( (string) $_post ); ?>');
if (stored === '1') { btn.dataset.liked = '1'; btn.classList.add('pf-helpful-btn--active'); btn.setAttribute('aria-pressed','true'); }

btn.addEventListener('click', function() {
    var liked = btn.dataset.liked === '1';
    btn.disabled = true;
    var fd = new FormData();
    fd.append('action', 'adn_post_helpful');
    fd.append('nonce',   btn.dataset.nonce);
    fd.append('post_id', btn.dataset.post);
    fd.append('liked',   liked ? '1' : '0');

    fetch('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', { method:'POST', body:fd })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.success) {
                var nowLiked = d.data.liked;
                btn.dataset.liked = nowLiked ? '1' : '0';
                btn.classList.toggle('pf-helpful-btn--active', nowLiked);
                btn.setAttribute('aria-pressed', nowLiked ? 'true' : 'false');
                btn.querySelector('.pf-helpful-count').textContent = d.data.count;
                sessionStorage.setItem('adn_helpful_<?php echo esc_js( (string) $_post ); ?>', nowLiked ? '1' : '0');
            }
            btn.disabled = false;
        })
        .catch(function(){ btn.disabled = false; });
});

// Native share button - show only when Web Share API is available
(function(){
    var nsBtn = document.getElementById('pf-native-share-<?php echo esc_js( (string) $_post ); ?>');
    if (!nsBtn || !navigator.share) return;
    nsBtn.style.display = '';
    nsBtn.addEventListener('click', function(){
        navigator.share({
            title: nsBtn.dataset.title || document.title,
            url:   nsBtn.dataset.url   || location.href
        }).catch(function(){});
    });
}());
}());
</script>
