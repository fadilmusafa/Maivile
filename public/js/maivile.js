// Loading state: form ber-atribut data-loading menonaktifkan tombol submit agar tidak terkirim dua kali.
document.addEventListener('submit', function (e) {
  var form = e.target;
  if (!form.matches || !form.matches('form[data-loading]')) return;
  var btn = form.querySelector('button[type="submit"]');
  if (!btn || btn.disabled) return;
  setTimeout(function () {
    btn.disabled = true;
    btn.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-2 mv-spin" aria-hidden="true"></span>');
  }, 0);
});

// Jika pengguna menekan tombol Back, kembalikan tombol ke keadaan normal.
window.addEventListener('pageshow', function (e) {
  if (!e.persisted) return;
  document.querySelectorAll('form[data-loading] button[type="submit"]').forEach(function (b) {
    b.disabled = false;
    var s = b.querySelector('.mv-spin');
    if (s) s.remove();
  });
});

// Modal konfirmasi: form ber-atribut data-confirm="Pesan..." meminta persetujuan sebelum dikirim.
document.addEventListener('submit', function (e) {
  var form = e.target;
  if (!form.matches || !form.matches('form[data-confirm]') || form.dataset.confirmed === '1') return;

  e.preventDefault();
  e.stopImmediatePropagation();

  var modalEl = document.getElementById('mvConfirmModal');
  if (!modalEl || !window.bootstrap) {
    if (window.confirm(form.dataset.confirm)) { form.dataset.confirmed = '1'; form.submit(); }
    return;
  }

  modalEl.querySelector('.mv-confirm-text').textContent = form.dataset.confirm;
  var ok = modalEl.querySelector('.mv-confirm-ok');
  var fresh = ok.cloneNode(true);           // buang handler lama
  ok.parentNode.replaceChild(fresh, ok);
  var modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
  fresh.addEventListener('click', function () {
    form.dataset.confirmed = '1';
    modal.hide();
    form.submit();
  });
  modal.show();
}, true);
