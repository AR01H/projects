<?php
/**
 * Template Name: Previous Clients
 */
get_header(); ?>

  <div id="nav-placeholder"></div>

  <section class="clients-hero">
    <div class="container">
      <div class="eyebrow reveal" style="justify-content:center">Success Stories</div>
      <h1 class="reveal reveal-delay-1">Our Featured Client Outcomes</h1>
      <p class="reveal reveal-delay-2">Discover how we help UK buyers navigate complex markets, secure off-market gems,
        and save thousands on their dream homes.</p>
    </div>
  </section>

  <section class="section interactive-stories" style="background:var(--bg)">
    <div class="container">
      <div style="text-align:center;max-width:640px;margin:0 auto 60px">
        <div class="eyebrow reveal" style="justify-content:center">Interactive Journeys</div>
        <h2 class="reveal reveal-delay-1">Explore Our Success Stories</h2>
      </div>

      <div class="split-review-grid interactive-grid">
        <div class="split-review__visual reveal">
          <div class="coverflow-container" style="height:450px; width:100%; margin:0">
            <div class="coverflow-item active" data-story="1">
              <div class="coverflow-item__avatar"><img
                  src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop"
                  alt="Sarah & Raj"></div>
              <blockquote class="coverflow-item__quote">"Saved us Â£27,500 and six months of stress."</blockquote>
              <div class="coverflow-item__author">
                <span class="coverflow-item__name">Sarah & Raj Mehta</span>
              </div>
            </div>
            <div class="coverflow-item next" data-story="2">
              <div class="coverflow-item__avatar"><img
                  src="https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200&h=200&fit=crop"
                  alt="Emma & Tom"></div>
              <blockquote class="coverflow-item__quote">"The negotiation was flawless. Saved Â£40,000."</blockquote>
              <div class="coverflow-item__author">
                <span class="coverflow-item__name">Emma & Tom Wright</span>
              </div>
            </div>
            <div class="coverflow-item prev" data-story="3">
              <div class="coverflow-item__avatar"><img
                  src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&h=200&fit=crop"
                  alt="James Wilson"></div>
              <blockquote class="coverflow-item__quote">"Sourced three off-market properties for me."</blockquote>
              <div class="coverflow-item__author">
                <span class="coverflow-item__name">James Wilson</span>
              </div>
            </div>
          </div>
        </div>

        <div class="split-review__content reveal reveal-delay-1">
          <div id="story-content-1" class="story-content active">
            <div class="eyebrow">Richmond, London</div>
            <h3>The Mehta Family's First Home</h3>
            <p class="split-review__quote">"We were struggling to get our offers accepted in Richmond. Advaith Homes
              stepped in, analyzed the competition, and helped us secure our dream home Â£27.5k below what we were
              prepared to pay."</p>
            <div class="split-review__details">
              <div class="split-review__author">Sarah & Raj Mehta</div>
              <div class="split-review__loc">Negotiation Win: Â£27,500 Saved</div>
            </div>
          </div>
          <div id="story-content-2" class="story-content">
            <div class="eyebrow">Guildford, Surrey</div>
            <h3>Emma & Tom's Surrey Retreat</h3>
            <p class="split-review__quote">"Moving out of London was daunting. They didn't just find the house; they
              managed the whole chain and saved us Â£40k on the final price."</p>
            <div class="split-review__details">
              <div class="split-review__author">Emma & Tom Wright</div>
              <div class="split-review__loc">Negotiation Win: Â£40,000 Saved</div>
            </div>
          </div>
          <div id="story-content-3" class="story-content">
            <div class="eyebrow">Manchester</div>
            <h3>James' Investment Portfolio</h3>
            <p class="split-review__quote">"As a professional investor, I value speed and off-market access. Advaith
              delivered three high-yield units before they even hit Rightmove."</p>
            <div class="split-review__details">
              <div class="split-review__author">James Wilson</div>
              <div class="split-review__loc">Portfolio Growth: 3 Units Sourced</div>
            </div>
          </div>

          <div class="coverflow-controls" style="margin-top:40px; justify-content: flex-start;">
            <button class="coverflow-btn prev"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
              </svg></button>
            <button class="coverflow-btn next"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg></button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section" style="background:var(--bg-alt)">
    <div class="container">
      <div style="text-align:center;max-width:640px;margin:0 auto">
        <div class="eyebrow reveal" style="justify-content:center">Detailed Stories</div>
        <h2 class="reveal reveal-delay-1">Tap to Reveal the Journey</h2>
        <p class="reveal reveal-delay-2">Click on any card to see the full outcome and specific savings we achieved for
          these clients.</p>
      </div>

      <div class="flip-grid">
        <!-- Flip Card 1 -->
        <div class="flip-card reveal" onclick="toggleFlip(this)">
          <div class="flip-card-inner">
            <div class="flip-card-front">
              <img src="https://images.unsplash.com/photo-1556911220-e15b29be8c8f?w=600&h=400&fit=crop" alt="London"
                class="flip-card__img">
              <div class="flip-card__body">
                <h3 class="flip-card__title">The Battersea Loft</h3>
                <p style="color:var(--text-secondary);font-size:0.9rem">A rare loft conversion in a competitive
                  building. Secured against multiple bidders.</p>
                <div class="flip-card__reveal-btn">Tap to see results â†’</div>
              </div>
            </div>
            <div class="flip-card-back">
              <h4>The Outcome</h4>
              <p>"We managed to get our offer accepted despite being Â£15k lower than the highest bid, thanks to our
                proof-of-funds packaging."</p>
              <div class="stats">
                <div class="flip-stat-item"><span class="flip-stat-label">Saved</span><span
                    class="flip-stat-value">Â£15,000</span></div>
                <div class="flip-stat-item"><span class="flip-stat-label">Competition</span><span
                    class="flip-stat-value">5 Bidders</span></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Flip Card 2 -->
        <div class="flip-card reveal reveal-delay-1" onclick="toggleFlip(this)">
          <div class="flip-card-inner">
            <div class="flip-card-front">
              <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=600&h=400&fit=crop"
                alt="Cotswold" class="flip-card__img">
              <div class="flip-card__body">
                <h3 class="flip-card__title">The Cotswold Retreat</h3>
                <p style="color:var(--text-secondary);font-size:0.9rem">Family relocation from Hong Kong. Full search
                  and school catchment analysis.</p>
                <div class="flip-card__reveal-btn">Tap to see results â†’</div>
              </div>
            </div>
            <div class="flip-card-back">
              <h4>The Outcome</h4>
              <p>"Inspected 12 properties on their behalf and secured a home in the top-rated school catchment area
                before the family arrived in the UK."</p>
              <div class="stats">
                <div class="flip-stat-item"><span class="flip-stat-label">Viewings</span><span
                    class="flip-stat-value">12 Homes</span></div>
                <div class="flip-stat-item"><span class="flip-stat-label">Result</span><span class="flip-stat-value">Top
                    School</span></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Flip Card 3 -->
        <div class="flip-card reveal reveal-delay-2" onclick="toggleFlip(this)">
          <div class="flip-card-inner">
            <div class="flip-card-front">
              <img src="https://images.unsplash.com/photo-1480074568708-e7b720bb3f09?w=600&h=400&fit=crop" alt="Oxford"
                class="flip-card__img">
              <div class="flip-card__body">
                <h3 class="flip-card__title">The Oxford Townhouse</h3>
                <p style="color:var(--text-secondary);font-size:0.9rem">Off-market acquisition in North Oxford. Secured
                  for a professional couple.</p>
                <div class="flip-card__reveal-btn">Tap to see results â†’</div>
              </div>
            </div>
            <div class="flip-card-back">
              <h4>The Outcome</h4>
              <p>"Acquired before it reached the open market through our direct relationship with the seller's estate
                agent."</p>
              <div class="stats">
                <div class="flip-stat-item"><span class="flip-stat-label">Access</span><span
                    class="flip-stat-value">Off-Market</span></div>
                <div class="flip-stat-item"><span class="flip-stat-label">Price</span><span class="flip-stat-value">-5%
                    Under</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div style="text-align:center;max-width:640px;margin:0 auto">
        <div class="eyebrow reveal" style="justify-content:center">Detailed Outcomes</div>
        <h2 class="reveal reveal-delay-1">Case Study Breakdown</h2>
      </div>
      <div class="story-grid">
        <!-- Existing Detailed Story 1 -->
        <div class="story-card reveal">
          <div class="story-card__image"><img
              src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&h=400&fit=crop" alt="Property">
          </div>
          <div class="story-card__content">
            <h3 class="story-card__title">Investor Portfolio Expansion</h3>
            <div class="story-card__location">ðŸ“ Manchester City Centre</div>
            <p class="story-card__quote">"Sourced three high-yield apartments in a single month for a regular client.
              Each property was secured with a discount of at least 8%."</p>
            <div class="story-card__stats">
              <div class="story-stat"><span class="story-stat__label">Net Yield</span><span
                  class="story-stat__value">7.5%</span></div>
              <div class="story-stat"><span class="story-stat__label">Properties</span><span class="story-stat__value">3
                  Units</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section" style="background:var(--bg-alt)">
    <div class="container">
      <div style="text-align:center;max-width:640px;margin:0 auto">
        <div class="eyebrow reveal" style="justify-content:center">Community Feedback</div>
        <h2 class="reveal">What Our Clients Are Saying</h2>
      </div>
      <div class="review-masonry reveal reveal-delay-2">
        <div class="review-bubble">
          <p class="review-bubble__text">"Professional, dedicated and always one step ahead. They found things our own
            surveyor missed."</p>
          <div class="review-bubble__user">
            <div class="review-bubble__avatar">AL</div>
            <div class="review-bubble__info">
              <h5>Alex Lowes</h5>
            </div>
          </div>
        </div>
        <div class="review-bubble">
          <p class="review-bubble__text">"Saved me Â£30k on my first investment property. Worth every single penny of
            their fee."</p>
          <div class="review-bubble__user">
            <div class="review-bubble__avatar">MK</div>
            <div class="review-bubble__info">
              <h5>Mark Knight</h5>
            </div>
          </div>
        </div>
        <div class="review-bubble">
          <p class="review-bubble__text">"Buying from abroad was stressful until we found Advaith. They were our eyes
            and ears on the ground."</p>
          <div class="review-bubble__user">
            <div class="review-bubble__avatar">JS</div>
            <div class="review-bubble__info">
              <h5>Julie Smith</h5>
            </div>
          </div>
        </div>
        <div class="review-bubble">
          <p class="review-bubble__text">"A truly premium service for serious buyers. Highly recommended."</p>
          <div class="review-bubble__user">
            <div class="review-bubble__avatar">PC</div>
            <div class="review-bubble__info">
              <h5>Paul Collins</h5>
            </div>
          </div>
        </div>
        <div class="review-bubble">
          <p class="review-bubble__text">"They treat your money like their own. Fierce negotiators!"</p>
          <div class="review-bubble__user">
            <div class="review-bubble__avatar">TM</div>
            <div class="review-bubble__info">
              <h5>Tina M.</h5>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section" style="background:var(--bg-alt)">
    <div class="container">
      <div style="text-align: center; margin-bottom: 40px;">
        <div class="eyebrow reveal" style="justify-content:center">Portfolio</div>
        <h2 class="reveal">Secured Properties</h2>
      </div>
      <div class="bento-gallery">
        <div class="bento-item bento-wide"><img
            src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80"
            alt="Home"></div>
        <div class="bento-item bento-tall"><img
            src="https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=600&q=80"
            alt="Interior"></div>
        <div class="bento-item"><img
            src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=600&q=80"
            alt="Kitchen"></div>
        <div class="bento-item bento-large"><img
            src="https://images.unsplash.com/photo-1600607686527-6fb886090705?auto=format&fit=crop&w=800&q=80"
            alt="Garden"></div>
        <div class="bento-item"><img
            src="https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=800&q=80"
            alt="Bedroom"></div>
        <div class="bento-item bento-wide"><img
            src="https://images.unsplash.com/photo-1600566752355-35792bedcfea?auto=format&fit=crop&w=800&q=80"
            alt="Luxury"></div>
      </div>
    </div>
  </section>

  <div id="footer-placeholder"></div>
  
  <script>
    // 3D Coverflow Logic
    document.addEventListener('DOMContentLoaded', () => {
      const items = document.querySelectorAll('.coverflow-item');
      const storyContents = document.querySelectorAll('.story-content');
      if (!items.length) return;

      let currentIndex = 0;
      const totalItems = items.length;

      function updateCoverflow() {
        items.forEach((item, index) => {
          item.classList.remove('active', 'prev', 'next', 'prev-hidden', 'next-hidden');
          if (index === currentIndex) {
            item.classList.add('active');
            // Sync Story Content
            const storyId = item.dataset.story;
            storyContents.forEach(content => {
              content.classList.remove('active');
              if (content.id === `story-content-${storyId}`) {
                content.classList.add('active');
              }
            });
          }
          else if (index === (currentIndex - 1 + totalItems) % totalItems) item.classList.add('prev');
          else if (index === (currentIndex + 1) % totalItems) item.classList.add('next');
          else if (index < currentIndex) item.classList.add('prev-hidden');
          else item.classList.add('next-hidden');
        });
      }

      document.querySelector('.coverflow-btn.prev')?.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + totalItems) % totalItems;
        updateCoverflow();
      });
      document.querySelector('.coverflow-btn.next')?.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % totalItems;
        updateCoverflow();
      });
      items.forEach((item, index) => {
        item.addEventListener('click', () => {
          currentIndex = index;
          updateCoverflow();
        });
      });

      updateCoverflow();
    });

    // Flip Card Logic
    function toggleFlip(element) {
      element.classList.toggle('flipped');
    }
  </script>

<?php get_footer(); ?>

