(function () {
    if (typeof fpAnalytics === 'undefined' || !fpAnalytics.measurementId) {
        return;
    }

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    var loaded = false;

    function loadGtag() {
        if (loaded) {
            return;
        }
        loaded = true;

        var script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + fpAnalytics.measurementId;
        document.head.appendChild(script);

        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', fpAnalytics.measurementId, { anonymize_ip: true });
    }

    // Only load once real consent for it exists — either already given on
    // a previous visit, or just now via the cookie banner (no reload
    // needed, see the fpcCookieConsent event dispatched by
    // cookie-consent.js).
    if (getCookie('fpc_cookie_consent') === 'all') {
        loadGtag();
    }

    document.addEventListener('fpcCookieConsent', function (event) {
        if (event.detail && event.detail.consent === 'all') {
            loadGtag();
        }
    });
})();
