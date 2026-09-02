<?php
/**
 * VintageSoulTheme - Cookie Consent Banner & Preferences Modal Component
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;
?>
<!-- Bottom Cookie Consent Banner -->
<aside class="vst-cookie-banner is-hidden" id="vst-cookie-banner" role="region" aria-label="<?php esc_attr_e( 'Cookie Consent', 'vintagesoul' ); ?>">
	<div class="container vst-cookie-banner__container">
		<div class="vst-cookie-banner__content">
			<p class="vst-cookie-banner__text">
				<?php esc_html_e( 'We use cookies to remember your preferences and improve your experience on our site. Read our', 'vintagesoul' ); ?>
				<a href="<?php echo esc_url( RouteService::url( 'about' ) ); ?>" class="vst-cookie-banner__policy-link"><?php esc_html_e( 'Cookie Policy', 'vintagesoul' ); ?></a>
				<?php esc_html_e( 'to learn more.', 'vintagesoul' ); ?>
			</p>
			<button type="button" class="vst-cookie-banner__manage-link" id="vst-cookie-open-prefs">
				<?php esc_html_e( 'Manage Preferences', 'vintagesoul' ); ?>
			</button>
		</div>
		<div class="vst-cookie-banner__actions">
			<button type="button" class="btn btn--outline-vintage vst-cookie-banner__btn-reject" id="vst-cookie-banner-reject">
				<span><?php esc_html_e( 'Reject', 'vintagesoul' ); ?></span>
			</button>
			<button type="button" class="btn btn--primary-vintage vst-cookie-banner__btn-accept" id="vst-cookie-banner-accept">
				<span><?php esc_html_e( 'Accept All', 'vintagesoul' ); ?></span>
			</button>
		</div>
	</div>
</aside>

<!-- Cookie Preferences Modal -->
<div class="vst-cookie-modal is-hidden" id="vst-cookie-modal" role="dialog" aria-modal="true" aria-labelledby="vst-cookie-modal-title">
	<div class="vst-cookie-modal__backdrop" id="vst-cookie-modal-backdrop"></div>
	<div class="vst-cookie-modal__dialog">
		<div class="vst-cookie-modal__panel">
			<!-- Close Button -->
			<button type="button" class="vst-cookie-modal__close" id="vst-cookie-modal-close" aria-label="<?php esc_attr_e( 'Close cookie preferences', 'vintagesoul' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<line x1="18" y1="6" x2="6" y2="18"></line>
					<line x1="6" y1="6" x2="18" y2="18"></line>
				</svg>
			</button>

			<!-- Header -->
			<div class="vst-cookie-modal__header">
				<h2 class="vst-cookie-modal__title" id="vst-cookie-modal-title"><?php esc_html_e( 'Cookie Preferences', 'vintagesoul' ); ?></h2>
				<p class="vst-cookie-modal__subtitle">
					<?php esc_html_e( 'Choose which optional cookies we can use. Read our', 'vintagesoul' ); ?>
					<a href="<?php echo esc_url( RouteService::url( 'about' ) ); ?>"><?php esc_html_e( 'Cookie Policy', 'vintagesoul' ); ?></a>
					<?php esc_html_e( 'for full details.', 'vintagesoul' ); ?>
				</p>
			</div>

			<!-- Preferences List -->
			<div class="vst-cookie-modal__body">
				
				<!-- 1. Necessary -->
				<div class="vst-cookie-pref-item">
					<div class="vst-cookie-pref-item__header">
						<div class="vst-cookie-pref-item__title-wrap">
							<h3 class="vst-cookie-pref-item__title"><?php esc_html_e( 'Necessary', 'vintagesoul' ); ?></h3>
						</div>
						<span class="vst-cookie-badge-active"><?php esc_html_e( 'ALWAYS ACTIVE', 'vintagesoul' ); ?></span>
					</div>
					<p class="vst-cookie-pref-item__desc">
						<?php esc_html_e( 'Required for the site to work - security, language choice, session handling. These cannot be turned off.', 'vintagesoul' ); ?>
					</p>
				</div>

				<!-- 2. Analytics -->
				<div class="vst-cookie-pref-item">
					<div class="vst-cookie-pref-item__header">
						<div class="vst-cookie-pref-item__title-wrap">
							<label for="vst-cookie-toggle-analytics" class="vst-cookie-pref-item__title"><?php esc_html_e( 'Analytics', 'vintagesoul' ); ?></label>
						</div>
						<label class="vst-cookie-switch">
							<input type="checkbox" id="vst-cookie-toggle-analytics">
							<span class="vst-cookie-slider" aria-hidden="true"></span>
						</label>
					</div>
					<p class="vst-cookie-pref-item__desc">
						<?php esc_html_e( 'Helps us understand how visitors use the site, so we can improve it.', 'vintagesoul' ); ?>
					</p>
				</div>

				<!-- 3. Advertising -->
				<div class="vst-cookie-pref-item">
					<div class="vst-cookie-pref-item__header">
						<div class="vst-cookie-pref-item__title-wrap">
							<label for="vst-cookie-toggle-advertising" class="vst-cookie-pref-item__title"><?php esc_html_e( 'Advertising', 'vintagesoul' ); ?></label>
						</div>
						<label class="vst-cookie-switch">
							<input type="checkbox" id="vst-cookie-toggle-advertising">
							<span class="vst-cookie-slider" aria-hidden="true"></span>
						</label>
					</div>
					<p class="vst-cookie-pref-item__desc">
						<?php esc_html_e( 'Used to measure and personalise the ads you may see.', 'vintagesoul' ); ?>
					</p>
				</div>

			</div>

			<!-- Modal Actions -->
			<div class="vst-cookie-modal__footer">
				<button type="button" class="btn btn--outline-vintage vst-cookie-modal__btn-reject" id="vst-cookie-modal-reject">
					<span><?php esc_html_e( 'Reject All', 'vintagesoul' ); ?></span>
				</button>
				<button type="button" class="btn btn--secondary-vintage vst-cookie-modal__btn-save" id="vst-cookie-modal-save">
					<span><?php esc_html_e( 'Save Preferences', 'vintagesoul' ); ?></span>
				</button>
				<button type="button" class="btn btn--primary-vintage vst-cookie-modal__btn-accept" id="vst-cookie-modal-accept">
					<span><?php esc_html_e( 'Accept All', 'vintagesoul' ); ?></span>
				</button>
			</div>

		</div>
	</div>
</div>
