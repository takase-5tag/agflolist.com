/* AG FLOLIST - read-only columns in Welcart Management > Member List.
 * Supports a classic Welcart table and redraws after pagination/search.
 * Never touches Welcart data or member registration.
 */
(function () {
  "use strict";
  if (typeof agcmMemberDirectory === "undefined") return;
  var targetSelector = "#mainDataTable";
  var working = false;
  var pending = false;
  var timer = 0;

  function getMemberId(row) {
    var anchors = row.querySelectorAll('a[href*="member_id="]');
    for (var i = 0; i < anchors.length; i++) {
      try {
        var url = new URL(anchors[i].href, window.location.href);
        if (url.searchParams.get("member_action") !== "edit") continue;
        var id = Number(url.searchParams.get("member_id"));
        if (Number.isSafeInteger(id) && id > 0) return id;
      } catch (e) {
        /* other Welcart URL types are ignored */
      }
    }
    return 0;
  }
  function insertCell(row, text, extra) {
    var td = document.createElement("td");
    td.className = "agcm-native-member-cell" + (extra ? " " + extra : "");
    td.textContent = text;
    var last = row.lastElementChild;
    if (last && /^(TD|TH)$/i.test(last.tagName)) row.insertBefore(td, last);
    else row.appendChild(td);
    return td;
  }
  function insertHeader(row, name) {
    var th = document.createElement("th");
    th.className = "agcm-native-member-head";
    th.scope = "col";
    th.textContent = name;
    var last = row.lastElementChild;
    if (last && /^(TD|TH)$/i.test(last.tagName)) row.insertBefore(th, last);
    else row.appendChild(th);
  }
  function render() {
    if (working) {
      pending = true;
      return;
    }
    var table = document.querySelector(targetSelector);
    if (!table) return;
    var rows = Array.prototype.slice.call(table.querySelectorAll("tr"));
    if (!rows.length) return;
    var requests = {},
      unprocessed = [];
    rows.forEach(function (row) {
      var id = getMemberId(row);
      if (!id || row.querySelector(".agcm-native-member-cell")) return;
      var typeCell = insertCell(row, "取得中…", "agcm-native-member-type");
      var companyCell = insertCell(row, "—", "agcm-native-member-company");
      requests[id] = requests[id] || [];
      requests[id].push({ type: typeCell, company: companyCell });
      unprocessed.push(id);
    });
    if (!unprocessed.length) return;
    if (!table.querySelector(".agcm-native-member-head")) {
      // Welcart's list header precedes the rows, with a final delete/action column.
      var header = rows.find(function (row) {
        return !getMemberId(row) && row.querySelector("th");
      });
      if (header) {
        insertHeader(header, "登録区分");
        insertHeader(header, "所属企業");
      }
    }
    working = true;
    var form = new FormData();
    form.append("action", "agcm_member_row_labels");
    form.append("nonce", agcmMemberDirectory.nonce);
    Object.keys(requests).forEach(function (id) {
      form.append("member_ids[]", id);
    });
    fetch(agcmMemberDirectory.ajaxUrl, {
      method: "POST",
      body: form,
      credentials: "same-origin",
    })
      .then(function (response) {
        if (!response.ok) throw new Error("HTTP " + response.status);
        return response.json();
      })
      .then(function (body) {
        if (!body || !body.success) throw new Error("JSON response");
        Object.keys(requests).forEach(function (id) {
          var data = (body.data && body.data[id]) || {
            label: "未分類",
            company: "—",
            type: "",
          };
          requests[id].forEach(function (c) {
            if (!c.type.isConnected || !c.company.isConnected) return;
            c.type.textContent = data.label || "未分類";
            c.type.setAttribute("data-agcm-kind", data.type || "unknown");
            c.company.textContent = data.company || "—";
          });
        });
      })
      .catch(function () {
        Object.keys(requests).forEach(function (id) {
          requests[id].forEach(function (c) {
            if (c.type.isConnected) c.type.textContent = "取得失敗";
          });
        });
      })
      .finally(function () {
        working = false;
        if (pending) {
          pending = false;
          setTimeout(render, 0);
        }
      });
  }
  function schedule() {
    clearTimeout(timer);
    timer = setTimeout(render, 160);
  }
  document.addEventListener("DOMContentLoaded", function () {
    render();
    var wrap = document.getElementById("datatable") || document.body;
    new MutationObserver(function (events) {
      if (
        events.some(function (e) {
          return e.addedNodes.length || e.removedNodes.length;
        })
      )
        schedule();
    }).observe(wrap, { childList: true, subtree: true });
  });
})();
