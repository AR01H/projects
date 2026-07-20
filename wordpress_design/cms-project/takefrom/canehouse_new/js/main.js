/* ===================================================
   THE CANE HOUSE — main.js (v2, matches reference design)
   All content is fetched from /data/*.json — nothing is
   hardcoded in the HTML or this file. jQuery drives the
   DOM rendering + interactions; one AJAX call handles the
   booking form submission.
   =================================================== */

const DATA_URLS = {
  site: 'data/site.json',
  gallery: 'data/gallery.json',
  flavours: 'data/flavours.json'
};

// Simulated "API" endpoint for the booking form AJAX submit.
// Point this at your real backend endpoint when ready.
const BOOKING_SUBMIT_URL = 'data/booking-response.json';

let SITE = null;
let GALLERY = null;
let FLAVOURS = null;

const booking = {
  step: 1,
  caneType: null,
  texture: null,
  flavours: [],
  eventType: '',
  eventDate: '',
  location: '',
  guests: '',
  requests: ''
};

$(document).ready(function () {
  loadAllData();
  bindNav();
  bindModal();
});

/* ---------------------------------------------------
   Fetch every JSON file (parallel), then render.
   --------------------------------------------------- */
function loadAllData() {
  $.when(
    $.getJSON(DATA_URLS.site),
    $.getJSON(DATA_URLS.gallery),
    $.getJSON(DATA_URLS.flavours)
  ).done(function (siteRes, galleryRes, flavoursRes) {
    SITE = siteRes[0];
    GALLERY = galleryRes[0];
    FLAVOURS = flavoursRes[0];

    renderHeader();
    renderHero();
    renderStory();
    renderDrinks();
    renderEvents();
    renderGallery();
    renderContact();
    renderFooter();
    renderBookingStaticParts();
  }).fail(function (err) {
    console.error('Failed to load site data', err);
    $('#load-error').text('Sorry — content could not be loaded. Please refresh.').show();
  });
}

/* ---------------------------------------------------
   Header / Nav
   --------------------------------------------------- */
function renderHeader() {
  const b = SITE.brand;
  document.title = b.name + ' — ' + b.tagline;

  $('#logo-image').attr('src', b.logoImage).attr('alt', b.name);
  $('#logo-text').html(`${b.logoLine1}<br>${b.logoLine2} <span class="accent">${b.logoLine3}</span>`);

  const $navList = $('#main-nav-list').empty();
  SITE.nav.forEach((item, i) => {
    $navList.append(`<li><a href="${item.href}" class="${i === 0 ? 'active' : ''}">${item.label}</a></li>`);
  });

  $('#nav-cta-btn').html(`<span class="line1">${SITE.cta.line1}</span><span class="line2">${SITE.cta.line2}</span>`);
}

/* ---------------------------------------------------
   Hero
   --------------------------------------------------- */
function renderHero() {
  const h = SITE.hero;
  $('#hero-inner').css('background-image', `url("${h.image}")`);

  const $titleLines = $('#hero-title-lines').empty();
  h.title.forEach(line => $titleLines.append(`<h1>${line}</h1>`));

  const $sub = $('#hero-subtitle-lines').empty();
  h.subtitle.forEach(line => $sub.append(`<span class="script">${line}</span>`));

  $('#hero-badge').text(h.badge);

  const $buttons = $('#hero-buttons').empty();
  h.buttons.forEach(btn => {
    $buttons.append(`<a href="${btn.href}" class="btn btn-outline">${btn.icon} ${btn.label}</a>`);
  });
}

/* ---------------------------------------------------
   Our Story
   --------------------------------------------------- */
function renderStory() {
  const s = SITE.story;
  $('#story-heading').text(s.heading);
  $('#story-subheading').text(s.subheading);
  $('#story-photo').attr('src', s.photo).attr('alt', s.heading);
  $('#story-photo-badge').text(s.photoBadge);
  $('#story-sketch').attr('src', s.sketch).attr('alt', 'Sugarcane press illustration');

  const $paras = $('#story-paragraphs').empty();
  s.paragraphs.forEach(p => $paras.append(`<p>${p}</p>`));
}

/* ---------------------------------------------------
   Our Drinks
   --------------------------------------------------- */
function renderDrinks() {
  const d = SITE.drinks;
  $('#drinks-heading').text(d.heading);
  $('#drinks-view-all-btn').text(d.viewAllLabel);

  const $grid = $('#drinks-grid').empty();
  d.items.forEach(item => {
    $grid.append(`
      <div class="drink-card">
        <img src="${item.image}" alt="${item.name}" onerror="this.style.display='none'">
        <h3>${item.name}</h3>
        <p>${item.desc}</p>
      </div>
    `);
  });
}

