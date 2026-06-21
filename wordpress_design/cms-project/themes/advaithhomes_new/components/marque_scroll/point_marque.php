<?php
$_trust     = $args['trust']     ?? [];
$_is_string = $args['is_string'] ?? false;
$_is_icon   = $args['is_icon']   ?? false;

if ( empty( $_trust ) ) return;
?>
<div class="page-hero-bar page-hero-bar--marquee">
    <div class="">
        <div class="page-hero-bar-inner">
            <div class="phb-marquee-track">
                <?php for ( $i = 0; $i < 4; $i++ ) : ?>
                    <?php foreach ( $_trust as $_t ) : ?>

                        <?php if ( $_is_string ) : ?>
                            <div class="phb-trust-simple">
                                <span class="phb-trust-check" aria-hidden="true">✓</span>
                                <?php echo esc_html( (string) $_t ); ?>
                            </div>

                        <?php elseif ( $_is_icon ) :
                            $_ti = adn_icon( isset( $_t['icon'] ) ? (string) $_t['icon'] : '' );
                            $_tp = esc_html( isset( $_t['label'] ) ? (string) $_t['label'] : ( isset( $_t['title'] )    ? (string) $_t['title']    : '' ) );
                            $_ts = esc_html( isset( $_t['note'] )  ? (string) $_t['note']  : ( isset( $_t['subtitle'] ) ? (string) $_t['subtitle'] : '' ) );
                        ?>
                            <div class="phb-trust-icon-item">
                                <span class="phb-trust-icon" aria-hidden="true"><?php echo $_ti; ?></span>
                                <div>
                                    <strong><?php echo $_tp; ?></strong>
                                    <?php if ( '' !== $_ts ) : ?>
                                        <span><?php echo $_ts; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php endforeach; ?>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>