(function () {
  var toggle = document.querySelector('.fp-nav-toggle');
  var mobileNav = document.getElementById('fp-mobile-nav');

  if (!toggle || !mobileNav) {
    return;
  }

  toggle.addEventListener('click', function () {
    var isOpen = mobileNav.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
})();