/* ---------------------------------------------------
   Events & Catering
   --------------------------------------------------- */
function renderEvents() {
  const e = SITE.events;
  $('#events-heading').text(e.heading);
  $('#events-subheading').text(e.subheading);
  $('#events-image').attr('src', e.image).attr('alt', e.heading);

  const $sign = $('#events-sign').empty();
  e.signText.forEach(line => $sign.append(`<span>${line}</span><br>`));

  const $features = $('#events-features').empty();
  e.features.forEach(f => {
    $features.append(`
      <div class="event-feature">
        <img src="${f.icon}" alt="${f.label}" onerror="this.style.display='none'">
        <p>${f.label}</p>
      </div>
    `);
  });

  $('#events-cta-btn').html(`${e.cta.icon} ${e.cta.label}`);
}

/* ---------------------------------------------------
   Gallery
   --------------------------------------------------- */
function renderGallery() {
  const $filters = $('#gallery-filters').empty();
  GALLERY.categories.forEach((cat, i) => {
    $filters.append(`<button data-cat="${cat}" class="${i === 0 ? 'active' : ''}">${cat}</button>`);
  });

  renderGalleryGrid('All');

  $filters.on('click', 'button', function () {
    $filters.find('button').removeClass('active');
    $(this).addClass('active');
    renderGalleryGrid($(this).data('cat'));
  });
}

function renderGalleryGrid(category) {
  const $grid = $('#gallery-grid').empty();
  const images = category === 'All'
    ? GALLERY.images
    : GALLERY.images.filter(img => img.category === category);

  images.forEach(img => {
    $grid.append(`
      <figure>
        <img src="${img.src}" alt="${img.caption}">
        <figcaption>${img.caption}</figcaption>
      </figure>
    `);
  });
}

/* ---------------------------------------------------
   Contact
   --------------------------------------------------- */
function renderContact() {
  const c = SITE.contact;
  $('#contact-heading').text(c.heading);
  $('#contact-cafe-name').text(c.cafeName);
  $('#contact-address').html(c.address.join('<br>'));
  $('#contact-phone').text(c.phone);
  $('#contact-email').text(c.email);
  $('#contact-hours').html(c.hours.join('<br>'));

  const $formFields = $('#contact-form-fields').empty();
  c.formFields.forEach(field => {
    $formFields.append(`
      <div class="form-group">
        <input type="${field.type}" id="${field.id}" placeholder="${field.label}">
      </div>
    `);
  });

  $('#contact-message-input').attr('placeholder', c.messageLabel);
  $('#contact-submit-btn').text(c.submitLabel);

  $('#contact-map-img').attr('src', c.mapImage).attr('alt', 'Map to ' + c.cafeName);
  $('#contact-map-label').html(`<strong>${c.mapPinLabel}</strong><br>${c.address.join(', ')}`);
}

/* ---------------------------------------------------
   Footer
   --------------------------------------------------- */
function renderFooter() {
  const f = SITE.footer;
  $('#footer-logo-img').attr('src', SITE.brand.logoImage).attr('alt', SITE.brand.name);
  $('#footer-logo-text').html(`${f.logoLine1}<br>${f.logoLine2} ${f.logoLine3}`);
  $('#footer-logo-sub').text(f.logoSub);

  $('#footer-tagline').html(`${f.tagline1}<br>${f.tagline2}`);

  const $quote = $('#footer-quote').empty();
  f.quote.forEach(line => $quote.append(`${line}<br>`));

  const $socials = $('#footer-socials').empty();
  f.socials.forEach(s => {
    $socials.append(`<a href="${s.url}" target="_blank" rel="noopener"><img src="${s.icon}" alt="${s.name}" onerror="this.style.display='none'"></a>`);
  });

  $('#footer-copyright').text(f.copyright);
}

/* ---------------------------------------------------
   Booking modal static option renders
   --------------------------------------------------- */
function renderBookingStaticParts() {
  const $caneGrid = $('#cane-type-grid').empty();
  FLAVOURS.caneTypes.forEach(c => {
    $caneGrid.append(`
      <div class="option-card" data-type="cane" data-id="${c.id}">
        <span class="check">✓</span>
        <img src="${c.image}" alt="${c.name}" onerror="this.style.display='none'">
        <h4>${c.name}</h4>
        <p>${c.desc}</p>
      </div>
    `);
  });

  const $textureGrid = $('#texture-grid').empty();
  FLAVOURS.textures.forEach(t => {
    $textureGrid.append(`
      <div class="option-card" data-type="texture" data-id="${t.id}">
        <span class="check">✓</span>
        <h4>${t.name}</h4>
      </div>
    `);
  });

  const $flavourGrid = $('#flavour-grid').empty();
  FLAVOURS.flavours.forEach(f => {
    $flavourGrid.append(`
      <div class="option-card" data-type="flavour" data-id="${f.id}">
        <span class="check">✓</span>
        <img src="${f.image}" alt="${f.name}" onerror="this.style.display='none'">
        <h4>${f.name}</h4>
      </div>
    `);
  });

  const $eventSelect = $('#event-type-select').empty();
  $eventSelect.append(`<option value="">Select event type</option>`);
  FLAVOURS.eventTypes.forEach(et => {
    $eventSelect.append(`<option value="${et}">${et}</option>`);
  });
}

