(function () {
  'use strict';
  document.querySelectorAll('.agcm-copy').forEach(function (button) {
    button.addEventListener('click', async function () {
      var el = document.getElementById(button.getAttribute('data-copy-target'));
      if (!el) return;
      try {
        if (navigator.clipboard && window.isSecureContext) await navigator.clipboard.writeText(el.value);
        else { el.select(); document.execCommand('copy'); el.blur(); }
        button.textContent = 'コピーしました';
      } catch (e) { el.focus(); el.select(); button.textContent = '選択しました（手動コピー）'; }
    });
  });
  document.querySelectorAll('input.agcm-rate').forEach(function (input) {
    var cell = input.closest('tr').querySelector('.agcm-calculated');
    input.addEventListener('input', function () {
      var n = Number(input.value), base = Number(input.dataset.base);
      cell.textContent = input.value === '' || !Number.isFinite(n) || n < 0 || n > 100
        ? '—' : '¥' + Math.floor(base * n / 100).toLocaleString('ja-JP');
    });
  });
  var buildQr = document.getElementById('agcm-build-qr');
  if (buildQr && typeof window.agcmQrSvg === 'function') {
    buildQr.addEventListener('click', function () {
      var url = document.getElementById('agcm-url');
      var preview = document.getElementById('agcm-qr-preview');
      var save = document.getElementById('agcm-qr-save');
      if (!url || !preview || !save) return;
      try {
        var svg = window.agcmQrSvg(url.value);
        preview.innerHTML = svg;
        save.href = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
        save.hidden = false;
        buildQr.textContent = 'QRコードを再生成';
      } catch (e) {
        preview.textContent = 'QRコードの生成に失敗しました。' + e.message;
      }
    });
  }
})();
