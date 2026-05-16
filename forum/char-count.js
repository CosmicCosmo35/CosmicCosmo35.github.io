document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('textarea[maxlength]').forEach(function(textarea) {
    var counter = textarea.parentElement.querySelector('.char-count');
    if (!counter) return;

    function update() {
      var len = textarea.value.length;
      var max = parseInt(textarea.getAttribute('maxlength'));
      counter.textContent = len + ' / ' + max;
      counter.classList.toggle('over', len > max * 0.9);
    }

    textarea.addEventListener('input', update);
    update();
  });
});
