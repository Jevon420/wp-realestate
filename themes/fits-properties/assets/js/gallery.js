(function () {
  var mainImg = document.getElementById('fp-gallery-main');
  var thumbs = document.querySelectorAll('.fp-gallery-thumb');

  if (!mainImg || !thumbs.length) {
    return;
  }

  thumbs.forEach(function (thumb) {
    thumb.addEventListener('click', function () {
      var full = thumb.getAttribute('data-full');
      if (full) {
        mainImg.setAttribute('src', full);
      }
    });
  });
})();
