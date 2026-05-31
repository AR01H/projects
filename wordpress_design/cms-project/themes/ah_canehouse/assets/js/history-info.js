(function () {
    'use strict';

    if ( typeof TOTAL_HISTORY_INFO === 'undefined' || TOTAL_HISTORY_INFO <= 0 ) return; 
    let   current = 0;
    let   busy    = false;

    const pages   = Array.from( document.querySelectorAll('.ch-book-page') );
    const dots    = Array.from( document.querySelectorAll('.ch-hdot') );
    const ticks   = Array.from( document.querySelectorAll('.ch-hist-tick') );
    const pfill   = document.getElementById('ch-hist-pfill');
    const leaf    = document.getElementById('ch-turn-leaf');
    const prevBtn = document.getElementById('ch-hist-prev');
    const nextBtn = document.getElementById('ch-hist-next');
    const isMobile = () => window.innerWidth <= 640;

    /* ── Progress / dots / ticks ──────────────────────────────────── */
    function updateProgress( idx ) {
        const pct = Math.round( (idx + 1) / TOTAL_HISTORY_INFO * 100 );
        if ( pfill ) pfill.style.width = pct + '%';
        dots.forEach( (d, i) => {
            d.classList.toggle( 'active', i === idx );
            d.setAttribute( 'aria-selected', i === idx ? 'true' : 'false' );
        });
        ticks.forEach( (t, i) => {
            t.dataset.active = i <= idx ? 'true' : 'false';
        });
    }

    /* ── Reset all pages to clean state ──────────────────────────── */
    function resetAll( activeIdx ) {
        pages.forEach( (p, i) => {
            p.classList.remove('is-active','is-exiting','is-entering','is-rewind-fly');
            p.style.zIndex     = TOTAL_HISTORY_INFO - i;
            p.style.transform  = '';
            p.style.opacity    = '';
            p.style.transition = '';
            p.setAttribute('aria-hidden', i === activeIdx ? 'false' : 'true');
            p.querySelectorAll('.ch-page-turn-trigger').forEach( b =>
                b.setAttribute('tabindex', i === activeIdx ? '0' : '-1')
            );
        });
        pages[ activeIdx ].classList.add('is-active');
    }

    /* ── Peel leaf helper ─────────────────────────────────────────── */
    function fireLeaf( onDone ) {
        if ( isMobile() || !leaf ) { onDone && onDone(); return; }
        leaf.classList.add('is-turning');
        leaf.addEventListener('animationend', function onEnd() {
            leaf.classList.remove('is-turning');
            leaf.removeEventListener('animationend', onEnd);
            onDone && onDone();
        }, { once: true });
    }

    /* ── REWIND: cascade all pages back to idx=0 ─────────────────── */
    function rewindToStart( afterDone ) {
        /*
         * Visually: pages from current down to 1 fly off to the RIGHT
         * in quick succession (staggered), revealing page 0 underneath.
         * Each page fans out with a slight rotation then vanishes.
         */
        const stagger = 90;   /* ms between each page flip */
        const dur     = 320;  /* ms each page takes */
        let   count   = current; /* number of pages to flip back */

        if ( count === 0 ) { afterDone && afterDone(); return; }

        /* Lift z-indices so they stack correctly during cascade */
        for ( let i = current; i >= 1; i-- ) {
            pages[i].style.zIndex = TOTAL_HISTORY_INFO + (current - i) + 2;
        }
        /* Make sure page 0 peeks behind */
        pages[0].style.zIndex    = 1;
        pages[0].style.opacity   = '1';
        pages[0].style.transform = 'translateX(0) scale(1)';

        let completed = 0;

        for ( let i = current; i >= 1; i-- ) {
            const p     = pages[i];
            const delay = (current - i) * stagger;

            setTimeout( () => {
                /* Snap to visible first */
                p.style.transition = 'none';
                p.style.opacity    = '1';
                p.style.transform  = 'translateX(0) rotateY(0deg)';
                void p.offsetWidth; /* reflow */

                /* Then animate off to the right */
                p.style.transition = `opacity ${dur}ms cubic-bezier(.4,0,.2,1), transform ${dur}ms cubic-bezier(.4,0,.2,1)`;
                p.style.transform  = `translateX(55px) rotateY(18deg) scale(0.94)`;
                p.style.opacity    = '0';

                setTimeout( () => {
                    completed++;
                    p.classList.remove('is-active');
                    /* When last page has flown off, settle on page 0 */
                    if ( completed === count ) {
                        resetAll(0);
                        current = 0;
                        updateProgress(0);
                        busy = false;
                        afterDone && afterDone();
                    }
                }, dur + 20 );

            }, delay );
        }
    }

    /* ── WIND-FORWARD: cascade all pages from 0 to TOTAL_HISTORY_INFO-1 ──────── */
    function windToEnd( afterDone ) {
        /*
         * Pages 0 → TOTAL_HISTORY_INFO-2 flip to the LEFT in cascade,
         * revealing the last page underneath.
         */
        const stagger = 90;
        const dur     = 300;
        const count   = TOTAL_HISTORY_INFO - 1 - current;

        if ( count === 0 ) { afterDone && afterDone(); return; }

        /* Ensure last page is visible underneath */
        pages[ TOTAL_HISTORY_INFO - 1 ].style.zIndex  = 1;
        pages[ TOTAL_HISTORY_INFO - 1 ].style.opacity = '1';
        pages[ TOTAL_HISTORY_INFO - 1 ].style.transform = 'translateX(0) scale(1)';

        /* Stack pages we'll flip */
        for ( let i = current; i <= TOTAL_HISTORY_INFO - 2; i++ ) {
            pages[i].style.zIndex = TOTAL_HISTORY_INFO + (TOTAL_HISTORY_INFO - 2 - i) + 2;
        }

        let completed = 0;

        for ( let i = current; i <= TOTAL_HISTORY_INFO - 2; i++ ) {
            const p     = pages[i];
            const delay = (i - current) * stagger;

            setTimeout( () => {
                p.style.transition = 'none';
                p.style.opacity    = '1';
                p.style.transform  = 'translateX(0) rotateY(0deg)';
                void p.offsetWidth;

                p.style.transition = `opacity ${dur}ms cubic-bezier(.4,0,.2,1), transform ${dur}ms cubic-bezier(.4,0,.2,1)`;
                p.style.transform  = `translateX(-55px) rotateY(-18deg) scale(0.94)`;
                p.style.opacity    = '0';

                setTimeout( () => {
                    completed++;
                    p.classList.remove('is-active');
                    if ( completed === count ) {
                        resetAll( TOTAL_HISTORY_INFO - 1 );
                        current = TOTAL_HISTORY_INFO - 1;
                        updateProgress( TOTAL_HISTORY_INFO - 1 );
                        busy = false;
                        afterDone && afterDone();
                    }
                }, dur + 20 );
            }, delay );
        }
    }

    /* ── Normal single-page turn ─────────────────────────────────── */
    function goTo( rawNext ) {
        if ( busy ) return;

        /* Wrap detection */
        const wrapsToStart = rawNext >= TOTAL_HISTORY_INFO;  /* was on last, clicked Next  */
        const wrapsToEnd   = rawNext < 0;       /* was on first, clicked Prev */

        if ( rawNext === current ) return;

        /* Handle wrap-around with cascade */
        if ( wrapsToStart ) {
            busy = true;
            /* Fire a single peel leaf then cascade rewind */
            if ( !isMobile() && leaf ) {
                leaf.classList.add('is-turning');
                leaf.addEventListener('animationend', function onEnd(){
                    leaf.classList.remove('is-turning');
                    leaf.removeEventListener('animationend', onEnd);
                }, { once: true });
            }
            setTimeout( () => rewindToStart(), isMobile() ? 0 : 180 );
            return;
        }

        if ( wrapsToEnd ) {
            busy = true;
            setTimeout( () => windToEnd(), 20 );
            return;
        }

        /* ── Normal forward / backward ── */
        busy = true;
        const next      = rawNext;
        const isForward = next > current;
        const outPage   = pages[ current ];
        const inPage    = pages[ next ];

        if ( isForward && !isMobile() ) {
            fireLeaf( null );
        }

        outPage.classList.remove('is-active');
        outPage.classList.add('is-exiting');
        outPage.setAttribute('aria-hidden', 'true');
        outPage.querySelectorAll('.ch-page-turn-trigger').forEach( b => b.setAttribute('tabindex', '-1') );

        const delay = isForward && !isMobile() ? 200 : 20;
        setTimeout( () => {
            outPage.classList.remove('is-exiting');

            inPage.classList.add('is-entering');
            inPage.style.zIndex = TOTAL_HISTORY_INFO + 10;
            void inPage.offsetWidth;

            inPage.classList.remove('is-entering');
            inPage.classList.add('is-active');
            inPage.setAttribute('aria-hidden', 'false');
            inPage.querySelectorAll('.ch-page-turn-trigger').forEach( b => b.setAttribute('tabindex', '0') );

            pages.forEach( (p, i) => { p.style.zIndex = TOTAL_HISTORY_INFO - i; });
            inPage.style.zIndex = TOTAL_HISTORY_INFO + 1;

            current = next;
            updateProgress( current );
            setTimeout( () => { busy = false; }, 60 );
        }, delay );
    }

    /* ── Event bindings ──────────────────────────────────────────── */
    dots.forEach( (d, i) => d.addEventListener('click', () => goTo(i)) );

    if ( prevBtn ) prevBtn.addEventListener('click', () => goTo( current - 1 ));
    if ( nextBtn ) nextBtn.addEventListener('click', () => goTo( current + 1 ));

    pages.forEach( (p, i) => {
        const trigger = p.querySelector('.ch-page-turn-next');
        if ( trigger ) trigger.addEventListener('click', () => goTo( i + 1 ));
    });

    document.addEventListener('keydown', e => {
        if ( e.key === 'ArrowRight' || e.key === 'ArrowDown' ) goTo( current + 1 );
        if ( e.key === 'ArrowLeft'  || e.key === 'ArrowUp'   ) goTo( current - 1 );
    });

    let touchStartX = 0;
    const book = document.getElementById('ch-book');
    if ( book ) {
        book.addEventListener('touchstart', e => {
            touchStartX = e.touches[0].clientX;
        }, { passive: true });
        book.addEventListener('touchend', e => {
            const dx = e.changedTouches[0].clientX - touchStartX;
            if ( Math.abs(dx) > 45 ) goTo( dx < 0 ? current + 1 : current - 1 );
        }, { passive: true });
    }

    /* ── Init ────────────────────────────────────────────────────── */
    updateProgress(0);

})();