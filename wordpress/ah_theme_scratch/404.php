<?php get_header(); ?>

<main class="main-content" style="background: #f8fafc; padding-top: 160px; padding-bottom: 100px;">
    <div class="container" style="max-width: 1000px;">
        
        <!-- Premium 404 Header -->
        <div style="text-align: center; margin-bottom: 80px;">
            <div style="font-size: 120px; font-weight: 900; line-height: 1; color: #f1f5f9; position: absolute; left: 50%; transform: translateX(-50%); z-index: 0; margin-top: -40px;">404</div>
            <div style="position: relative; z-index: 1;">
                <span style="background: #fee2e2; color: #ef4444; padding: 6px 15px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Lost at Sea?</span>
                <h1 style="font-family: var(--font-display); font-size: clamp(2.5rem, 5vw, 4rem); color: #0f172a; margin-top: 20px;">We can't find that page.</h1>
                <p style="color: #64748b; font-size: 1.2rem; max-width: 600px; margin: 25px auto 0; line-height: 1.6;">
                    The link might be broken or the page has moved. Let's get you back to the right property intelligence.
                </p>
                <div style="margin-top: 40px; display: flex; justify-content: center; gap: 15px;">
                    <a href="<?php echo home_url('/'); ?>" class="button button--primary" style="padding: 15px 35px;">Back to Home</a>
                    <a href="<?php echo home_url('/contact'); ?>" class="button button--outline" style="padding: 15px 35px; border: 1px solid #e2e8f0; color: #1e293b;">Contact Support</a>
                </div>
            </div>
        </div>

        <!-- Helpful Recovery Sections -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 60px;">
            
            <!-- Recovery Section 1: Market News -->
            <div style="background: white; padding: 40px; border-radius: 24px; box-shadow: var(--shadow-md); border: 1px solid #f1f5f9;">
                <div style="font-size: 32px; margin-bottom: 20px;">🚨</div>
                <h3 style="font-family: var(--font-display); font-size: 1.6rem; margin-bottom: 10px;">Market News</h3>
                <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 20px;">Stay updated with the latest UK property trends and law changes.</p>
                <a href="<?php echo home_url('/news'); ?>" style="color: #ef4444; font-weight: 700; text-decoration: none; font-size: 0.9rem;">View News →</a>
            </div>

            <!-- Recovery Section 2: Buying Advice -->
            <div style="background: white; padding: 40px; border-radius: 24px; box-shadow: var(--shadow-md); border: 1px solid #f1f5f9;">
                <div style="font-size: 32px; margin-bottom: 20px;">📖</div>
                <h3 style="font-family: var(--font-display); font-size: 1.6rem; margin-bottom: 10px;">Buying Guides</h3>
                <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 20px;">Expert advice and step-by-step guides for home buyers.</p>
                <a href="<?php echo home_url('/buying'); ?>" style="color: #ef4444; font-weight: 700; text-decoration: none; font-size: 0.9rem;">Start Learning →</a>
            </div>

            <!-- Recovery Section 3: Blog -->
            <div style="background: white; padding: 40px; border-radius: 24px; box-shadow: var(--shadow-md); border: 1px solid #f1f5f9;">
                <div style="font-size: 32px; margin-bottom: 20px;">💡</div>
                <h3 style="font-family: var(--font-display); font-size: 1.6rem; margin-bottom: 10px;">Expert Insights</h3>
                <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 20px;">Deep dives into negotiation, finance, and property hotspots.</p>
                <a href="<?php echo home_url('/blog'); ?>" style="color: #ef4444; font-weight: 700; text-decoration: none; font-size: 0.9rem;">Read Blog →</a>
            </div>

        </div>

        <!-- Market Ticker Bottom -->
        <div style="margin-top: 80px; text-align: center; border-top: 1px solid #f1f5f9; padding-top: 60px;">
            <p style="font-weight: 700; color: #0f172a; margin-bottom: 20px;">Looking for something specific?</p>
            <form role="search" method="get" action="<?php echo home_url('/'); ?>" style="max-width: 500px; margin: 0 auto; display: flex; gap: 10px;">
                <input type="search" name="s" placeholder="Search insights, guides, or properties..." style="flex-grow: 1; padding: 15px 20px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;">
                <button type="submit" class="button button--primary" style="padding: 15px 30px;">Search</button>
            </form>
        </div>

    </div>
</main>

<?php get_footer(); ?>