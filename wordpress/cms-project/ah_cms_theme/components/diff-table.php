<?php defined( 'ABSPATH' ) || exit;

$rows = [
	[ __( 'Works exclusively for the buyer', 'ah-theme' ),     __( 'Always', 'ah-theme' ),            __( 'Never', 'ah-theme' ) ],
	[ __( 'Independent property research', 'ah-theme' ),       __( 'Full report', 'ah-theme' ),       __( 'Not provided', 'ah-theme' ) ],
	[ __( 'Price negotiation on your behalf', 'ah-theme' ),    __( 'Expert negotiation', 'ah-theme' ), __( 'Negotiates for seller', 'ah-theme' ) ],
	[ __( 'Access to off-market properties', 'ah-theme' ),     __( 'Exclusive network', 'ah-theme' ), __( 'Listed only', 'ah-theme' ) ],
	[ __( 'Legal & survey guidance', 'ah-theme' ),             __( 'Full support', 'ah-theme' ),      __( 'Not their role', 'ah-theme' ) ],
	[ __( 'Unbiased advice on every property', 'ah-theme' ),   __( 'Always honest', 'ah-theme' ),    __( 'Motivated to sell', 'ah-theme' ) ],
	[ __( 'Post-offer completion support', 'ah-theme' ),       __( 'Until keys in hand', 'ah-theme' ), __( 'Rarely', 'ah-theme' ) ],
];
?>
<section class="section">
  <div class="container">
    <div style="text-align:center;max-width:640px;margin:0 auto">
      <div class="eyebrow reveal"><?php esc_html_e( 'The Difference', 'ah-theme' ); ?></div>
      <h2 class="reveal reveal-delay-1"><?php esc_html_e( 'What Makes Us Different from Estate Agents', 'ah-theme' ); ?></h2>
      <p class="reveal reveal-delay-2">
        <?php esc_html_e( "Estate agents are paid by sellers. We're paid by you — which means our interests are completely aligned with yours.", 'ah-theme' ); ?>
      </p>
    </div>

    <div class="diff-table reveal reveal-delay-2">
      <div class="diff-table__header">
        <div class="diff-table__header-cell"><?php esc_html_e( 'Feature', 'ah-theme' ); ?></div>
        <div class="diff-table__header-cell diff-table__header-cell--highlight">✦ <?php esc_html_e( 'Advaith Homes', 'ah-theme' ); ?></div>
        <div class="diff-table__header-cell"><?php esc_html_e( 'Estate Agent', 'ah-theme' ); ?></div>
      </div>
      <?php foreach ( $rows as $row ) : ?>
        <div class="diff-table__row">
          <div class="diff-table__cell diff-table__cell--feature"><?php echo esc_html( $row[0] ); ?></div>
          <div class="diff-table__cell"><span class="check">✔</span> <?php echo esc_html( $row[1] ); ?></div>
          <div class="diff-table__cell"><span class="cross">✘</span> <?php echo esc_html( $row[2] ); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
