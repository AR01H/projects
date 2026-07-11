<?php
/**
 * admin/tabs/home/sub-journey.php
 * Theme-level image overrides for all Journey cards:
 *   Group A - CMS taxonomy parent terms  (keyed by term ID  → adn_journey_card_images)
 *   Group B - JSON fallback cards         (keyed by URL slug → adn_journey_json_images)
 */

defined( 'ABSPATH' ) || exit;

wp_enqueue_media();

/* ── Saved options ───────────────────────────────────────────── */
$saved_term_imgs = get_option( 'adn_journey_card_images', array() );
if ( ! is_array( $saved_term_imgs ) ) { $saved_term_imgs = array(); }

$saved_json_imgs = get_option( 'adn_journey_json_images', array() );
if ( ! is_array( $saved_json_imgs ) ) { $saved_json_imgs = array(); }

/* ── Source data ─────────────────────────────────────────────── */
$terms      = function_exists( 'adn_cms_guide_parents' ) ? adn_cms_guide_parents( 20 ) : array();
$home_data  = function_exists( 'adn_service_home_data' ) ? adn_service_home_data() : array();
$json_cards = ( isset( $home_data['journey']['cards'] ) && is_array( $home_data['journey']['cards'] ) )
	? $home_data['journey']['cards'] : array();

/* Helper: build a stable slug key from a card URL */
$url_key = function( $url ) {
	$slug = sanitize_title( trim( (string) $url, '/' ) );
	return '' !== $slug ? $slug : 'card';
};

/* ── Shared card-render helper (outputs a picker card) ───────── */
/*
 * $card_id   string  unique HTML id suffix
 * $name      string  card title
 * $icon      string  emoji
 * $input_name string  HTML input name (e.g. "journey_images[5]")
 * $saved_id  int     currently saved attachment ID (0 = none)
 * $cms_id    int     fallback attachment from CMS/term (0 = none)
 * $source    string  'override'|'cms'|'none'
 */
