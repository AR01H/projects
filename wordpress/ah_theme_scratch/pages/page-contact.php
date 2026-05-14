<?php
/**
 * Template Name: Contact Page Component
 * Description: The modular component for the Contact page with form handling.
 */

// Handle Form Submission
$submission_msg = '';
$submission_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ah_submit_contact'])) {
    $name = sanitize_text_field($_POST['full_name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $message = sanitize_textarea_field($_POST['message']);

    if (!empty($name) && !empty($email)) {
        $inquiry_id = wp_insert_post([
            'post_title' => $name,
            'post_content' => $message,
            'post_status' => 'publish',
            'post_type' => 'inquiry'
        ]);

        if ($inquiry_id && !is_wp_error($inquiry_id)) {
            update_post_meta($inquiry_id, 'email', $email);
            update_post_meta($inquiry_id, 'phone', $phone);
            update_post_meta($inquiry_id, 'status', 'New');
            
            $submission_success = true;
            $submission_msg = "Thank you! We have received your inquiry and will be in touch shortly.";
        }
    } else {
        $submission_msg = "Please fill in all required fields.";
    }
}

$page_eyebrow = "Contact Us";
$page_title = "Let's Find Your Dream Home";
$page_desc = "Have questions or ready to start your property search? Our team is here to help you navigate the UK market.";

$contact_email = get_option('ah_contact_email', 'contact@advaithhomes.co.uk');
$contact_phone = get_option('ah_contact_phone', '+44 774 722 3762');
$contact_address = get_option('ah_contact_address', 'London, United Kingdom');
?>

<div class="container">
    <div style="max-width: 800px; margin: 0 auto 60px; text-align: center;">
        <div class="eyebrow reveal" style="color:var(--gold-600)"><?php echo esc_html($page_eyebrow); ?></div>
        <h1 class="reveal reveal-delay-1" style="margin-bottom: 20px; font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; letter-spacing: -0.02em;">
            <?php echo esc_html($page_title); ?>
        </h1>
        <p class="reveal reveal-delay-2" style="font-size: 1.1rem; line-height: 1.8; color: var(--slate-700);">
            <?php echo esc_html($page_desc); ?>
        </p>
    </div>

    <div class="contact-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 60px; margin-bottom: 80px;">
        <!-- Contact Info -->
        <div class="reveal">
            <h3 style="margin-bottom: 30px; font-size: 1.5rem;">Get In Touch</h3>
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="width: 40px; height: 40px; background: var(--client-color-50); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--client-color-700); flex-shrink: 0;">📬</div>
                    <div>
                        <div style="font-weight: 700; color: var(--slate-900);">Email</div>
                        <a href="mailto:<?php echo esc_attr($contact_email); ?>" style="color: var(--slate-600); text-decoration: none;"><?php echo esc_html($contact_email); ?></a>
                    </div>
                </div>
                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="width: 40px; height: 40px; background: var(--client-color-50); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--client-color-700); flex-shrink: 0;">📞</div>
                    <div>
                        <div style="font-weight: 700; color: var(--slate-900);">Phone</div>
                        <a href="tel:<?php echo esc_attr(str_replace(' ', '', $contact_phone)); ?>" style="color: var(--slate-600); text-decoration: none;"><?php echo esc_html($contact_phone); ?></a>
                    </div>
                </div>
                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="width: 40px; height: 40px; background: var(--client-color-50); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--client-color-700); flex-shrink: 0;">📍</div>
                    <div>
                        <div style="font-weight: 700; color: var(--slate-900);">Office</div>
                        <div style="color: var(--slate-600);"><?php echo esc_html($contact_address); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="reveal reveal-delay-1" style="background: white; padding: 40px; border-radius: 20px; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
            <?php if ($submission_msg) : ?>
                <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; <?php echo $submission_success ? 'background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;' : 'background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;'; ?>">
                    <?php echo esc_html($submission_msg); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 8px;">Full Name</label>
                    <input type="text" name="full_name" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 8px;">Email Address</label>
                        <input type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 8px;">Phone Number</label>
                        <input type="text" name="phone" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit;">
                    </div>
                </div>
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 8px;">Your Message</label>
                    <textarea name="message" rows="4" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; resize: vertical;"></textarea>
                </div>
                <button type="submit" name="ah_submit_contact" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center;">Send Inquiry →</button>
            </form>
        </div>
    </div>
</div>

<!-- Reusing the CTA Component -->
<?php get_template_part('pages/components/home/cta'); ?>
