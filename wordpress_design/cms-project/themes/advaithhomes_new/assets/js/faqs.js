(function(){
    function animateDetails(details){
        var content = details.querySelector('.faq-a-body');
        if(!content) return;
        if(details.open){
            content.style.height = content.scrollHeight + 'px';
            content.addEventListener('transitionend', function te(){
                if(details.open){ content.style.height = 'auto'; }
                content.removeEventListener('transitionend', te);
            });
        } else {
            var cur = content.scrollHeight;
            content.style.height = cur + 'px';
            content.offsetHeight; // force reflow
            content.style.height = '0px';
        }
    }

    document.addEventListener('DOMContentLoaded', function(){
        // Page FAQs: animated collapse/expand + exclusive behaviour
        var faqs = Array.prototype.slice.call(document.querySelectorAll('.faq-item'));
        faqs.forEach(function(details){
            var content = details.querySelector('.faq-a-body');
            if(content){
                if(!details.open){ content.style.height = '0px'; }
                else { content.style.height = 'auto'; }
            }
            details.addEventListener('toggle', function(){
                if ( details.open ) {
                    faqs.forEach(function(other){ if ( other !== details && other.open ) other.open = false; });
                }
                animateDetails(details);
            });
        });

        // Sidebar FAQs: exclusive only (no animation required here)
        var sidebar = Array.prototype.slice.call(document.querySelectorAll('.contact-faq-item'));
        sidebar.forEach(function(d){
            d.addEventListener('toggle', function(){
                if (!d.open) return;
                sidebar.forEach(function(other){ if ( other !== d && other.open ) other.open = false; });
            });
        });
    });
})();
