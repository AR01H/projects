<?php
/**
 * Template Name: Contact
 */
get_header(); ?>

    <div id="nav-placeholder"></div>

    <section class="page-hero">
        <div class="container">
            <div class="eyebrow reveal" style="justify-content:center">Get In Touch</div>
            <h1 class="reveal reveal-delay-1">We'd Love to Hear From You</h1>
            <p class="reveal reveal-delay-2">Whether you have a question, want to book a consultation, or just want to
                understand how we can help â€” reach out. We respond within 2 hours during business hours.</p>
        </div>
    </section>

    <section class="section" style="padding-top:0">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-info__item reveal">
                        <div class="contact-info__icon">ðŸ“ž</div>
                        <div>
                            <div class="contact-info__label">Phone</div>
                            <div class="contact-info__value">+44 774 722 3762</div>
                            <p style="font-size:.85rem;margin-top:4px">Mondayâ€“Saturday, 9amâ€“6pm</p>
                        </div>
                    </div>
                    <div class="contact-info__item reveal reveal-delay-1">
                        <div class="contact-info__icon">âœ‰ï¸</div>
                        <div>
                            <div class="contact-info__label">Email</div>
                            <div class="contact-info__value">contact@advaithhomes.co.uk</div>
                            <p style="font-size:.85rem;margin-top:4px">We reply within 2 hours</p>
                        </div>
                    </div>
                    <div class="contact-info__item reveal reveal-delay-2">
                        <div class="contact-info__icon">ðŸ“</div>
                        <div>
                            <div class="contact-info__label">Location</div>
                            <div class="contact-info__value">London & Nationwide</div>
                            <p style="font-size:.85rem;margin-top:4px">Covering all of England & Wales</p>
                        </div>
                    </div>
                    <div class="contact-info__item reveal reveal-delay-3">
                        <div class="contact-info__icon">â°</div>
                        <div>
                            <div class="contact-info__label">Hours</div>
                            <div class="contact-info__value">Monâ€“Fri: 9amâ€“6pm</div>
                            <p style="font-size:.85rem;margin-top:4px">Saturday: 10amâ€“4pm Â· Sunday: Closed</p>
                        </div>
                    </div>

                    <div class="card reveal"
                        style="margin-top:8px;background:var(--client-color-50);border-color:var(--client-color-200)">
                        <div style="font-size:1.5rem;margin-bottom:12px">â˜Žï¸</div>
                        <h4 style="margin-bottom:8px">Book a Free Consultation</h4>
                        <p style="font-size:.875rem;margin-bottom:16px">30 minutes with one of our expert buyer's agents
                            â€” no charge, no obligation.</p>
                        <a href="<?php echo home_url("/free-consultation"); ?>" class="btn btn-primary"
                            style="width:100%;justify-content:center">Book Now â†’</a>
                    </div>
                </div>

                <div class="card reveal reveal-delay-1" style="padding:40px">
                    <h3 style="margin-bottom:8px">Send Us a Message</h3>
                    <p style="font-size:.875rem;margin-bottom:28px">Fill in the form below and we'll get back to you
                        within 2 hours.</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">First Name *</label>
                            <input type="text" class="form-input" placeholder="John" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name *</label>
                            <input type="text" class="form-input" placeholder="Smith" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" class="form-input" placeholder="john@example.com" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-input" placeholder="+44 7700 000000" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">How can we help? *</label>
                        <select class="form-select">
                            <option value="">Select an option...</option>
                            <option>I want to book a free consultation</option>
                            <option>I've found a property and need negotiation help</option>
                            <option>I want full search & acquisition support</option>
                            <option>I need a property research report</option>
                            <option>I have a general question</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Property budget (approx.)</label>
                        <select class="form-select">
                            <option value="">Select range...</option>
                            <option>Under Â£200,000</option>
                            <option>Â£200,000 â€“ Â£350,000</option>
                            <option>Â£350,000 â€“ Â£500,000</option>
                            <option>Â£500,000 â€“ Â£750,000</option>
                            <option>Â£750,000 â€“ Â£1,000,000</option>
                            <option>Over Â£1,000,000</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea class="form-textarea"
                            placeholder="Tell us about your situation, what you're looking for, or any questions you have..."></textarea>
                    </div>
                    <button class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px"
                        onclick="handleSubmit(this)">Send Message â†’</button>
                    <p style="font-size:.75rem;color:var(--text-muted);margin-top:12px;text-align:center">By submitting
                        you agree to our <a href="pages/privacy-policy.html" style="color:var(--accent)">Privacy
                            Policy</a>. We never share your data.</p>
                </div>
            </div>
        </div>
    </section>

    <div id="footer-placeholder"></div>
    
    <script>
        function handleSubmit(btn) {
            btn.textContent = 'âœ“ Message Sent!';
            btn.style.background = '#16a34a';
            btn.disabled = true;
        }
    </script>

<?php get_footer(); ?>

