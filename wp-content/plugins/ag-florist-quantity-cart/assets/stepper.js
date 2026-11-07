/* AG FLOLIST: Welcart session-backed +/- buttons with cart-page return recovery. */
(function () {
  "use strict";
  document.addEventListener("DOMContentLoaded", function () {
    const root = document.querySelector("#main.ag-product-category--premium");
    const config = window.agflStepper || {};
    if (!root || !config.url || !config.member) return;

    let busy = false;
    let ready = false;
    let syncRun = 0;
    const steppers = Array.from(
      root.querySelectorAll(".ag-stepper[data-post-id][data-sku]"),
    );
    const alert = root.querySelector("[data-stepper-alert]");

    function setMessage(message, isError) {
      if (!alert) return;
      alert.textContent = message || "";
      alert.classList.toggle("is-error", !!isError);
      alert.hidden = !message;
    }

    function refresh(resp) {
      const lines = resp.items || [];
      const prices = resp.prices || [];
      steppers.forEach(function (stepper) {
        const post = Number(stepper.dataset.postId);
        const sku = stepper.dataset.sku;
        const match = lines.find(function (l) {
          return Number(l.post_id) === post && l.sku === sku;
        });
        const count = match ? Number(match.quantity) : 0;
        const number = stepper.querySelector("[data-stepper-number]");
        if (number) number.textContent = String(count);
        const minus = stepper.querySelector('[data-stepper-op="minus"]');
        if (minus) minus.disabled = count <= 0;
        const updated = prices.find(function (p) {
          return Number(p.post_id) === post && p.sku === sku;
        });
        if (updated) {
          const product = stepper.closest(".ag-list-product__sku");
          const price =
            product && product.querySelector("[data-stepper-price]");
          if (price)
            price.textContent =
              "¥" + Number(updated.price).toLocaleString("ja-JP");
        }
      });
      root
        .querySelectorAll("[data-stepper-total-items]")
        .forEach(function (node) {
          node.textContent = String(Number(resp.total_quantity || 0));
        });
      root.querySelectorAll("[data-tansa-cart-count]").forEach(function (node) {
        node.textContent = String(Number(resp.tansa_quantity || 0));
      });
      const q = Number(resp.tansa_quantity || 0);
      root.querySelectorAll(".ag-list-tiers__item").forEach(function (el) {
        const lo = Number(el.dataset.min || 0);
        const hi = Number(el.dataset.max || 999999);
        el.classList.toggle("is-current", q >= lo && q <= hi);
      });
      if (typeof resp.widget_html === "string") {
        const widget = document.querySelector("#agfl-cart-dock-rows");
        if (widget) widget.innerHTML = resp.widget_html;
      }
      if (typeof resp.token === "string" && resp.token.length > 0) {
        config.token = resp.token;
      }
    }

    function appendVisible(form) {
      // Resync all prices, including TANSA's 5/6 and 11/12 tier transitions.
      steppers.forEach(function (stepper, i) {
        form.append("visible[" + i + "][post_id]", stepper.dataset.postId);
        form.append("visible[" + i + "][sku]", stepper.dataset.sku);
      });
    }

    function request(form) {
      return fetch(config.url, {
        method: "POST",
        credentials: "same-origin",
        cache: "no-store",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
          Accept: "application/json",
        },
        body: form.toString(),
      }).then(function (response) {
        return response.text().then(function (raw) {
          let body;
          try {
            body = JSON.parse(raw);
          } catch (_) {
            throw new Error(
              "通信エラー（HTTP " +
                response.status +
                "）。ページを更新してお試しください。",
            );
          }
          if (!response.ok || !body.success) {
            throw new Error(
              (body.data && body.data.message) ||
                "数量情報の取得に失敗しました（HTTP " +
                  response.status +
                  "）。",
            );
          }
          return body.data;
        });
      });
    }

    /** Back-forward cache may restore stale DOM/token, even though the cart session changed. */
    function syncCart() {
      if (busy) return Promise.resolve();
      busy = true;
      const run = ++syncRun;
      root.classList.add("ag-stepper-is-busy");
      setMessage("カートの最新情報を確認しています…", false);
      const form = new URLSearchParams();
      form.append("op", "sync"); // Read-only: no outdated token required.
      appendVisible(form);
      return request(form)
        .then(function (data) {
          if (run !== syncRun) return;
          refresh(data);
          ready = true;
          setMessage("", false);
          document.dispatchEvent(
            new CustomEvent("agfl:cart-updated", { detail: data }),
          );
        })
        .catch(function (error) {
          ready = false;
          setMessage(
            error.message ||
              "カートの状態を確認できません。ページを更新してください。",
            true,
          );
        })
        .finally(function () {
          if (run !== syncRun) return;
          busy = false;
          root.classList.remove("ag-stepper-is-busy");
        });
    }

    steppers.forEach(function (stepper) {
      stepper.addEventListener("click", function (e) {
        const button = e.target.closest("[data-stepper-op]");
        if (!button || !stepper.contains(button) || button.disabled) return;
        e.preventDefault();
        if (busy) return; // Do not modify the cart until stale state is refreshed.
        if (!ready) {
          syncCart();
          return;
        }
        busy = true;
        setMessage("カートの数量を更新しています…", false);
        const form = new URLSearchParams();
        form.append("token", config.token || "");
        form.append("post_id", stepper.dataset.postId);
        form.append("sku", stepper.dataset.sku);
        form.append("op", button.dataset.stepperOp);
        appendVisible(form);
        root.classList.add("ag-stepper-is-busy");
        request(form)
          .then(function (data) {
            refresh(data);
            setMessage("カートの数量を更新しました。", false);
            document.dispatchEvent(
              new CustomEvent("agfl:cart-updated", { detail: data }),
            );
          })
          .catch(function (error) {
            ready = false;
            setMessage(
              error.message ||
                "更新できませんでした。ページを再読み込みしてください。",
              true,
            );
            // A failed request can mean the browser kept a stale Welcart token.
            // Fetch the CURRENT cart without replaying a possibly committed +/- request.
            // This prevents accidental duplicate additions after a network failure.
          })
          .finally(function () {
            busy = false;
            root.classList.remove("ag-stepper-is-busy");
          });
      });
    });

    // pageshow fires on both normal navigation and Safari/Chrome back-forward restores.
    // Do not use location.reload(): it would lose the user's scroll position.
    window.addEventListener("pageshow", function () {
      syncCart();
    });
  });
})();
