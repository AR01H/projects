/**
 * Live Vintage Time, Weather & Location Component
 * 
 * Features:
 * - 24-Hour live updating laptop clock
 * - Automatic IP / Timezone city detection
 * - Real-time weather condition & icon (Sunny, Rain, Night, Clouds, etc.)
 * - Offline/Cached resilient local storage
 */

(function () {
  'use strict';

  function initLiveBadge() {
    var timeEl = document.getElementById('vst-live-time');
    var locEl = document.querySelector('#vst-live-location .vst-live-badge__text');
    var weatherIconEl = document.getElementById('vst-weather-icon');
    var weatherTextEl = document.getElementById('vst-weather-text');

    if (!timeEl) return;

    // 1. Live 24-Hour Laptop Clock (Hours & Minutes Only)
    function updateClock() {
      var now = new Date();
      var hours = String(now.getHours()).padStart(2, '0');
      var minutes = String(now.getMinutes()).padStart(2, '0');
      timeEl.textContent = hours + ':' + minutes;
    }

    updateClock();
    setInterval(updateClock, 1000);

    // 2. Weather mapping helper based on WMO code & day/night
    function parseWeather(code, isDay, temp) {
      var roundedTemp = Math.round(temp);
      var icon = isDay ? '☀️' : '🌙';

      if (code === 0) {
        icon = isDay ? '☀️' : '🌙';
      } else if (code >= 1 && code <= 3) {
        icon = isDay ? '⛅' : '☁️';
      } else if (code >= 45 && code <= 48) {
        icon = '🌫️';
      } else if (code >= 51 && code <= 67) {
        icon = '🌧️';
      } else if (code >= 71 && code <= 77) {
        icon = '❄️';
      } else if (code >= 80 && code <= 82) {
        icon = '🌦️';
      } else if (code >= 95 && code <= 99) {
        icon = '⛈️';
      }

      return {
        icon: icon,
        text: roundedTemp + '°C'
      };
    }

    // 3. Fallback based on client local time & season
    function fallbackWeather() {
      var now = new Date();
      var h = now.getHours();
      var isDay = h >= 6 && h < 20;
      var icon = isDay ? '☀️' : '🌙';
      var temp = isDay ? 22 : 16;
      return { icon: icon, text: temp + '°C' };
    }

    // 4. Fetch Location & Weather from Free APIs with 10-minute cache
    var CACHE_KEY = 'vst_weather_cache_v3';
    var cached = null;
    try {
      cached = JSON.parse(localStorage.getItem(CACHE_KEY) || 'null');
    } catch (e) {}

    var nowTs = Date.now();
    if (cached && (nowTs - cached.timestamp < 600000)) {
      if (locEl && cached.city) locEl.textContent = cached.city;
      if (weatherIconEl && cached.weather) weatherIconEl.textContent = cached.weather.icon;
      if (weatherTextEl && cached.weather) weatherTextEl.textContent = cached.weather.text;
      return;
    }

    // Detect location via IP Geolocation API
    fetch('https://ipapi.co/json/')
      .then(function (res) { return res.json(); })
      .then(function (geo) {
        var city = geo.city ? (geo.city + (geo.country_code ? ', ' + geo.country_code : '')) : 'Sutton, UK';
        var lat = geo.latitude || 51.3614;
        var lon = geo.longitude || -0.1942;

        if (locEl) locEl.textContent = city;

        // Fetch current live weather from Open-Meteo
        return fetch('https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lon + '&current_weather=true')
          .then(function (res) { return res.json(); })
          .then(function (wData) {
            var cw = wData.current_weather || {};
            var code = typeof cw.weathercode === 'number' ? cw.weathercode : 0;
            var isDay = typeof cw.is_day === 'number' ? (cw.is_day === 1) : (new Date().getHours() >= 6 && new Date().getHours() < 20);
            var temp = typeof cw.temperature === 'number' ? cw.temperature : 20;

            var result = parseWeather(code, isDay, temp);

            if (weatherIconEl) weatherIconEl.textContent = result.icon;
            if (weatherTextEl) weatherTextEl.textContent = result.text;

            try {
              localStorage.setItem(CACHE_KEY, JSON.stringify({
                timestamp: nowTs,
                city: city,
                weather: result
              }));
            } catch (e) {}
          });
      })
      .catch(function () {
        // Safe graceful offline fallback
        var def = fallbackWeather();
        if (weatherIconEl) weatherIconEl.textContent = def.icon;
        if (weatherTextEl) weatherTextEl.textContent = def.text;
        if (locEl && !locEl.textContent) locEl.textContent = 'Sutton, London';
      });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLiveBadge);
  } else {
    initLiveBadge();
  }
})();