$render_picker = function(
	$card_id, $name, $icon,
	$input_name, $saved_id, $cms_id
) use ( $ADN_TEXT_DOMAIN ) {
	$preview_id  = $saved_id ?: $cms_id;
	$preview_url = $preview_id ? wp_get_attachment_image_url( $preview_id, 'medium' ) : '';
	$source      = $saved_id ? 'override' : ( $cms_id ? 'cms' : 'none' );
	$ADN_TD      = defined( 'ADN_TEXT_DOMAIN' ) ? ADN_TEXT_DOMAIN : 'adn';
	?>
	<div class="card" style="padding:16px;max-width:none;" id="jny-card-<?php echo esc_attr( $card_id ); ?>">

		<div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
			<?php if ( $icon ) : ?>
			<span style="font-size:1.4rem;line-height:1;"><?php echo esc_html( $icon ); ?></span>
			<?php endif; ?>
			<strong style="font-size:13px;"><?php echo esc_html( $name ); ?></strong>
			<?php if ( 'cms' === $source ) : ?>
				<span style="font-size:10px;background:#e8f5e9;color:#2e7d32;padding:2px 7px;border-radius:3px;"><?php esc_html_e( 'CMS image', $ADN_TD ); ?></span>
			<?php elseif ( 'override' === $source ) : ?>
				<span style="font-size:10px;background:#fff3e0;color:#e65100;padding:2px 7px;border-radius:3px;"><?php esc_html_e( 'Override set', $ADN_TD ); ?></span>
			<?php endif; ?>
		</div>

		<div class="jny-img-preview" style="width:100%;height:150px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;overflow:hidden;margin-bottom:10px;display:flex;align-items:center;justify-content:center;">
			<?php if ( $preview_url ) : ?>
				<img src="<?php echo esc_url( $preview_url ); ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
			<?php else : ?>
				<span style="color:#aaa;font-size:12px;"><?php esc_html_e( 'No image set', $ADN_TD ); ?></span>
			<?php endif; ?>
		</div>

		<input type="hidden"
		       name="<?php echo esc_attr( $input_name ); ?>"
		       value="<?php echo esc_attr( $saved_id ); ?>"
		       class="jny-img-id">

		<div style="display:flex;gap:8px;flex-wrap:wrap;">
			<button type="button" class="button button-secondary jny-img-select" data-cid="<?php echo esc_attr( $card_id ); ?>">
				<?php echo $saved_id ? esc_html__( 'Change Image', $ADN_TD ) : esc_html__( 'Set Image', $ADN_TD ); ?>
			</button>
			<?php if ( $saved_id ) : ?>
			<button type="button" class="button jny-img-clear" data-cid="<?php echo esc_attr( $card_id ); ?>" style="color:#b32d2e;border-color:#b32d2e;">
				<?php esc_html_e( 'Remove', $ADN_TD ); ?>
			</button>
			<?php endif; ?>
		</div>

	</div>
	<?php
};
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Home Page - Journey Card Images', ADN_TEXT_DOMAIN ); ?></h1>

	<?php if ( isset( $_GET['adn_saved'] ) ) : ?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'Journey card images saved.', ADN_TEXT_DOMAIN ); ?></p>
	</div>
	<?php endif; ?>

	<p class="description" style="margin:12px 0 4px;font-size:13px;">
		<?php esc_html_e( 'Set an image for each journey card shown on the home page. CMS-sourced cards show the taxonomy image by default - set an override here to replace it.', ADN_TEXT_DOMAIN ); ?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="adn_save_home_journey">
		<?php wp_nonce_field( 'adn_save_home_journey' ); ?>

		<?php /* ── Group A: CMS taxonomy cards ── */ ?>
		<?php if ( ! empty( $terms ) ) : ?>
		<h2 style="margin-top:28px;font-size:14px;text-transform:uppercase;letter-spacing:.06em;color:#50575e;">
			<?php esc_html_e( 'CMS Journey Cards', ADN_TEXT_DOMAIN ); ?>
			<span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:12px;color:#999;margin-left:8px;">
				<?php esc_html_e( '(from taxonomy - Buying, Selling, Moving…)', ADN_TEXT_DOMAIN ); ?>
			</span>
		</h2>
		<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;margin-top:12px;">
			<?php foreach ( $terms as $term ) :
				$tid      = (int) $term->id;
				$name     = isset( $term->name ) ? $term->name : '';
				$icon     = isset( $term->icon_emoji ) ? $term->icon_emoji : '';
				$saved_id = isset( $saved_term_imgs[ $tid ] ) ? (int) $saved_term_imgs[ $tid ] : 0;
				$cms_id   = ! empty( $term->image_id ) ? (int) $term->image_id : 0;
				$render_picker( 't' . $tid, $name, $icon, 'journey_images[' . $tid . ']', $saved_id, $cms_id );
			endforeach; ?>
		</div>
		<?php endif; ?>

		<?php /* ── Group B: JSON fallback cards ── */ ?>
		<?php if ( ! empty( $json_cards ) ) : ?>
		<h2 style="margin-top:36px;font-size:14px;text-transform:uppercase;letter-spacing:.06em;color:#50575e;">
			<?php esc_html_e( 'Static Cards', ADN_TEXT_DOMAIN ); ?>
			<span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:12px;color:#999;margin-left:8px;">
				<?php esc_html_e( '(Professional Help, Calculators, Guides - no CMS image)', ADN_TEXT_DOMAIN ); ?>
			</span>
		</h2>
		<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;margin-top:12px;">
			<?php foreach ( $json_cards as $jcard ) :
				$jurl     = isset( $jcard['url'] )   ? (string) $jcard['url']   : '';
				$jname    = isset( $jcard['title'] )  ? (string) $jcard['title']  : '';
				$jicon    = isset( $jcard['icon'] )   ? (string) $jcard['icon']   : '';
				if ( '' === $jname ) { continue; }
				$key      = sanitize_key( sanitize_title( $jname ) );
				$old_key  = sanitize_key( sanitize_title( trim( $jurl, '/' ) ) );
				$saved_id = isset( $saved_json_imgs[ $key ] ) 
					? (int) $saved_json_imgs[ $key ] 
					: ( ( '' !== $old_key && isset( $saved_json_imgs[ $old_key ] ) ) ? (int) $saved_json_imgs[ $old_key ] : 0 );
				$render_picker( 'j-' . $key, $jname, $jicon, 'journey_json_images[' . $key . ']', $saved_id, 0 );
			endforeach; ?>
		</div>
		<?php endif; ?>

		<p style="margin-top:28px;">
			<button type="submit" class="button button-primary" style="font-size:14px;padding:6px 18px;">
				<?php esc_html_e( 'Save All Journey Card Images', ADN_TEXT_DOMAIN ); ?>
			</button>
		</p>
	</form>

	<script>
	(function($){
		$(document).on('click', '.jny-img-select', function(){
			var cid  = $(this).data('cid');
			var card = $('#jny-card-' + cid);
			var frame = wp.media({
				title : '<?php echo esc_js( __( 'Select Journey Card Image', ADN_TEXT_DOMAIN ) ); ?>',
				button: { text: '<?php echo esc_js( __( 'Use this image', ADN_TEXT_DOMAIN ) ); ?>' },
				multiple: false,
				library : { type: 'image' }
			});
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
				card.find('.jny-img-id').val(att.id);
				card.find('.jny-img-preview').html('<img src="' + url + '" style="width:100%;height:100%;object-fit:cover;display:block;" alt="">');
				card.find('.jny-img-select').text('<?php echo esc_js( __( 'Change Image', ADN_TEXT_DOMAIN ) ); ?>');
				if ( ! card.find('.jny-img-clear').length ) {
					card.find('.jny-img-select').after(
						'<button type="button" class="button jny-img-clear" data-cid="' + cid + '" style="color:#b32d2e;border-color:#b32d2e;margin-left:8px;"><?php echo esc_js( __( 'Remove', ADN_TEXT_DOMAIN ) ); ?></button>'
					);
				}
			});
			frame.open();
		});

		$(document).on('click', '.jny-img-clear', function(){
			var cid  = $(this).data('cid');
			var card = $('#jny-card-' + cid);
			card.find('.jny-img-id').val('0');
			card.find('.jny-img-preview').html('<span style="color:#aaa;font-size:12px;"><?php echo esc_js( __( 'No image set', ADN_TEXT_DOMAIN ) ); ?></span>');
			card.find('.jny-img-select').text('<?php echo esc_js( __( 'Set Image', ADN_TEXT_DOMAIN ) ); ?>');
			$(this).remove();
		});
	}(jQuery));
	</script>

</div>
