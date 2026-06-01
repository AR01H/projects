# Event Booking Form - Practical Examples

This document shows real-world examples of how to add booking forms to Canehouse pages using the `event-booking-form` component.

---

## Example 1: Simple Booking Form on Events Page

**File:** `page-events.php`

```php
<?php
get_header();
?>

<main class="ch-main">

  <!-- Hero Section -->
  <section class="ch-page-hero">
    <div class="container">
      <h1>Event Hire Packages</h1>
      <p>Request a quote for your event</p>
    </div>
  </section>

  <!-- Event Showcase with Booking Forms -->
  <section style="padding: 4rem 2rem; background: #fff;">
    <div class="container">
      <h2 style="margin-bottom: 3rem; text-align: center;">Our Packages</h2>

      <?php
      // Get all active events
      $events_model = new AH_Events_Model();
      $events = $events_model->get_active();

      foreach ( $events as $event ) :
      ?>
        <div style="
          border: 1px solid #e5e7eb;
          border-radius: 12px;
          padding: 2rem;
          margin-bottom: 3rem;
          background: #fafafa;
        ">
          <!-- Event Details -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 2rem;">
            <div>
              <div style="font-size: 3rem; margin-bottom: 1rem;">
                <?php echo esc_html( $event->icon ); ?>
              </div>
              <h3 style="font-size: 1.5rem; margin: 0 0 0.5rem;">
                <?php echo esc_html( $event->title ); ?>
              </h3>
              <p style="color: #6b7280; margin: 0 0 1.5rem;">
                <?php echo esc_html( $event->description ); ?>
              </p>

              <!-- Features List -->
              <?php
              $items = $event->items ? json_decode( $event->items, true ) : array();
              if ( ! empty( $items ) ) :
              ?>
                <ul style="list-style: none; padding: 0; margin: 0;">
                  <?php foreach ( $items as $item ) : ?>
                    <li style="padding: 0.5rem 0; color: #374151;">
                      ✓ <?php echo esc_html( $item ); ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>

            <!-- Booking Form -->
            <div>
              <?php
              get_template_part( 'components/event-booking-form', null, [
                'event_id'       => $event->id,
                'show_label'     => 'Book ' . $event->title,
                'button_text'    => 'Get Quote',
                'success_message' => 'Thanks! We\'ll review your request and respond within 24 hours.',
              ] );
              ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Contact Section -->
  <section style="padding: 4rem 2rem; background: #f3f4f6;">
    <div class="container" style="text-align: center;">
      <h2>Need Help Choosing?</h2>
      <p>Call us at +44 123 456 7890 or email hello@advaithhomes.com</p>
    </div>
  </section>

</main>

<?php get_footer(); ?>
```

---

## Example 2: Individual Event Booking Page

**File:** `page-wedding-hire.php` (custom page template)

