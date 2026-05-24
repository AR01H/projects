<?php
/**
 * Component: NIF Sidebar - Market Pulse
 *
 * @var array $args {
 *   @type array $site_stats  From ah_get_site_stats(). Each item:
 *                            [ 'label' => string, 'value'|'val' => string, 'trend' => 'up'|'down'|'' ]
 * }
 */
defined( 'ABSPATH' ) || exit;

$site_stats = $args['site_stats'] ?? [];

if ( empty( $site_stats ) ) {
	return;
}
?>
<div class="nif-sb-card nif-sb-card--stats" aria-label="<?php esc_attr_e( 'Market Pulse', 'ah-theme' ); ?>">
  <div class="nif-sb-card__header">
    <span class="nif-section-label--primary"><?php esc_html_e( 'Market Pulse', 'ah-theme' ); ?></span>
  </div>
  <div class="nif-sb-stats">
    <?php foreach ( $site_stats as $stat ) :
      $stat  = is_object( $stat ) ? (array) $stat : $stat;
      $label = $stat['label'] ?? '';
      $val   = $stat['value'] ?? ( $stat['val'] ?? '' );
      $trend = $stat['trend'] ?? '';
      if ( ! $label || ! $val ) continue;
    ?>
    <div class="nif-sb-stat">
      <span class="nif-sb-stat__label"><?php echo esc_html( $label ); ?></span>
      <span class="nif-sb-stat__value">
        <?php echo esc_html( $val ); ?>
        <?php if ( $trend === 'up' ) : ?>
          <svg class="nif-trend nif-trend--up" width="12" height="12" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5" aria-label="<?php esc_attr_e( 'up', 'ah-theme' ); ?>">
            <polyline points="18 15 12 9 6 15"/>
          </svg>
        <?php elseif ( $trend === 'down' ) : ?>
          <svg class="nif-trend nif-trend--down" width="12" height="12" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5" aria-label="<?php esc_attr_e( 'down', 'ah-theme' ); ?>">
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        <?php endif; ?>
      </span>
    </div>
    <?php endforeach; ?>
  </div>
</div>