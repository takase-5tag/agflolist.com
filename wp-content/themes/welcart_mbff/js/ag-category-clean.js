/* AG FLOLIST 商品一覧。表示のみ調整、Welcart購入イベントには触れない。 */
(function () {
  "use strict";
  function applyCleanUI() {
    var root = document.querySelector("#main.ag-product-category--premium");
    if (!root) return;

    // 商品詳細ページへ移動する画像・商品名のリンクを文字/画像に変換。
    // 購入ボタン・トライアル案内・カテゴリーのアンカーは維持する。
    root
      .querySelectorAll(
        ".ag-list-product__visual > a, .ag-list-product__content h3 > a",
      )
      .forEach(function (link) {
        var wrapper = document.createElement("span");
        wrapper.className = link.parentElement.classList.contains(
          "ag-list-product__visual",
        )
          ? "ag-clean-static-image"
          : "ag-clean-static-title";
        while (link.firstChild) wrapper.appendChild(link.firstChild);
        link.replaceWith(wrapper);
      });
    root.querySelectorAll(".ag-list-product__detail").forEach(function (el) {
      el.remove();
    });

    // 説明をなくさず、長いものだけ3行に折りたたむ。
    root.querySelectorAll(".ag-list-product__intro").forEach(function (el) {
      if (el.hasAttribute("data-ag-clean-ready")) return;
      el.setAttribute("data-ag-clean-ready", "1");
      var computed = window.getComputedStyle(el);
      var fontSize = parseFloat(computed.fontSize) || 12;
      var lineHeight = parseFloat(computed.lineHeight) || fontSize * 1.8;
      if (el.scrollHeight <= lineHeight * 3 + 3) return;
      el.classList.add("is-clamped");
      var toggle = document.createElement("button");
      toggle.type = "button";
      toggle.className = "ag-clean-more";
      toggle.textContent = "商品説明をもっと見る ＋";
      toggle.setAttribute("aria-expanded", "false");
      el.after(toggle);
      toggle.addEventListener("click", function () {
        var expanded = el.classList.toggle("is-expanded");
        el.classList.toggle("is-clamped", !expanded);
        toggle.textContent = expanded
          ? "説明を閉じる −"
          : "商品説明をもっと見る ＋";
        toggle.setAttribute("aria-expanded", expanded ? "true" : "false");
      });
    });
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", applyCleanUI);
  } else {
    applyCleanUI();
  }
})();
