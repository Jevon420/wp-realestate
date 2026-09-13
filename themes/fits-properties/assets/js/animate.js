(function () {
    var items = document.querySelectorAll('.fp-animate');

    if (!items.length) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        items.forEach(function (el) {
            el.classList.add('fp-in-view');
        });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('fp-in-view');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15,
        rootMargin: '0px 0px -40px 0px',
    });

    items.forEach(function (el) {
        observer.observe(el);
    });
})();
