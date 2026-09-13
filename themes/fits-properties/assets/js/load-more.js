(function () {
    var button = document.getElementById('fp-load-more-btn');

    if (!button || typeof fpLoadMore === 'undefined') {
        return;
    }

    var grid = document.getElementById(button.dataset.target);
    var page = 1;
    var maxPages = parseInt(button.dataset.maxPages, 10) || 1;

    function buildSkeletonCards(count) {
        var wrap = document.createElement('div');
        var card = '<div class="fp-card fp-skeleton-card">' +
            '<div class="fp-skeleton-card__media"></div>' +
            '<div class="fp-skeleton-card__body">' +
            '<div class="fp-skeleton-line fp-skeleton-line--short"></div>' +
            '<div class="fp-skeleton-line fp-skeleton-line--title"></div>' +
            '<div class="fp-skeleton-line"></div>' +
            '</div></div>';
        wrap.innerHTML = card.repeat(count);
        return Array.prototype.slice.call(wrap.children);
    }

    button.addEventListener('click', function () {
        if (!grid) {
            return;
        }

        button.disabled = true;
        button.textContent = 'Loading…';

        var skeletons = buildSkeletonCards(3);
        skeletons.forEach(function (el) {
            grid.appendChild(el);
        });

        var params = new URLSearchParams(window.location.search);
        params.delete('paged');
        params.set('fp_page', page + 1);
        params.set('action', 'fp_load_more_properties');
        params.set('nonce', fpLoadMore.nonce);

        if (button.dataset.taxonomy) {
            params.set('fp_taxonomy', button.dataset.taxonomy);
        }
        if (button.dataset.term) {
            params.set('fp_term', button.dataset.term);
        }

        fetch(fpLoadMore.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString(),
        })
            .then(function (response) { return response.json(); })
            .then(function (result) {
                skeletons.forEach(function (el) { el.remove(); });

                if (!result.success) {
                    button.textContent = 'Something went wrong';
                    return;
                }

                var temp = document.createElement('div');
                temp.innerHTML = result.data.html;
                var newCards = Array.prototype.slice.call(temp.children);
                newCards.forEach(function (el) {
                    grid.appendChild(el);
                });

                // Image skeletons resolve on their own (each <img> fires its
                // own onload — see fp_lazy_img_attrs()); scroll-reveal needs
                // the new cards handed to the shared observer instance.
                if (window.fpObserveAnimations) {
                    window.fpObserveAnimations(newCards);
                }

                page += 1;

                if (page >= maxPages || !result.data.hasMore) {
                    button.parentElement.hidden = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More';
                }
            })
            .catch(function () {
                skeletons.forEach(function (el) { el.remove(); });
                button.disabled = false;
                button.textContent = 'Load More';
            });
    });
})();
