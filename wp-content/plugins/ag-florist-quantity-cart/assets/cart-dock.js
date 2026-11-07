/* AG FLOLIST fixed cart footer. Native WCEX markup, no session-changing AJAX calls. */
(function () {
  "use strict";
  document.addEventListener("DOMContentLoaded", function () {
    const dock = document.getElementById("agfl-cart-dock");
    if (!dock) return;
    const panel = dock.querySelector("#agfl-cart-dock-panel");
    const trigger = dock.querySelector("[data-agfl-dock-toggle]");
    const close = dock.querySelector("[data-agfl-dock-close]");
    const quantity = dock.querySelector("[data-agfl-dock-quantity]");
    const subtotal = dock.querySelector("[data-agfl-dock-subtotal]");
    const warning = dock.querySelector("[data-agfl-dock-warning]");
    const widget = dock.querySelector("#agfl-cart-dock-rows");
    let lastOpener = null;

    function setOpen(open) {
      panel.hidden = !open;
      dock.classList.toggle("is-open", open);
      trigger.setAttribute("aria-expanded", String(open));
      trigger.innerHTML = open
        ? 'カートを閉じる <span aria-hidden="true">⌄</span>'
        : 'カートの中身を見る <span aria-hidden="true">⌃</span>';
      if (open) {
        lastOpener = document.activeElement;
        close.focus();
      } else if (lastOpener && typeof lastOpener.focus === "function") {
        lastOpener.focus();
      }
    }
    trigger.addEventListener("click", function () {
      setOpen(panel.hidden);
    });
    close.addEventListener("click", function () {
      setOpen(false);
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && !panel.hidden) setOpen(false);
    });
    document.addEventListener("pointerdown", function (e) {
      if (!panel.hidden && !dock.contains(e.target)) setOpen(false);
    });

    function formatMoney(value) {
      return "¥" + Number(value || 0).toLocaleString("ja-JP");
    }
    function setTotals(qty, amount, hasZero) {
      quantity.textContent = Number(qty || 0).toLocaleString("ja-JP");
      subtotal.textContent = formatMoney(amount);
      dock.dataset.quantity = String(qty || 0);
      if (warning) warning.hidden = !hasZero;
    }
    document.addEventListener("agfl:cart-updated", function (event) {
      const data = event.detail || {};
      const lines = Array.isArray(data.items) ? data.items : [];
      const invalid = lines.some(function (r) {
        return Number(r.quantity || 0) > 0 && Number(r.price || 0) <= 0;
      });
      setTotals(data.total_quantity || 0, data.subtotal || 0, invalid);
      // stepper.js already updates #wgct_row with server-provided WCEX HTML.
    });

    // All footer rows are rendered from the same Welcart session snapshot as the totals.
    // Do not let a second widget recalculate or replace these rows.
  });
})();