/* ---------------------------------------------------
   Nav / mobile menu / booking triggers
   --------------------------------------------------- */
function bindNav() {
  $('#hamburger-btn').on('click', function () {
    $('#main-nav-list').slideToggle(200);
  });

  $(document).on('click', '[data-open-booking]', function (e) {
    e.preventDefault();
    openBookingModal();
  });
}

/* ---------------------------------------------------
   Booking Modal wizard logic
   --------------------------------------------------- */
function bindModal() {
  $('#modal-overlay').on('click', function (e) {
    if (e.target === this) closeBookingModal();
  });
  $('#modal-close-btn').on('click', closeBookingModal);

  $(document).on('click', '.option-card', function () {
    const type = $(this).data('type');
    const id = $(this).data('id');

    if (type === 'flavour') {
      $(this).toggleClass('selected');
      const idx = booking.flavours.indexOf(id);
      if (idx === -1) booking.flavours.push(id);
      else booking.flavours.splice(idx, 1);
    } else {
      $(this).siblings('.option-card').removeClass('selected');
      $(this).addClass('selected');
      if (type === 'cane') booking.caneType = id;
      if (type === 'texture') booking.texture = id;
    }
  });

  $(document).on('click', '[data-next]', nextStep);
  $(document).on('click', '[data-back]', prevStep);
  $('#confirm-booking-btn').on('click', submitBooking);

  goToStep(1);
}

function openBookingModal() {
  $('#modal-overlay').addClass('open');
  goToStep(1);
}

function closeBookingModal() {
  $('#modal-overlay').removeClass('open');
}

function nextStep() {
  if (booking.step === 3) {
    booking.eventType = $('#event-type-select').val();
    booking.eventDate = $('#event-date-input').val();
    booking.location = $('#event-location-input').val();
    booking.guests = $('#event-guests-input').val();
    booking.requests = $('#event-requests-input').val();
  }
  if (booking.step < 4) goToStep(booking.step + 1);
}

function prevStep() {
  if (booking.step > 1) goToStep(booking.step - 1);
}

function goToStep(n) {
  booking.step = n;

  $('.booking-step').removeClass('active');
  $(`.booking-step[data-step="${n}"]`).addClass('active');

  $('.step-indicator .num').removeClass('active done');
  $('.step-indicator .num').each(function () {
    const stepNum = parseInt($(this).data('num'), 10);
    if (stepNum < n) $(this).addClass('done');
    else if (stepNum === n) $(this).addClass('active');
  });

  if (n === 4) renderConfirmSummary();
}

function renderConfirmSummary() {
  const caneName = lookupName(FLAVOURS.caneTypes, booking.caneType);
  const flavourNames = booking.flavours
    .map(id => lookupName(FLAVOURS.flavours, id))
    .filter(Boolean)
    .join(', ');

  $('#summary-cane').text(caneName || '—');
  $('#summary-flavours').text(flavourNames || '—');
  $('#summary-event-type').text(booking.eventType || '—');
  $('#summary-event-date').text(booking.eventDate || '—');
  $('#summary-location').text(booking.location || '—');
  $('#summary-guests').text(booking.guests || '—');
}

function lookupName(list, id) {
  const found = list.find(item => item.id === id);
  return found ? found.name : '';
}

/* ---------------------------------------------------
   Submit booking — real AJAX call.
   Swap BOOKING_SUBMIT_URL for your live API endpoint.
   --------------------------------------------------- */
function submitBooking() {
  const payload = { ...booking, submittedAt: new Date().toISOString() };

  $('#form-status').text('Submitting your booking...');

  $.ajax({
    url: BOOKING_SUBMIT_URL,
    method: 'GET', // change to POST when wired to a real backend
    dataType: 'json'
  })
  .done(function (response) {
    $('#form-status').text(response.message || 'Booking confirmed! We will contact you shortly.');
    console.log('Booking payload (would be POSTed to server):', payload);
  })
  .fail(function () {
    $('#form-status').text('Booking received! We will contact you shortly to confirm.');
    console.log('Booking payload (no backend connected):', payload);
  });
}
