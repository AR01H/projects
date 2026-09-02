/**
 * VintageSoulTheme - Cookie Consent & Preferences Management
 *
 * Rules:
 * - Reject -> Ask again after 24 Hours (24 * 60 * 60 * 1000 ms)
 * - Accept / Save -> Do NOT ask again until 30 Days (30 * 24 * 60 * 60 * 1000 ms)
 */
(function () {
  'use strict';

  var STORAGE_KEY_STATUS = 'vst_cookie_consent_status';
  var STORAGE_KEY_PREFS = 'vst_cookie_consent_prefs';
  var STORAGE_KEY_EXPIRY = 'vst_cookie_consent_expiry';

  var ONE_DAY_MS = 24 * 60 * 60 * 1000;
  var THIRTY_DAYS_MS = 30 * 24 * 60 * 60 * 1000;

  function getStorage(key) {
    try {
      return localStorage.getItem(key);
    } catch (e) {
      return null;
    }
  }

  function setStorage(key, val) {
    try {
      localStorage.setItem(key, val);
    } catch (e) {}
  }

  function initCookieConsent() {
    var banner = document.getElementById('vst-cookie-banner');
    var modal = document.getElementById('vst-cookie-modal');
    if (!banner || !modal) return;

    var btnOpenPrefs = document.getElementById('vst-cookie-open-prefs');
    var btnBannerReject = document.getElementById('vst-cookie-banner-reject');
    var btnBannerAccept = document.getElementById('vst-cookie-banner-accept');

    var btnModalClose = document.getElementById('vst-cookie-modal-close');
    var btnModalBackdrop = document.getElementById('vst-cookie-modal-backdrop');
    var btnModalReject = document.getElementById('vst-cookie-modal-reject');
    var btnModalSave = document.getElementById('vst-cookie-modal-save');
    var btnModalAccept = document.getElementById('vst-cookie-modal-accept');

    var toggleAnalytics = document.getElementById('vst-cookie-toggle-analytics');
    var toggleAdvertising = document.getElementById('vst-cookie-toggle-advertising');

    // 1. Check current consent state and expiry
    var expiry = parseInt(getStorage(STORAGE_KEY_EXPIRY) || '0', 10);
    var now = Date.now();

    if (!expiry || now > expiry) {
      // Expiry reached or no decision made yet -> Show banner after subtle delay
      setTimeout(function () {
        banner.classList.remove('is-hidden');
        banner.classList.add('is-visible');
      }, 700);
    }

    // Load saved preferences if any
    try {
      var savedPrefs = JSON.parse(getStorage(STORAGE_KEY_PREFS) || '{}');
      if (toggleAnalytics) toggleAnalytics.checked = !!savedPrefs.analytics;
      if (toggleAdvertising) toggleAdvertising.checked = !!savedPrefs.advertising;
    } catch (e) {}

    // Modal open/close handlers
    function openModal() {
      modal.classList.remove('is-hidden');
      modal.classList.add('is-visible');
      document.body.classList.add('cookie-modal-open');
    }

    function closeModal() {
      modal.classList.remove('is-visible');
      setTimeout(function () {
        modal.classList.add('is-hidden');
        document.body.classList.remove('cookie-modal-open');
      }, 250);
    }

    function hideBanner() {
      banner.classList.remove('is-visible');
      setTimeout(function () {
        banner.classList.add('is-hidden');
      }, 300);
    }

    // Decision Handlers
    function handleAcceptAll() {
      var expiryTime = Date.now() + THIRTY_DAYS_MS;
      var prefs = { necessary: true, analytics: true, advertising: true };

      setStorage(STORAGE_KEY_STATUS, 'accepted');
      setStorage(STORAGE_KEY_PREFS, JSON.stringify(prefs));
      setStorage(STORAGE_KEY_EXPIRY, expiryTime.toString());

      if (toggleAnalytics) toggleAnalytics.checked = true;
      if (toggleAdvertising) toggleAdvertising.checked = true;

      closeModal();
      hideBanner();
    }

    function handleRejectAll() {
      var expiryTime = Date.now() + ONE_DAY_MS;
      var prefs = { necessary: true, analytics: false, advertising: false };

      setStorage(STORAGE_KEY_STATUS, 'rejected');
      setStorage(STORAGE_KEY_PREFS, JSON.stringify(prefs));
      setStorage(STORAGE_KEY_EXPIRY, expiryTime.toString());

      if (toggleAnalytics) toggleAnalytics.checked = false;
      if (toggleAdvertising) toggleAdvertising.checked = false;

      closeModal();
      hideBanner();
    }

    function handleSavePreferences() {
      var analytics = toggleAnalytics ? toggleAnalytics.checked : false;
      var advertising = toggleAdvertising ? toggleAdvertising.checked : false;
      var prefs = { necessary: true, analytics: analytics, advertising: advertising };

      // If user accepted optional cookies, give 30 days. If all optional rejected, ask again in 24 hours.
      var hasOptional = analytics || advertising;
      var duration = hasOptional ? THIRTY_DAYS_MS : ONE_DAY_MS;
      var expiryTime = Date.now() + duration;

      setStorage(STORAGE_KEY_STATUS, hasOptional ? 'custom' : 'rejected');
      setStorage(STORAGE_KEY_PREFS, JSON.stringify(prefs));
      setStorage(STORAGE_KEY_EXPIRY, expiryTime.toString());

      closeModal();
      hideBanner();
    }

    // Bind Banner Buttons
    if (btnOpenPrefs) btnOpenPrefs.addEventListener('click', openModal);
    if (btnBannerReject) btnBannerReject.addEventListener('click', handleRejectAll);
    if (btnBannerAccept) btnBannerAccept.addEventListener('click', handleAcceptAll);

    // Bind Modal Buttons
    if (btnModalClose) btnModalClose.addEventListener('click', closeModal);
    if (btnModalBackdrop) btnModalBackdrop.addEventListener('click', closeModal);
    if (btnModalReject) btnModalReject.addEventListener('click', handleRejectAll);
    if (btnModalSave) btnModalSave.addEventListener('click', handleSavePreferences);
    if (btnModalAccept) btnModalAccept.addEventListener('click', handleAcceptAll);

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal.classList.contains('is-visible')) {
        closeModal();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCookieConsent);
  } else {
    initCookieConsent();
  }
})();
