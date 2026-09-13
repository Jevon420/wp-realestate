(function () {
    var thumbs = Array.prototype.slice.call(document.querySelectorAll('.fp-gallery-thumb'));
    var mainWrap = document.querySelector('.fp-property-gallery__main');

    if (!thumbs.length) {
        return;
    }

    var propertyTitle = document.title;
    var images = thumbs.map(function (thumb) {
        return { full: thumb.getAttribute('data-full') };
    });

    var currentIndex = 0;
    var lastFocused = null;
    var modal, imgEl, counterEl, prevBtn, nextBtn;

    function buildModal() {
        modal = document.createElement('div');
        modal.className = 'fp-lightbox';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-label', 'Property photo viewer');
        modal.hidden = true;
        modal.innerHTML =
            '<button type="button" class="fp-lightbox__close" aria-label="Close">&times;</button>' +
            '<button type="button" class="fp-lightbox__nav fp-lightbox__nav--prev" aria-label="Previous photo">&#8249;</button>' +
            '<div class="fp-lightbox__stage">' +
            '<img class="fp-lightbox__img" alt="">' +
            '<p class="fp-lightbox__counter"></p>' +
            '</div>' +
            '<button type="button" class="fp-lightbox__nav fp-lightbox__nav--next" aria-label="Next photo">&#8250;</button>';

        document.body.appendChild(modal);

        imgEl = modal.querySelector('.fp-lightbox__img');
        counterEl = modal.querySelector('.fp-lightbox__counter');
        prevBtn = modal.querySelector('.fp-lightbox__nav--prev');
        nextBtn = modal.querySelector('.fp-lightbox__nav--next');

        modal.querySelector('.fp-lightbox__close').addEventListener('click', close);
        prevBtn.addEventListener('click', function () { show(currentIndex - 1); });
        nextBtn.addEventListener('click', function () { show(currentIndex + 1); });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                close();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (modal.hidden) {
                return;
            }
            if (event.key === 'Escape') { close(); }
            if (event.key === 'ArrowLeft') { show(currentIndex - 1); }
            if (event.key === 'ArrowRight') { show(currentIndex + 1); }
        });
    }

    function show(index) {
        currentIndex = (index + images.length) % images.length;
        var image = images[currentIndex];

        imgEl.src = image.full;
        imgEl.alt = propertyTitle + ' — photo ' + (currentIndex + 1);
        counterEl.textContent = (currentIndex + 1) + ' / ' + images.length;

        var multiple = images.length > 1;
        prevBtn.hidden = !multiple;
        nextBtn.hidden = !multiple;
    }

    function open(index) {
        if (!modal) {
            buildModal();
        }

        lastFocused = document.activeElement;
        modal.hidden = false;
        document.body.classList.add('fp-lightbox-open');
        show(index);
        modal.querySelector('.fp-lightbox__close').focus();
    }

    function close() {
        modal.hidden = true;
        document.body.classList.remove('fp-lightbox-open');
        if (lastFocused && typeof lastFocused.focus === 'function') {
            lastFocused.focus();
        }
    }

    thumbs.forEach(function (thumb, index) {
        thumb.addEventListener('click', function () {
            open(index);
        });
    });

    if (mainWrap) {
        var viewAllBtn = document.createElement('button');
        viewAllBtn.type = 'button';
        viewAllBtn.className = 'fp-gallery-view-all';
        viewAllBtn.innerHTML = 'View All Photos <span>(' + images.length + ')</span>';
        mainWrap.appendChild(viewAllBtn);

        mainWrap.style.cursor = 'pointer';
        mainWrap.addEventListener('click', function () {
            open(0);
        });
    }
})();
