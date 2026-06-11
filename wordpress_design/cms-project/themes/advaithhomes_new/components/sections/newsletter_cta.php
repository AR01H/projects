<?php
/**
 * components/sections/newsletter_cta.php — Section: Newsletter subscribe CTA
 *
 * Props: $newsletter { icon, title, description, placeholder, button_label, note }
 * Usage: adn_component( 'sections/newsletter_cta', array( 'newsletter' => $ctx['newsletter'] ) );
 *
 * NOTE: the form is presentational for now (no endpoint). Wire it to a REST
 * route later the same way the contact form posts to /contact.
 */

defined( 'ABSPATH' ) || exit;

$newsletter = isset( $newsletter ) && is_array( $newsletter ) ? $newsletter : array();
?>
<div class="newsletter-inner">
    <div>
        <div class="newsletter-icon"><?php echo esc_html( isset( $newsletter['icon'] ) ? $newsletter['icon'] : '' ); ?></div>
        <h3><?php echo esc_html( isset( $newsletter['title'] ) ? $newsletter['title'] : '' ); ?></h3>
        <p><?php echo esc_html( isset( $newsletter['description'] ) ? $newsletter['description'] : '' ); ?></p>
    </div>
    <div class="newsletter-form-wrap">
        <form class="newsletter-form" onsubmit="return false;">
            <input type="email"
                   placeholder="<?php echo esc_attr( isset( $newsletter['placeholder'] ) ? $newsletter['placeholder'] : '' ); ?>"
                   aria-label="<?php echo esc_attr( isset( $newsletter['placeholder'] ) ? $newsletter['placeholder'] : 'Email address' ); ?>" />
            <button type="submit" class="btn btn-accent"><?php echo esc_html( isset( $newsletter['button_label'] ) ? $newsletter['button_label'] : '' ); ?></button>
        </form>
        <div class="newsletter-spam"><?php echo esc_html( isset( $newsletter['note'] ) ? $newsletter['note'] : '' ); ?></div>
    </div>
</div>
