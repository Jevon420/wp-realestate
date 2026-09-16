(function () {
  var toggle = document.querySelector('.fp-nav-toggle');
  var mobileNav = document.getElementById('fp-mobile-nav');

  if (toggle && mobileNav) {
    toggle.addEventListener('click', function () {
      var isOpen = mobileNav.classList.toggle('is-open');
      toggle.classList.toggle('is-open', isOpen);
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    // Close the menu after tapping a link, rather than leaving it open
    // when the visitor comes back via the browser's back button.
    mobileNav.addEventListener('click', function (event) {
      if (event.target.closest('a')) {
        mobileNav.classList.remove('is-open');
        toggle.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  var header = document.querySelector('.fp-header');

  if (header) {
    var updateHeaderShadow = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 8);
    };
    updateHeaderShadow();
    window.addEventListener('scroll', updateHeaderShadow, { passive: true });
  }
})();
