<?php
/**
 * components/sections/article_feedback.php — Article feedback row + share buttons.
 *
 * Props: $feedback { question, yes_label, no_label, share_label }
 * Usage: adn_component( 'sections/article_feedback', array( 'feedback' => $ctx['feedback'] ) );
 */

defined( 'ABSPATH' ) || exit;

$feedback = isset( $feedback ) && is_array( $feedback ) ? $feedback : array();
?>
<div class="article-feedback">
	<div>
		<div class="feedback-question">
			<?php echo esc_html( isset( $feedback['question'] ) ? $feedback['question'] : '' ); ?>
		</div>
	</div>
	<div class="feedback-btns">
		<button class="feedback-btn" type="button"><?php echo esc_html( isset( $feedback['yes_label'] ) ? $feedback['yes_label'] : '👍 Yes' ); ?></button>
		<button class="feedback-btn" type="button"><?php echo esc_html( isset( $feedback['no_label'] ) ? $feedback['no_label'] : '👎 No' ); ?></button>
	</div>
	<div class="share-row">
		<span class="share-label"><?php echo esc_html( isset( $feedback['share_label'] ) ? $feedback['share_label'] : 'Share this guide' ); ?></span>
		<button class="share-btn share-fb" type="button" aria-label="<?php esc_attr_e( 'Share on Facebook', ADN_TEXT_DOMAIN ); ?>">f</button>
		<button class="share-btn share-x" type="button" aria-label="<?php esc_attr_e( 'Share on X', ADN_TEXT_DOMAIN ); ?>">&#x1D54F;</button>
		<button class="share-btn share-li" type="button" aria-label="<?php esc_attr_e( 'Share on LinkedIn', ADN_TEXT_DOMAIN ); ?>">in</button>
		<button class="share-btn share-wa" type="button" aria-label="<?php esc_attr_e( 'Share on WhatsApp', ADN_TEXT_DOMAIN ); ?>">W</button>
	</div>
</div>
