(function () {
    var supportsObserver = 'IntersectionObserver' in window;

    var observer = supportsObserver
        ? new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fp-in-view');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -40px 0px',
        })
        : null;

    function observe(items) {
        if (!items || !items.length) {
            return;
        }

        if (!supportsObserver) {
            Array.prototype.forEach.call(items, function (el) {
                el.classList.add('fp-in-view');
            });
            return;
        }

        Array.prototype.forEach.call(items, function (el) {
            observer.observe(el);
        });
    }

    // Exposed so newly-inserted content (e.g. "Load More" cards) can be
    // handed to the same observer instance without re-scanning the page.
    window.fpObserveAnimations = observe;

    observe(document.querySelectorAll('.fp-animate'));
})();