```php
<?php
/**
 * Template Name: Wedding Hire Package
 */
get_header();

// Hard-code event ID 1 for wedding package
$event_id = 1;
?>

<main class="ch-main">

  <section class="ch-page-hero" style="background: linear-gradient(135deg, #c084fc 0%, #7c3aed 100%);">
    <div class="container">
      <h1 style="color: #fff;">Wedding Package</h1>
      <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem;">
        Live sugarcane pressing for your Desi wedding or celebration
      </p>
    </div>
  </section>

  <section style="padding: 4rem 2rem;">
    <div class="container">
      <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 3rem; align-items: start;">

        <!-- Left: Details -->
        <div>
          <h2>Why Choose Us for Your Wedding?</h2>
          <ul style="font-size: 1rem; line-height: 1.8; color: #374151;">
            <li>✓ 100% fresh, live-pressed sugarcane juice</li>
            <li>✓ Served cold immediately after pressing</li>
            <li>✓ Available for up to 500+ guests</li>
            <li>✓ Custom flavour blends (ginger, mint, lemon, etc.)</li>
            <li>✓ Professional setup & breakdown included</li>
            <li>✓ Served by our trained team</li>
            <li>✓ Perfect for Mehendi, Walima, Eid gatherings</li>
          </ul>

          <h3 style="margin-top: 2rem;">What's Included?</h3>
          <ul style="font-size: 0.95rem; color: #6b7280;">
            <li>💚 Fresh sugarcane sourced daily</li>
            <li>⚙️ Professional juice extraction machine</li>
            <li>🥤 Eco-friendly cups & straws</li>
            <li>👨‍💼 Service team member for full event</li>
            <li>🎨 Custom signage with your event theme</li>
            <li>❄️ Ice & refrigeration included</li>
          </ul>
        </div>

        <!-- Right: Booking Form -->
        <div style="background: #f9fafb; padding: 2rem; border-radius: 12px; border: 2px solid #c084fc;">
          <?php
          get_template_part( 'components/event-booking-form', null, [
            'event_id'        => $event_id,
            'show_label'      => 'Get Your Wedding Quote',
            'button_text'     => 'Request Booking',
            'success_message' => 'Perfect! We\'ve received your request. Our team will call you within 2 hours with pricing and availability.',
          ] );
          ?>
        </div>

      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section style="padding: 4rem 2rem; background: #f3f4f6;">
    <div class="container" style="max-width: 800px;">
      <h2 style="text-align: center; margin-bottom: 2rem;">Common Questions</h2>

      <div style="border-bottom: 1px solid #e5e7eb; padding: 1.5rem 0;">
        <h4>How much per guest?</h4>
        <p>Pricing depends on guest count and date. We'll send a custom quote after you submit the form.</p>
      </div>

      <div style="border-bottom: 1px solid #e5e7eb; padding: 1.5rem 0;">
        <h4>Can you handle outdoor venues?</h4>
        <p>Yes! We operate in all weather conditions. We bring our own generator and gazebo if needed.</p>
      </div>

      <div style="border-bottom: 1px solid #e5e7eb; padding: 1.5rem 0;">
        <h4>How long do you stay?</h4>
        <p>Typically 2-4 hours depending on guest count and your preferences. We'll discuss timing in your quote.</p>
      </div>

      <div style="padding: 1.5rem 0;">
        <h4>Do you offer alcohol-free drinks too?</h4>
        <p>Yes, sugarcane juice is naturally alcohol-free! We also offer custom blends with fresh botanicals.</p>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
```

---

## Example 3: Multi-Event Hire Comparison Page

**File:** `page-hire-comparison.php`

