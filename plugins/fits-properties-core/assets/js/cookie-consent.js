(function () {
    var COOKIE_NAME = 'fpc_cookie_consent';
    var COOKIE_DAYS = 180;

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function setCookie(name, value, days) {
        var expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + expires.toUTCString() + ';path=/;SameSite=Lax';
    }

    var banner = document.getElementById('fpc-cookie-consent');

    if (!banner) {
        return;
    }

    if (!getCookie(COOKIE_NAME)) {
        banner.hidden = false;
        // Two ticks so the browser registers the starting state before
        // the transition runs (matches the pattern used elsewhere on the
        // site for the same reason).
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                banner.classList.add('is-visible');
            });
        });
    }

    banner.addEventListener('click', function (event) {
        var button = event.target.closest('[data-fpc-consent]');

        if (!button) {
            return;
        }

        setCookie(COOKIE_NAME, button.getAttribute('data-fpc-consent'), COOKIE_DAYS);
        banner.classList.remove('is-visible');
        window.setTimeout(function () {
            banner.hidden = true;
        }, 300);
    });
})();