```php
<?php
/**
 * Template Name: Hire Package Comparison
 */
get_header();

$events_model = new AH_Events_Model();
$events = $events_model->get_active( 3 ); // Get first 3 active events
?>

<main class="ch-main">

  <section class="ch-page-hero">
    <div class="container">
      <h1>Choose Your Perfect Package</h1>
      <p>Compare our event packages side-by-side</p>
    </div>
  </section>

  <section style="padding: 4rem 2rem;">
    <div class="container">

      <!-- Comparison Table -->
      <div style="overflow-x: auto; margin-bottom: 4rem;">
        <table style="
          width: 100%;
          border-collapse: collapse;
          background: #fff;
          border: 1px solid #e5e7eb;
        ">
          <thead>
            <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
              <th style="padding: 1rem; text-align: left; font-weight: 600;">Feature</th>
              <?php foreach ( $events as $ev ) : ?>
                <th style="padding: 1rem; text-align: center;">
                  <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">
                    <?php echo esc_html( $ev->icon ); ?>
                  </div>
                  <?php echo esc_html( $ev->title ); ?>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <tr style="border-bottom: 1px solid #e5e7eb;">
              <td style="padding: 1rem; font-weight: 600;">Guest Count</td>
              <td style="padding: 1rem; text-align: center;">50–200</td>
              <td style="padding: 1rem; text-align: center;">200–500</td>
              <td style="padding: 1rem; text-align: center;">500+</td>
            </tr>
            <tr style="border-bottom: 1px solid #e5e7eb;">
              <td style="padding: 1rem; font-weight: 600;">Live Pressing</td>
              <td style="padding: 1rem; text-align: center;">✓</td>
              <td style="padding: 1rem; text-align: center;">✓</td>
              <td style="padding: 1rem; text-align: center;">✓</td>
            </tr>
            <tr style="border-bottom: 1px solid #e5e7eb;">
              <td style="padding: 1rem; font-weight: 600;">Flavour Options</td>
              <td style="padding: 1rem; text-align: center;">Up to 3</td>
              <td style="padding: 1rem; text-align: center;">Up to 6</td>
              <td style="padding: 1rem; text-align: center;">Unlimited</td>
            </tr>
            <tr>
              <td style="padding: 1rem; font-weight: 600;">Setup & Team</td>
              <td style="padding: 1rem; text-align: center;">1 person</td>
              <td style="padding: 1rem; text-align: center;">2 people</td>
              <td style="padding: 1rem; text-align: center;">2+ people</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Booking Forms Section -->
      <h2 style="text-align: center; margin-bottom: 3rem;">Select Your Package & Request a Quote</h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
        <?php foreach ( $events as $event ) : ?>
          <div style="
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 2rem;
            background: #fafafa;
            transition: all 0.3s ease;
          ">
            <h3 style="margin: 0 0 1rem; font-size: 1.3rem;">
              <?php echo esc_html( $event->title ); ?>
            </h3>
            <p style="color: #6b7280; margin: 0 0 1.5rem;">
              <?php echo esc_html( $event->description ); ?>
            </p>

            <?php
            get_template_part( 'components/event-booking-form', null, [
              'event_id'    => $event->id,
              'show_label'  => 'Book ' . $event->title,
              'button_text' => 'Request Quote',
            ] );
            ?>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

</main>

<?php get_footer(); ?>
```

---

## Setup Instructions for These Examples

### Step 1: Create Pages

1. Go **WordPress Admin** → **Pages** → **Add New**
2. Choose one of the templates above:
   - Template: `Hire Package Comparison` (for example 3)
   - Template: `Wedding Hire Package` (for example 2)
   - Or edit your existing **Events page** with example 1 code
3. Publish

### Step 2: Configure Events

1. Go **Events & Hire Packages**
2. Edit each event and configure:
   - ✅ Enable notifications (checkbox)
   - Set trigger name: `booking_wedding`, `booking_corporate`, etc.
3. Click **Update**

### Step 3: Create Rules

1. Go **Triggers Maker** → **Rules** tab
2. Create rule: "Wedding Booking Notify Team"
   - Trigger: `booking_wedding`
   - Action: Email
   - To: `{config_admin_email}`
   - Subject: `New Wedding Booking: {num_guests} guests from {client_name}`
   - Body: Use template with `{tokens}`
3. Create rule: "Booking Auto-Reply to Client"
   - Trigger: `booking_wedding`
   - Action: Email
   - To: `{email}`
   - Subject: `Thanks {client_name}! We got your booking request`

### Step 4: Test

1. Visit your page with the booking form
2. Fill form and submit
3. Check **Triggers Maker** → **Trigger Logs**
   - Should see "✅ Sent" emails

---

## Styling with Tailwind / Custom CSS

If you want custom styling, add CSS to `assets/css/components.css`:

```css
.ch-event-booking-form {
  /* Your custom styles */
}

.ch-event-booking-form input,
.ch-event-booking-form textarea {
  /* Input styles */
}

.ch-booking-success {
  /* Success message styles */
}
```

The component uses inline styles for portability, but you can override with CSS.

---

## Next Steps

- Read **RULES_ENGINE_INTEGRATION.md** for complete token reference
- Go to **Triggers Maker Config** and set up global email defaults
- Test by submitting a booking and checking **Trigger Logs**
