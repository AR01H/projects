/**
 * assets/js/ui-kit.js - the shared UI behaviours, as classes.
 *
 * ONE dialog system for the whole site, plus the behaviours that sit beside
 * it (toasts, dismissible notices, tabs, the paper story, drifting leaves,
 * copy-to-clipboard).
 *
 * ── OOP, mirroring the PHP side ────────────────────────────────────────────
 * Every behaviour is a class with the same three-part contract as the feature
 * classes in src/: it is CONSTRUCTED with its element + options, it OWNS its
 * own state, and it exposes a small public API. NTUiKit is the façade that
 * boots them and is exported as window.NT / window.NTUI.
 *
 *   NTStore        localStorage that cannot throw
 *   NTDialogView   builds dialog markup from a data object  (the renderer)
 *   NTDialog       one dialog instance: open/close/focus/auto-open
 *   NTDialogs      finds + owns every dialog on the page, and creates
 *                  runtime ones from data (alert / confirm / show)
 *   NTToaster      the toast stack
 *   NTAlerts       inline alerts: render from data, dismiss, remember
 *   NTNotices      the announcement strip
 *   NTTabs / NTPaperStory / NTLeafDrift / NTCopy
 *   NTUiKit        boots and re-scans them all
 *
 * ── Data in, dialog out ────────────────────────────────────────────────────
 * NTDialogView understands the SAME object shape as admin/data/dialogs.json,
 * so a dialog you POST back from an AJAX call renders identically to one
 * declared in PHP:
 *
 *   NT.dialog.show({
 *     title: 'Order received', kicker: 'Thank you', tone: 'success',
 *     icon: 'check', size: 'sm', body: ['Line one', 'Line two'],
 *     list: ['Pressed this morning'],
 *     actions: [ { label: 'Track it', url: '/order/', style: 'primary' },
 *                { label: 'Close', style: 'ghost', dialog_close: true } ]
 *   });
 *
 *   NT.alert('Saved.')            NT.confirm({ body: 'Delete this?' })
 *   NT.toast('Copied', 'success') NT.alerts.render(target, { tone, body })
 *
 * NO user-facing string lives in this file. Every word comes from
 * window.ntUi, which PHP builds from admin/data/ui.json (NT_Ui::js_config()).
 *
 * Everything degrades: with JS off dialogs stay closed, notices stay visible,
 * tabs render every panel stacked and the paper story reads top to bottom.
 */
(function () {
  "use strict";

  var CFG = window.ntUi || {};
  var LABELS = CFG.labels || {};
  var ARIA = CFG.aria || {};
  var ICONS = CFG.icons || {};
  var DEFAULTS = CFG.defaults || {};
  var TONES = CFG.tones || [
    "info",
    "success",
    "warning",
    "error",
    "note",
    "question",
  ];

  var DIALOG_PREFIX = "app-dialog-";

  /** Copy lookup - never prints "undefined" for a missing key. */
  function t(key, fallback) {
    var value = LABELS[key];
    return typeof value === "string" && value !== "" ? value : fallback || "";
  }

  function reducedMotion() {
    return !!(
      window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches
    );
  }

  function toneOrDefault(value) {
    return TONES.indexOf(value) !== -1 ? value : DEFAULTS.tone || "note";
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTStore - localStorage that cannot throw.
	   Private-mode browsers reject writes; a storage failure must never take
	   the page down, so every call degrades to "nothing was remembered".
	   ══════════════════════════════════════════════════════════════════════ */

  class NTStore {
    constructor(key) {
      this.key = key;
    }
    read() {
      try {
        var raw = window.localStorage.getItem(this.key);
        var parsed = raw ? JSON.parse(raw) : [];
        return Array.isArray(parsed) ? parsed : [];
      } catch (e) {
        return [];
      }
    }
    has(id) {
      return this.read().indexOf(String(id)) !== -1;
    }
    add(id) {
      try {
        var list = this.read();
        if (list.indexOf(String(id)) === -1) {
          list.push(String(id));
          window.localStorage.setItem(this.key, JSON.stringify(list));
        }
      } catch (e) {
        /* storage unavailable - the dismissal just won't persist */
      }
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTDialogView - the renderer.
	   Turns a plain data object (the dialogs.json shape) into the exact same
	   DOM that components/parts/dialog.php prints server-side. This is the
	   ONLY place dialog markup is built in JS, so the two stay in step.

	   SAFETY: every caller-supplied string goes in through textContent. The
	   only innerHTML is CFG.icons, which is SVG this site's PHP generated.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTDialogView {
    /**
     * @param {object} data dialogs.json-shaped object.
     * @returns {HTMLDialogElement}
     */
    static build(data) {
      var d = NTDialogView.normalise(data);

      var dlg = document.createElement("dialog");
      dlg.className = [
        "app-dialog",
        "app-dialog--" + d.tone,
        "app-dialog--" + d.size,
        d.cssClass,
      ]
        .filter(Boolean)
        .join(" ");
      if (d.id) {
        dlg.id =
          d.id.indexOf(DIALOG_PREFIX) === 0 ? d.id : DIALOG_PREFIX + d.id;
      }
      if (!d.dismissible) {
        dlg.setAttribute("data-nt-dialog-locked", "1");
      }
      if (d.autoOpen > 0) {
        dlg.setAttribute("data-nt-dialog-auto", String(d.autoOpen));
      }
      if (d.once) {
        dlg.setAttribute("data-nt-dialog-once", d.once);
      }

      var form = document.createElement("form");
      form.method = "dialog";
      form.className = "app-dialog__shell";

      var paper = document.createElement("div");
      paper.className = "app-dialog__paper";

      if (d.dismissible) {
        paper.appendChild(NTDialogView.closeButton());
      }
      paper.appendChild(NTDialogView.head(d));

      var body = NTDialogView.body(d);
      if (body) {
        paper.appendChild(body);
      }

      var actions = NTDialogView.actions(d);
      if (actions) {
        paper.appendChild(actions);
      }

      ["tl", "tr", "bl", "br"].forEach(function (pos) {
        var corner = document.createElement("span");
        corner.className = "app-dialog__corner app-dialog__corner--" + pos;
        corner.setAttribute("aria-hidden", "true");
        paper.appendChild(corner);
      });

      form.appendChild(paper);
      dlg.appendChild(form);
      return dlg;
    }

    /** Fill in defaults so the builders below never have to guess. */
    static normalise(data) {
      data = typeof data === "string" ? { body: data } : data || {};
      var tone = toneOrDefault(data.tone);
      var body = data.body;
      if (typeof body === "string") {
        body = body.split("\n");
      }

      return {
        id: data.id || "",
        tone: tone,
        size:
          ["sm", "md", "lg", "full"].indexOf(data.size) !== -1
            ? data.size
            : DEFAULTS.size || "md",
        icon: data.icon || tone,
        kicker: data.kicker || "",
        title: data.title || "",
        body: (body || []).filter(function (line) {
          return String(line).trim() !== "";
        }),
        list: data.list || [],
        image: data.image || "",
        imageAlt: data.image_alt || "",
        form:
          data.form && data.form.fields && data.form.fields.length
            ? data.form
            : null,
        actions: data.actions || [],
        dismissible: data.dismissible !== false,
        autoOpen: parseInt(data.auto_open, 10) || 0,
        once: data.once || "",
        cssClass: data.class || "",
      };
    }

    static closeButton() {
      var btn = document.createElement("button");
      btn.type = "submit";
      btn.value = "close";
      btn.className = "app-dialog__close";
      btn.setAttribute("aria-label", ARIA.close || t("close", "Close"));
      if (ICONS.close) {
        btn.innerHTML = ICONS.close;
      } // trusted: PHP-generated SVG
      return btn;
    }

    static head(d) {
      var head = document.createElement("header");
      head.className = "app-dialog__head";

      var iconSvg = ICONS[d.icon] || ICONS[d.tone];
      if (iconSvg) {
        var icon = document.createElement("span");
        icon.className = "app-dialog__icon";
        icon.setAttribute("aria-hidden", "true");
        icon.innerHTML = iconSvg; // trusted: PHP-generated SVG
        head.appendChild(icon);
      } else if (d.icon && !ICONS[d.icon]) {
        // An emoji or any other literal the data supplied.
        var glyph = document.createElement("span");
        glyph.className = "app-dialog__icon app-icon-emoji";
        glyph.setAttribute("aria-hidden", "true");
        glyph.textContent = d.icon;
        head.appendChild(glyph);
      }

      if (d.kicker) {
        var kicker = document.createElement("span");
        kicker.className = "app-dialog__kicker";
        kicker.textContent = d.kicker;
        head.appendChild(kicker);
      }

      var title = document.createElement("h2");
      title.className = "app-dialog__title";
      title.textContent = d.title;
      head.appendChild(title);

      var rule = document.createElement("span");
      rule.className = "app-dialog__rule";
      rule.setAttribute("aria-hidden", "true");
      head.appendChild(rule);

      return head;
    }

    static body(d) {
      if (!d.body.length && !d.list.length && !d.image && !d.form) {
        return null;
      }

      var wrap = document.createElement("div");
      wrap.className = "app-dialog__body";

      if (d.image) {
        var figure = document.createElement("figure");
        figure.className = "app-dialog__figure";
        var img = document.createElement("img");
        img.src = d.image;
        img.alt = d.imageAlt;
        img.loading = "lazy";
        figure.appendChild(img);
        wrap.appendChild(figure);
      }

      d.body.forEach(function (line) {
        var p = document.createElement("p");
        p.className = "app-dialog__text";
        p.textContent = line; // textContent = XSS-safe
        wrap.appendChild(p);
      });

      if (d.list.length) {
        var ul = document.createElement("ul");
        ul.className = "app-dialog__list";
        d.list.forEach(function (point) {
          var li = document.createElement("li");
          if (ICONS.check) {
            var tick = document.createElement("span");
            tick.className = "app-dialog__tick";
            tick.innerHTML = ICONS.check; // trusted: PHP-generated SVG
            li.appendChild(tick);
          }
          var span = document.createElement("span");
          span.textContent = point;
          li.appendChild(span);
          ul.appendChild(li);
        });
        wrap.appendChild(ul);
      }

      if (d.form) {
        var formWrap = document.createElement("div");
        formWrap.className = "app-dialog__form";
        formWrap.appendChild(NTDialogView.form(d.form));
        wrap.appendChild(formWrap);
      }

      return wrap;
    }

    /**
     * Build a real, working form from the field data PHP resolved out of
     * admin/data/form_*.json - the same definitions the inline form
     * component uses. The submit is wired by the data-nt-ajax-form
     * handler in common.js the moment the dialog opens.
     */
    static form(form) {
      var el = document.createElement("form");
      el.className = ["app-form", form.class || ""].filter(Boolean).join(" ");
      if (form.id) {
        el.id = form.id;
      }
      if (form.action) {
        el.setAttribute("data-nt-ajax-form", form.action);
      }
      el.setAttribute("novalidate", "");

      (form.fields || []).forEach(function (field) {
        var group = document.createElement("div");
        group.className = "app-form-group app-form-row";

        if (field.label) {
          var label = document.createElement("label");
          label.className = "app-form-label";
          label.htmlFor = field.id;
          label.textContent = field.label + (field.required ? " *" : "");
          group.appendChild(label);
        }

        var input;
        if (field.type === "textarea") {
          input = document.createElement("textarea");
          input.className = "app-form-textarea";
          input.rows = 5;
        } else if (field.type === "select") {
          input = document.createElement("select");
          input.className = "app-form-select";
          Object.keys(field.options || {}).forEach(function (value) {
            var option = document.createElement("option");
            option.value = value;
            option.textContent = field.options[value];
            input.appendChild(option);
          });
        } else {
          input = document.createElement("input");
          input.className = "app-form-input";
          input.type = field.type || "text";
        }

        input.id = field.id;
        input.name = field.name;
        if (field.placeholder) {
          input.placeholder = field.placeholder;
        }
        if (field.required) {
          input.required = true;
        }
        group.appendChild(input);

        el.appendChild(group);
      });

      var submit = document.createElement("button");
      submit.type = "submit";
      submit.className = "app-btn button app-form-submit";
      submit.textContent = form.submit || t("submit", "Send");
      el.appendChild(submit);

      var status = document.createElement("p");
      status.className = "app-form-status";
      status.setAttribute("role", "status");
      status.setAttribute("aria-live", "polite");
      status.style.display = "none";
      el.appendChild(status);

      return el;
    }

    static actions(d) {
      if (!d.actions.length) {
        return null;
      }

      var footer = document.createElement("footer");
      footer.className = "app-dialog__actions";

      d.actions.forEach(function (action) {
        if (!action || !action.label) {
          return;
        }
        var style =
          ["primary", "ghost", "danger"].indexOf(action.style) !== -1
            ? action.style
            : "primary";
        var cls = "app-dialog__btn app-dialog__btn--" + style;
        var node;

        if (action.url) {
          node = document.createElement("a");
          node.href = action.url;
          if (action.new_tab) {
            node.target = "_blank";
            node.rel = "noopener noreferrer";
          }
        } else {
          node = document.createElement("button");
          node.type = action.dialog_close === false ? "button" : "submit";
          node.value = action.value || "ok";
          if (action.action) {
            node.setAttribute("data-nt-dialog-action", action.action);
          }
        }
        node.className = cls;
        node.textContent = action.label;
        footer.appendChild(node);
      });

      return footer.children.length ? footer : null;
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTDialog - one dialog instance.
	   Wraps a native <dialog>, which already gives us the top layer, a focus
	   trap and Esc. This class adds backdrop-click, focus restore, named
	   actions, auto-open and "show once per visitor".
	   ══════════════════════════════════════════════════════════════════════ */

  class NTDialog {
    constructor(element, options) {
      this.el = element;
      this.options = options || {};
      this.opener = null;
      this.seen = new NTStore(DEFAULTS.dialogStore || "app_seen_dialogs");
      this.bind();
    }

    get locked() {
      return this.el.hasAttribute("data-nt-dialog-locked");
    }

    bind() {
      var self = this;

      this.el.addEventListener("close", function () {
        self.onClosed();
      });

      this.el.addEventListener("click", function (e) {
        // The <dialog> box fills the element, so a click that lands on
        // the element itself (not the paper) came from the backdrop.
        if (!self.locked && e.target === self.el) {
          self.close("backdrop");
        }
      });

      this.el.addEventListener("cancel", function (e) {
        if (self.locked) {
          e.preventDefault();
        } // a locked dialog refuses Esc too
      });

      this.el
        .querySelectorAll("[data-nt-dialog-action]")
        .forEach(function (btn) {
          btn.addEventListener("click", function () {
            self.runAction(btn.getAttribute("data-nt-dialog-action"));
          });
        });

      this.scheduleAutoOpen();
    }

    /** "welcome" style dialogs: open themselves, optionally only once ever. */
    scheduleAutoOpen() {
      var delay = parseInt(this.el.getAttribute("data-nt-dialog-auto"), 10);
      if (!(delay > 0)) {
        return;
      }

      var onceKey = this.el.getAttribute("data-nt-dialog-once") || "";
      if (onceKey && this.seen.has(onceKey)) {
        return;
      }

      var self = this;
      window.setTimeout(function () {
        // Never ambush someone already reading another dialog.
        if (document.querySelector("dialog.app-dialog[open]")) {
          return;
        }
        self.open();
        if (onceKey) {
          self.seen.add(onceKey);
        }
      }, delay);
    }

    open(opener) {
      this.opener = opener || document.activeElement;

      if (typeof this.el.showModal === "function") {
        if (!this.el.open) {
          this.el.showModal();
        }
      } else {
        this.el.setAttribute("open", ""); // no <dialog> support: still usable
      }
      this.el.classList.add("is-open");
      document.documentElement.classList.add("app-dialog-open");
      this.focusFirst();
      return this;
    }

    close(returnValue) {
      if (typeof this.el.close === "function" && this.el.open) {
        this.el.close(returnValue || "");
      } else {
        this.el.removeAttribute("open");
        this.onClosed();
      }
      return this;
    }

    focusFirst() {
      var target =
        this.el.querySelector(
          "[autofocus], input:not([type=hidden]), textarea, select, .app-dialog__btn--primary",
        ) || this.el.querySelector(".app-dialog__close");
      if (target) {
        window.requestAnimationFrame(function () {
          target.focus();
        });
      }
    }

    onClosed() {
      this.el.classList.remove("is-open");
      if (!document.querySelector("dialog.app-dialog[open]")) {
        document.documentElement.classList.remove("app-dialog-open");
      }
      if (
        this.opener &&
        typeof this.opener.focus === "function" &&
        document.contains(this.opener)
      ) {
        this.opener.focus();
      }
      this.opener = null;
    }

    /** The named actions a JSON button may fire, with no bespoke JS. */
    runAction(name) {
      switch (name) {
        case "print":
          window.print();
          break;
        case "reload":
          window.location.reload();
          break;
        case "back":
          window.history.back();
          break;
        case "close":
          this.close();
          break;
        default:
          document.dispatchEvent(
            new CustomEvent("nt:dialog-action", {
              detail: { action: name, dialog: this.el, instance: this },
            }),
          );
      }
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTDialogs - owns every dialog on the page, and builds runtime ones.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTDialogs {
    constructor(registry) {
      // The declared dialogs PHP shipped for this page, keyed by DOM id.
      // Nothing is in the document yet - each one is built the first
      // time it is opened, and a dialog nobody opens costs nothing.
      this.registry = registry || {};
      this.instances = new Map();
    }

    scan() {
      var self = this;

      // Anything already in the DOM (a legacy <dialog>, or one built
      // earlier in this page's life) still gets adopted.
      document.querySelectorAll("dialog.app-dialog").forEach(function (el) {
        if (el.dataset.ntDialogInit) {
          return;
        }
        el.dataset.ntDialogInit = "1";
        var instance = new NTDialog(el);
        if (el.id) {
          self.instances.set(el.id, instance);
        }
      });

      document
        .querySelectorAll("[data-nt-dialog-open]")
        .forEach(function (trigger) {
          if (trigger.dataset.ntDialogBound) {
            return;
          }
          trigger.dataset.ntDialogBound = "1";
          trigger.addEventListener("click", function (e) {
            e.preventDefault();
            self.open(trigger.getAttribute("data-nt-dialog-open"), trigger);
          });
        });

      // Declared dialogs that open themselves need building up front,
      // since no click will ever trigger them.
      Object.keys(this.registry).forEach(function (domId) {
        if (self.registry[domId].auto_open > 0) {
          self.get(domId);
        }
      });

      return this;
    }

    /**
     * Find (or build) an instance by DOM id, dialog key or element.
     * Building is lazy: the markup only enters the document here.
     */
    get(idOrEl) {
      if (!idOrEl) {
        return null;
      }

      if (idOrEl.nodeType === 1) {
        return this.adopt(idOrEl);
      }

      var key = String(idOrEl);
      var domId = key.indexOf(DIALOG_PREFIX) === 0 ? key : DIALOG_PREFIX + key;

      if (this.instances.has(domId)) {
        return this.instances.get(domId);
      }

      var existing =
        document.getElementById(domId) || document.getElementById(key);
      if (existing) {
        return this.adopt(existing);
      }

      var data = this.registry[domId] || this.registry[key];
      if (!data) {
        return null;
      }

      var el = NTDialogView.build(data);
      document.body.appendChild(el);
      var instance = this.adopt(el);

      // A just-built dialog may hold a form; hand it to the AJAX form
      // binder in common.js so submitting works exactly as it does for
      // a form rendered inline on the page.
      if (window.NT && typeof window.NT.bindForms === "function") {
        window.NT.bindForms();
      }
      return instance;
    }

    /** Wrap an existing element in an NTDialog and remember it. */
    adopt(el) {
      if (el.id && this.instances.has(el.id)) {
        return this.instances.get(el.id);
      }
      var instance = new NTDialog(el);
      el.dataset.ntDialogInit = "1";
      if (el.id) {
        this.instances.set(el.id, instance);
      }
      return instance;
    }

    open(idOrEl, opener) {
      var instance = this.get(idOrEl);
      return instance ? instance.open(opener) : null;
    }

    close(idOrEl, value) {
      var instance = this.get(idOrEl);
      return instance ? instance.close(value) : null;
    }

    /**
     * Build and open a dialog from data - the same object shape as
     * admin/data/dialogs.json. Resolves with the button value that closed
     * it ('ok', 'cancel', 'close', 'backdrop' …), so a caller can branch.
     *
     * @param {object|string} data
     * @returns {Promise<string>}
     */
    show(data) {
      var el = NTDialogView.build(data);
      document.body.appendChild(el);

      var instance = new NTDialog(el);
      el.dataset.ntDialogInit = "1";

      return new Promise(function (resolve) {
        el.addEventListener("close", function () {
          var value = el.returnValue;
          // Let the close animation finish before the node goes.
          window.setTimeout(
            function () {
              el.remove();
            },
            reducedMotion() ? 0 : 220,
          );
          resolve(value);
        });
        instance.open();
      });
    }

    /** Themed window.alert. Resolves when the visitor acknowledges it. */
    alert(data) {
      data =
        typeof data === "string" ? { body: data } : Object.assign({}, data);
      if (!data.size) {
        data.size = "sm";
      }
      if (!data.actions) {
        data.actions = [
          {
            label: data.okLabel || t("ok", "OK"),
            style: "primary",
            value: "ok",
          },
        ];
      }
      return this.show(data).then(function () {
        return undefined;
      });
    }

    /** Themed window.confirm. Resolves true only if the visitor agreed. */
    confirm(data) {
      data =
        typeof data === "string" ? { body: data } : Object.assign({}, data);
      if (!data.tone) {
        data.tone = "question";
      }
      if (!data.size) {
        data.size = "sm";
      }
      if (!data.actions) {
        data.actions = [
          {
            label: data.cancelLabel || t("cancel", "Cancel"),
            style: "ghost",
            value: "cancel",
          },
          {
            label: data.okLabel || t("confirm", "Confirm"),
            style: data.danger ? "danger" : "primary",
            value: "ok",
          },
        ];
      }
      return this.show(data).then(function (value) {
        return value === "ok";
      });
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTConfirmTriggers - declarative confirmation, no per-page JS.
	   Put data-nt-confirm="Are you sure?" on any link, button or form.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTConfirmTriggers {
    constructor(dialogs) {
      this.dialogs = dialogs;
    }
    scan() {
      var self = this;
      document.querySelectorAll("[data-nt-confirm]").forEach(function (node) {
        if (node.dataset.ntConfirmBound) {
          return;
        }
        node.dataset.ntConfirmBound = "1";

        var eventName = node.tagName === "FORM" ? "submit" : "click";
        node.addEventListener(eventName, function (e) {
          if (node.dataset.ntConfirmed === "1") {
            node.dataset.ntConfirmed = "";
            return; // second pass - let it through
          }
          e.preventDefault();
          self.dialogs
            .confirm({
              title: node.getAttribute("data-nt-confirm-title") || "",
              body: node.getAttribute("data-nt-confirm"),
              danger: node.hasAttribute("data-nt-confirm-danger"),
              okLabel: node.getAttribute("data-nt-confirm-ok") || "",
            })
            .then(function (yes) {
              if (!yes) {
                return;
              }
              node.dataset.ntConfirmed = "1";
              if (node.tagName === "FORM") {
                node.submit();
              } else {
                node.click();
              }
            });
        });
      });
      return this;
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTToaster - brief confirmations that don't interrupt.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTToaster {
    constructor() {
      this.host = null;
    }
    container() {
      if (this.host && document.contains(this.host)) {
        return this.host;
      }
      this.host = document.createElement("div");
      this.host.className = "app-toasts";
      this.host.setAttribute("role", "status");
      this.host.setAttribute("aria-live", "polite");
      this.host.setAttribute("aria-label", ARIA.toasts || "");
      document.body.appendChild(this.host);
      return this.host;
    }

    /**
     * @param {string|object} message Text, or { message, tone, duration }.
     * @param {string} [tone]
     * @param {number} [duration] ms
     */
    show(message, tone, duration) {
      if (message && typeof message === "object") {
        tone = message.tone;
        duration = message.duration;
        message = message.message || message.body || "";
      }
      if (!message) {
        return null;
      }
      tone = TONES.indexOf(tone) !== -1 ? tone : "success";

      var node = document.createElement("div");
      node.className = "app-toast app-toast--" + tone;

      if (ICONS[tone]) {
        var icon = document.createElement("span");
        icon.className = "app-toast__icon";
        icon.setAttribute("aria-hidden", "true");
        icon.innerHTML = ICONS[tone]; // trusted: PHP-generated SVG
        node.appendChild(icon);
      }

      var text = document.createElement("span");
      text.className = "app-toast__text";
      text.textContent = message; // textContent = XSS-safe
      node.appendChild(text);

      this.container().appendChild(node);
      window.requestAnimationFrame(function () {
        node.classList.add("is-in");
      });

      var life = duration || DEFAULTS.toastDuration || 5000;
      var timer = window.setTimeout(dismiss, life);

      function dismiss() {
        window.clearTimeout(timer);
        node.classList.remove("is-in");
        window.setTimeout(
          function () {
            node.remove();
          },
          reducedMotion() ? 0 : 320,
        );
      }
      node.addEventListener("click", dismiss);

      return node;
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTAlerts - inline alert boxes: render from data, dismiss, remember.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTAlerts {
    constructor(store) {
      this.store = store;
    }

    /**
     * Build an inline alert from data - the same shape PHP's NT_Alert takes.
     *
     *   NT.alerts.render(form, { tone: 'error', body: '…' });
     *
     * @param {Element|string} target Element or selector to render into.
     * @param {object} data  { tone, title, body, link_label, link_url,
     *                         dismissible, compact, dismiss_id, class }
     * @param {string} [position] 'prepend' (default) | 'append' | 'replace'
     * @returns {HTMLElement|null}
     */
    render(target, data, position) {
      var host =
        typeof target === "string" ? document.querySelector(target) : target;
      if (!host) {
        return null;
      }

      data = typeof data === "string" ? { body: data } : data || {};
      var tone = toneOrDefault(data.tone);

      var box = document.createElement("div");
      box.className = [
        "app-alert",
        "app-alert--" + tone,
        data.compact ? "app-alert--compact" : "",
        data.class || "",
      ]
        .filter(Boolean)
        .join(" ");
      box.setAttribute(
        "role",
        tone === "error" || tone === "warning" ? "alert" : "note",
      );
      if (data.dismiss_id) {
        box.setAttribute("data-nt-alert-remember", data.dismiss_id);
      }

      if (ICONS[tone]) {
        var icon = document.createElement("span");
        icon.className = "app-alert__icon";
        icon.setAttribute("aria-hidden", "true");
        icon.innerHTML = ICONS[tone]; // trusted: PHP-generated SVG
        box.appendChild(icon);
      }

      var content = document.createElement("div");
      content.className = "app-alert__content";

      if (data.title) {
        var title = document.createElement("p");
        title.className = "app-alert__title";
        title.textContent = data.title;
        content.appendChild(title);
      }
      if (data.body || data.message) {
        var text = document.createElement("p");
        text.className = "app-alert__text";
        text.textContent = data.body || data.message; // textContent = XSS-safe
        content.appendChild(text);
      }
      if (data.link_label && data.link_url) {
        var link = document.createElement("a");
        link.className = "app-alert__link";
        link.href = data.link_url;
        link.textContent = data.link_label;
        content.appendChild(link);
      }
      box.appendChild(content);

      if (data.dismissible) {
        var close = document.createElement("button");
        close.type = "button";
        close.className = "app-alert__close";
        close.setAttribute("data-nt-alert-close", "");
        close.setAttribute("aria-label", t("dismiss", "Dismiss"));
        if (ICONS.close) {
          close.innerHTML = ICONS.close;
        }
        box.appendChild(close);
      }

      if (position === "replace") {
        host.innerHTML = "";
        host.appendChild(box);
      } else if (position === "append") {
        host.appendChild(box);
      } else {
        host.insertBefore(box, host.firstChild);
      }

      this.bindOne(box);
      return box;
    }

    bindOne(box) {
      var self = this;
      var remember = box.getAttribute("data-nt-alert-remember");
      if (remember && this.store.has(remember)) {
        box.remove();
        return;
      }

      var btn = box.querySelector("[data-nt-alert-close]");
      if (!btn || btn.dataset.ntAlertBound) {
        return;
      }
      btn.dataset.ntAlertBound = "1";
      btn.addEventListener("click", function () {
        if (remember) {
          self.store.add(remember);
        }
        NTAlerts.collapse(box);
      });
    }

    scan() {
      var self = this;
      document.querySelectorAll(".app-alert").forEach(function (box) {
        self.bindOne(box);
      });
      return this;
    }

    /** Shared close animation, also used by the notice bar. */
    static collapse(node, onDone) {
      if (reducedMotion()) {
        node.remove();
        if (onDone) {
          onDone();
        }
        return;
      }
      node.style.height = node.offsetHeight + "px";
      window.requestAnimationFrame(function () {
        node.classList.add("is-closing");
        node.style.height = "0px";
      });
      window.setTimeout(function () {
        node.remove();
        if (onDone) {
          onDone();
        }
      }, 320);
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTNotices - the announcement strip under the header.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTNotices {
    /**
     * @param {NTStore} store   Where dismissals are remembered.
     * @param {Array}   notices The notices PHP decided apply to this page
     *                          today (NT_Alert::notices()).
     * @param {string}  mount   Comma-separated selectors; the strip is
     *                          inserted after the first one that exists.
     */
    constructor(store, notices, mount) {
      this.store = store;
      this.notices = notices || [];
      this.mount = mount || "header";
    }

    /** Build the strip, skipping anything this visitor already closed. */
    render() {
      var self = this;
      var live = this.notices.filter(function (n) {
        return !self.store.has(n.id);
      });
      if (!live.length) {
        return null;
      }

      var host = document.createElement("div");
      host.className = "app-noticebar";
      host.setAttribute("role", "region");
      host.setAttribute("aria-label", ARIA.notice || "");

      live.forEach(function (notice) {
        host.appendChild(self.item(notice));
      });

      var anchor = null;
      this.mount.split(",").some(function (selector) {
        anchor = document.querySelector(selector.trim());
        return !!anchor;
      });

      if (anchor && anchor.parentNode) {
        anchor.parentNode.insertBefore(host, anchor.nextSibling);
      } else {
        document.body.insertBefore(host, document.body.firstChild);
      }
      return host;
    }

    item(notice) {
      var self = this;

      var wrap = document.createElement("div");
      wrap.className =
        "app-noticebar__item app-noticebar__item--" +
        toneOrDefault(notice.tone);
      wrap.setAttribute("data-nt-notice", notice.id);

      var inner = document.createElement("div");
      inner.className = "app-noticebar__inner";

      var iconSvg = ICONS[notice.icon] || ICONS[notice.tone];
      if (iconSvg) {
        var icon = document.createElement("span");
        icon.className = "app-noticebar__icon";
        icon.setAttribute("aria-hidden", "true");
        icon.innerHTML = iconSvg; // trusted: PHP-generated SVG
        inner.appendChild(icon);
      }

      if (notice.badge) {
        var badge = document.createElement("span");
        badge.className = "app-noticebar__badge";
        badge.textContent = notice.badge;
        inner.appendChild(badge);
      }

      var text = document.createElement("p");
      text.className = "app-noticebar__text";
      if (notice.title) {
        var strong = document.createElement("strong");
        strong.textContent = notice.title + " ";
        text.appendChild(strong);
      }
      text.appendChild(document.createTextNode(notice.message)); // XSS-safe
      inner.appendChild(text);

      if (notice.button_label) {
        var cta;
        if (notice.dialog) {
          cta = document.createElement("button");
          cta.type = "button";
          cta.setAttribute(
            "data-nt-dialog-open",
            DIALOG_PREFIX + notice.dialog,
          );
          cta.setAttribute("aria-haspopup", "dialog");
        } else if (notice.button_url) {
          cta = document.createElement("a");
          cta.href = notice.button_url;
        }
        if (cta) {
          cta.className = "app-noticebar__cta";
          cta.textContent = notice.button_label;
          inner.appendChild(cta);
        }
      }

      if (notice.dismissible) {
        var close = document.createElement("button");
        close.type = "button";
        close.className = "app-noticebar__close";
        close.setAttribute("aria-label", t("dismiss", "Dismiss"));
        if (ICONS.close) {
          close.innerHTML = ICONS.close;
        }
        close.addEventListener("click", function () {
          self.store.add(notice.id);
          NTAlerts.collapse(wrap);
        });
        inner.appendChild(close);
      }

      wrap.appendChild(inner);
      window.requestAnimationFrame(function () {
        wrap.classList.add("is-ready");
      });
      return wrap;
    }

    scan() {
      if (this.rendered) {
        return this;
      }
      this.rendered = true;
      this.render();
      return this;
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTTabs - components/tabs.php.
	   With JS off every panel stays visible and stacked, so content is never
	   hidden behind a script.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTTabs {
    constructor(scope) {
      this.scope = scope;
      this.tabs = Array.prototype.slice.call(
        scope.querySelectorAll("[data-nt-tab]"),
      );
      this.panels = Array.prototype.slice.call(
        scope.querySelectorAll("[data-nt-tab-panel]"),
      );
    }

    init() {
      if (!this.tabs.length || !this.panels.length) {
        return this;
      }
      var self = this;
      this.scope.classList.add("is-enhanced");

      this.tabs.forEach(function (tab, i) {
        tab.addEventListener("click", function () {
          self.select(tab.getAttribute("data-nt-tab"));
        });
        tab.addEventListener("keydown", function (e) {
          var dir = e.key === "ArrowRight" ? 1 : e.key === "ArrowLeft" ? -1 : 0;
          if (!dir) {
            return;
          }
          e.preventDefault();
          var next = self.tabs[(i + dir + self.tabs.length) % self.tabs.length];
          self.select(next.getAttribute("data-nt-tab"), true);
        });
      });

      var initial =
        this.scope.querySelector("[data-nt-tab].is-active") || this.tabs[0];
      this.select(initial.getAttribute("data-nt-tab"));
      return this;
    }

    select(key, focus) {
      this.tabs.forEach(function (tab) {
        var on = tab.getAttribute("data-nt-tab") === key;
        tab.classList.toggle("is-active", on);
        tab.setAttribute("aria-selected", on ? "true" : "false");
        tab.setAttribute("tabindex", on ? "0" : "-1");
        if (on && focus) {
          tab.focus();
        }
      });
      this.panels.forEach(function (panel) {
        var on = panel.getAttribute("data-nt-tab-panel") === key;
        panel.classList.toggle("is-active", on);
        panel.hidden = !on;
      });
      return this;
    }

    static scan() {
      document.querySelectorAll("[data-nt-tabs]").forEach(function (scope) {
        if (scope.dataset.ntTabsBound) {
          return;
        }
        scope.dataset.ntTabsBound = "1";
        new NTTabs(scope).init();
      });
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTPaperStory - components/paper-story.php.
	   Aged sheets stacked on a desk; the top one lifts away to reveal the
	   next. Click, keyboard, swipe or the arrows turn the page. With JS off
	   the sheets simply read top to bottom.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTPaperStory {
    constructor(root) {
      this.root = root;
      this.sheets = Array.prototype.slice.call(
        root.querySelectorAll("[data-nt-story-sheet]"),
      );
      this.dots = Array.prototype.slice.call(
        root.querySelectorAll("[data-nt-story-dot]"),
      );
      this.prevBtn = root.querySelector("[data-nt-story-prev]");
      this.nextBtn = root.querySelector("[data-nt-story-next]");
      this.counter = root.querySelector("[data-nt-story-counter]");
      this.index = 0;
      this.timer = null;
    }

    init() {
      if (this.sheets.length < 2) {
        return this;
      }
      var self = this;
      this.root.classList.add("is-enhanced");

      if (this.prevBtn) {
        this.prevBtn.addEventListener("click", function () {
          self.stop();
          self.go(self.index - 1);
        });
      }
      if (this.nextBtn) {
        this.nextBtn.addEventListener("click", function () {
          self.stop();
          self.go(self.index + 1);
        });
      }

      this.dots.forEach(function (dot, i) {
        dot.addEventListener("click", function () {
          self.stop();
          self.go(i);
        });
      });

      // Clicking the top sheet turns it, like a real stack of papers.
      this.sheets.forEach(function (sheet, i) {
        sheet.addEventListener("click", function (e) {
          if (e.target.closest("a, button")) {
            return;
          }
          if (i !== self.index) {
            return;
          }
          self.stop();
          self.go(self.index === self.sheets.length - 1 ? 0 : self.index + 1);
        });
      });

      this.root.addEventListener("keydown", function (e) {
        if (e.key === "ArrowRight" || e.key === "ArrowDown") {
          e.preventDefault();
          self.stop();
          self.go(self.index + 1);
        }
        if (e.key === "ArrowLeft" || e.key === "ArrowUp") {
          e.preventDefault();
          self.stop();
          self.go(self.index - 1);
        }
      });

      this.bindSwipe();
      this.startAutoplay();
      this.paint();
      return this;
    }

    bindSwipe() {
      var self = this,
        startX = 0,
        startY = 0;
      this.root.addEventListener(
        "touchstart",
        function (e) {
          startX = e.touches[0].clientX;
          startY = e.touches[0].clientY;
        },
        { passive: true },
      );
      this.root.addEventListener("touchend", function (e) {
        var dx = e.changedTouches[0].clientX - startX;
        var dy = e.changedTouches[0].clientY - startY;
        if (Math.abs(dx) < 45 || Math.abs(dx) < Math.abs(dy)) {
          return;
        }
        self.stop();
        self.go(dx < 0 ? self.index + 1 : self.index - 1);
      });
    }

    startAutoplay() {
      var ms =
        parseInt(this.root.getAttribute("data-nt-story-autoplay"), 10) || 0;
      if (!(ms > 0) || reducedMotion()) {
        return;
      }
      var self = this;
      this.timer = window.setInterval(function () {
        self.go(self.index === self.sheets.length - 1 ? 0 : self.index + 1);
      }, ms);
      this.root.addEventListener("mouseenter", function () {
        self.stop();
      });
    }

    stop() {
      if (this.timer) {
        window.clearInterval(this.timer);
        this.timer = null;
      }
    }

    go(n) {
      this.index = Math.max(0, Math.min(this.sheets.length - 1, n));
      this.paint();
      return this;
    }

    paint() {
      var self = this;
      this.sheets.forEach(function (sheet, i) {
        var offset = i - self.index;
        sheet.classList.toggle("is-current", offset === 0);
        sheet.classList.toggle("is-done", offset < 0);
        sheet.classList.toggle("is-waiting", offset > 0);
        // Depth: only the next few sheets peek out from under the top one.
        sheet.style.setProperty(
          "--app-sheet-offset",
          String(Math.max(offset, -1)),
        );
        sheet.setAttribute("aria-hidden", offset === 0 ? "false" : "true");
        sheet.style.zIndex = String(self.sheets.length - Math.abs(offset));
      });
      this.dots.forEach(function (dot, i) {
        dot.classList.toggle("is-active", i === self.index);
        dot.setAttribute("aria-current", i === self.index ? "true" : "false");
      });
      if (this.prevBtn) {
        this.prevBtn.disabled = this.index === 0;
      }
      if (this.nextBtn) {
        this.nextBtn.disabled = this.index === this.sheets.length - 1;
      }
      if (this.counter) {
        this.counter.textContent = t("page_of", "%1$s / %2$s")
          .replace("%1$s", String(this.index + 1))
          .replace("%2$s", String(this.sheets.length));
      }
    }

    static scan() {
      document.querySelectorAll("[data-nt-story]").forEach(function (root) {
        if (root.dataset.ntStoryBound) {
          return;
        }
        root.dataset.ntStoryBound = "1";
        new NTPaperStory(root).init();
      });
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTLeafDrift - the airy layer behind inner-page headings.
	   The markup and the leaf count come from PHP (parts/leaf-drift.php
	   reading admin/data/decor.json); this only randomises each leaf's path
	   so no two page loads drift identically.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTLeafDrift {
    constructor(field) {
      this.field = field;
    }

    init() {
      var ceiling =
        parseFloat(
          getComputedStyle(this.field).getPropertyValue("--app-leaf-ceiling"),
        ) || 0.34;
      var rand = function (min, max) {
        return min + Math.random() * (max - min);
      };

      Array.prototype.forEach.call(
        this.field.querySelectorAll(".app-leaf"),
        function (leaf, i) {
          leaf.style.setProperty(
            "--app-leaf-x",
            rand(-4, 104).toFixed(2) + "%",
          );
          leaf.style.setProperty(
            "--app-leaf-delay",
            (-rand(0, 26)).toFixed(2) + "s",
          );
          leaf.style.setProperty(
            "--app-leaf-duration",
            rand(16, 34).toFixed(2) + "s",
          );
          leaf.style.setProperty(
            "--app-leaf-drift",
            rand(-90, 90).toFixed(0) + "px",
          );
          leaf.style.setProperty(
            "--app-leaf-spin",
            rand(-220, 220).toFixed(0) + "deg",
          );
          leaf.style.setProperty(
            "--app-leaf-scale",
            rand(0.55, 1.25).toFixed(2),
          );
          leaf.style.setProperty(
            "--app-leaf-fade",
            rand(ceiling * 0.45, ceiling).toFixed(2),
          );
          leaf.style.setProperty(
            "--app-leaf-sway",
            rand(3.5, 7.5).toFixed(2) + "s",
          );
          leaf.style.setProperty("--app-leaf-i", String(i));
        },
      );

      this.field.classList.add("is-drifting");
      return this;
    }

    static scan() {
      var fields = document.querySelectorAll("[data-nt-leaves]");
      if (!fields.length) {
        return;
      }

      // Motion is the whole point of this layer - with it switched off
      // there is nothing worth keeping, so drop the nodes entirely.
      if (reducedMotion()) {
        fields.forEach(function (field) {
          field.remove();
        });
        return;
      }
      fields.forEach(function (field) {
        if (field.dataset.ntLeavesBound) {
          return;
        }
        field.dataset.ntLeavesBound = "1";
        new NTLeafDrift(field).init();
      });
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTOverlayEscape - get overlays out of the page's stacking context.

	   THE BUG THIS FIXES: the order/franchise/event wizard modals are printed
	   INSIDE the section that owns their button. Several ancestors on this
	   theme carry `position: relative; z-index: 1` (the content lift above the
	   background vignette). A z-index only competes with its siblings, so the
	   modal's z-index:2000 was being judged inside a context whose own z-index
	   is 1 - and the fixed header (z-index:500) sat on top of the open modal,
	   clipping its title.

	   Moving the element to be a direct child of <body> puts it back in the
	   root stacking context, where its 2000 means what it says. Done once, on
	   load, before anything can open.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTOverlayEscape {
    static scan() {
      // Every overlay kind in the theme: the wizard modals, the simple
      // modals, and any dialog a component rendered inline.
      var selector = ".app-bk-modal, .app-modal, dialog.app-dialog";

      document.querySelectorAll(selector).forEach(function (node) {
        if (node.dataset.ntEscaped) {
          return;
        }
        node.dataset.ntEscaped = "1";
        if (node.parentNode === document.body) {
          return;
        }
        document.body.appendChild(node);
      });
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTSideDock - the floating toolbar gets out of the way on a phone.

	   On a narrow screen the fixed side buttons sit over the reading column.
	   They slide off to the right while the visitor is scrolling DOWN (they
	   are reading, not looking for a button) and come back the instant they
	   stop or scroll up. A sliver stays visible so the dock can always be
	   pulled back by hand.

	   Desktop is untouched - there is room for it there.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTSideDock {
    constructor(el) {
      this.el = el;
      this.lastY = window.pageYOffset || 0;
      this.idle = null;
      this.ticking = false;
    }

    init() {
      var self = this;

      // The dock only tucks where it is actually in the way.
      this.mq = window.matchMedia("(max-width: 767px)");

      window.addEventListener(
        "scroll",
        function () {
          if (self.ticking) {
            return;
          }
          self.ticking = true;
          window.requestAnimationFrame(function () {
            self.onScroll();
          });
        },
        { passive: true },
      );

      // Touching it always brings it back, however it got tucked.
      this.el.addEventListener(
        "touchstart",
        function () {
          self.show();
        },
        { passive: true },
      );
      this.el.addEventListener("mouseenter", function () {
        self.show();
      });

      return this;
    }

    onScroll() {
      this.ticking = false;
      if (!this.mq.matches) {
        this.show();
        return;
      }

      var y = window.pageYOffset || 0;
      var goingDown = y > this.lastY + 6;
      var goingUp = y < this.lastY - 6;
      this.lastY = y;

      if (goingDown && y > 220) {
        this.tuck();
      } else if (goingUp) {
        this.show();
      }

      // Stopping counts as "I might want a button now".
      var self = this;
      window.clearTimeout(this.idle);
      this.idle = window.setTimeout(function () {
        self.show();
      }, 900);
    }

    tuck() {
      this.el.classList.add("is-tucked");
    }
    show() {
      this.el.classList.remove("is-tucked");
    }

    static scan() {
      document.querySelectorAll(".app-floating-toolbar").forEach(function (el) {
        if (el.dataset.ntDockBound) {
          return;
        }
        el.dataset.ntDockBound = "1";
        new NTSideDock(el).init();
      });
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTMobileNav - the drawer's dropdowns.

	   The drawer printed every submenu open, all the time. With three parents
	   carrying three or four children each, the nav was several screens long
	   and the top-level items - the ones most people want - were scattered
	   between them instead of being a short list.

	   Each parent now gets a real toggle button beside its link, so the link
	   still navigates and the arrow still opens the submenu. Only one branch
	   is open at a time. The markup is untouched: the button is added here,
	   which keeps parts/main_header.php the same shape for the desktop nav
	   that shares it.

	   Opening or closing the drawer is still legacy.js's job - this only
	   deals with what is inside it.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTMobileNav {
    constructor(nav) {
      this.nav = nav;
      this.groups = [];
    }

    init() {
      var self = this;

      Array.prototype.forEach.call(
        this.nav.querySelectorAll(".app-nav__has-sub"),
        function (item, i) {
          var link = item.querySelector(".app-mobile-nav__link");
          var sub = item.querySelector(".app-mobile-nav__submenu");
          if (!link || !sub) {
            return;
          }

          var id = "app-mnav-sub-" + i;
          sub.id = id;

          // Wrap the links in ONE element. The collapse is done with
          // grid-template-rows: 0fr -> 1fr, which only sizes the rows
          // the template declares - with the links as direct children
          // the grid auto-creates a row per link and every one of
          // those stays at its natural height, so the "closed"
          // submenu still took up its full space. One child, one row,
          // one thing to collapse.
          if (!sub.querySelector(".app-mobile-nav__subinner")) {
            var inner = document.createElement("div");
            inner.className = "app-mobile-nav__subinner";
            while (sub.firstChild) {
              inner.appendChild(sub.firstChild);
            }
            sub.appendChild(inner);
          }

          var toggle = document.createElement("button");
          toggle.type = "button";
          toggle.className = "app-mobile-nav__toggle";
          toggle.setAttribute("aria-expanded", "false");
          toggle.setAttribute("aria-controls", id);
          // The parent's own name, so the control is not just "button".
          toggle.setAttribute("aria-label", link.textContent.trim());
          if (ICONS["chevron-down"]) {
            toggle.innerHTML = ICONS["chevron-down"];
          }

          // A row holding the link and its toggle side by side.
          var row = document.createElement("div");
          row.className = "app-mobile-nav__row";
          item.insertBefore(row, link);
          row.appendChild(link);
          row.appendChild(toggle);

          var group = { item: item, sub: sub, toggle: toggle };
          self.groups.push(group);

          toggle.addEventListener("click", function () {
            var open = item.classList.contains("is-open");
            self.closeAll();
            if (!open) {
              self.open(group);
            }
          });
        },
      );

      return this;
    }

    open(group) {
      group.item.classList.add("is-open");
      group.toggle.setAttribute("aria-expanded", "true");
    }

    closeAll() {
      this.groups.forEach(function (g) {
        g.item.classList.remove("is-open");
        g.toggle.setAttribute("aria-expanded", "false");
      });
    }

    static scan() {
      document.querySelectorAll(".app-mobile-nav").forEach(function (nav) {
        if (nav.dataset.ntMnavBound) {
          return;
        }
        nav.dataset.ntMnavBound = "1";
        new NTMobileNav(nav).init();
      });
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTShare - the device's own share sheet, where there is one.

	   No third-party share buttons: those load a script per network and set a
	   cookie for each, on every page view, whether or not anyone shares. The
	   native sheet costs nothing and offers every app the reader actually
	   has. Where it does not exist the button stays hidden and the
	   copy-link button beside it does the job.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTShare {
    static scan() {
      if (!navigator.share) {
        return;
      } // button stays hidden

      document.querySelectorAll("[data-nt-share]").forEach(function (scope) {
        if (scope.dataset.ntShareBound) {
          return;
        }
        scope.dataset.ntShareBound = "1";

        var btn = scope.querySelector("[data-nt-share-native]");
        if (!btn) {
          return;
        }
        btn.hidden = false;

        btn.addEventListener("click", function () {
          navigator
            .share({
              title: scope.getAttribute("data-share-title") || document.title,
              url: scope.getAttribute("data-share-url") || window.location.href,
            })
            .catch(function () {
              /* the reader cancelled - not an error */
            });
        });
      });
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTCopy - data-nt-copy="text" on any button.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTCopy {
    constructor(toaster) {
      this.toaster = toaster;
    }
    scan() {
      var self = this;
      document.querySelectorAll("[data-nt-copy]").forEach(function (btn) {
        if (btn.dataset.ntCopyBound) {
          return;
        }
        btn.dataset.ntCopyBound = "1";
        btn.addEventListener("click", function () {
          var value = btn.getAttribute("data-nt-copy");
          if (!value || !navigator.clipboard) {
            return;
          }
          navigator.clipboard
            .writeText(value)
            .then(function () {
              self.toaster.show(t("copied", ""), "success");
            })
            .catch(function () {
              self.toaster.show(t("error_generic", ""), "error");
            });
        });
      });
      return this;
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
	   NTUiKit - the façade. Boots everything and exposes the public API.
	   ══════════════════════════════════════════════════════════════════════ */

  class NTUiKit {
    constructor() {
      var dismissStore = new NTStore(
        DEFAULTS.noticeStore || "app_dismissed_notices",
      );

      this.dialogs = new NTDialogs(CFG.dialogs);
      this.confirmTriggers = new NTConfirmTriggers(this.dialogs);
      this.toaster = new NTToaster();
      this.alerts = new NTAlerts(dismissStore);
      this.notices = new NTNotices(dismissStore, CFG.notices, CFG.noticeMount);
      this.copy = new NTCopy(this.toaster);
    }

    /** Idempotent - safe to re-run after injecting markup with AJAX. */
    scan() {
      NTOverlayEscape.scan(); // first: get overlays out of low z-index contexts
      this.notices.scan(); // then: the strip sits above everything
      this.dialogs.scan();
      this.confirmTriggers.scan();
      this.alerts.scan();
      this.copy.scan();
      NTTabs.scan();
      NTPaperStory.scan();
      NTLeafDrift.scan();
      NTSideDock.scan();
      NTShare.scan();
      NTMobileNav.scan();

      // A dialog built at runtime can contain a form; let common.js bind
      // its AJAX submit the same way it does for server-rendered ones.
      if (window.NT && typeof window.NT.bindForms === "function") {
        window.NT.bindForms();
      }
      return this;
    }

    /** The flat API page scripts use: NT.alert(), NT.toast(), … */
    api() {
      var self = this;
      return {
        dialog: {
          open: function (id, opener) {
            return self.dialogs.open(id, opener);
          },
          close: function (id, value) {
            return self.dialogs.close(id, value);
          },
          show: function (data) {
            return self.dialogs.show(data);
          },
        },
        alert: function (data) {
          return self.dialogs.alert(data);
        },
        confirm: function (data) {
          return self.dialogs.confirm(data);
        },
        toast: function (message, tone, duration) {
          return self.toaster.show(message, tone, duration);
        },
        alerts: self.alerts,
        refresh: function () {
          return self.scan();
        },
        // The classes themselves, for anything that needs to build on them.
        classes: {
          NTStore: NTStore,
          NTDialog: NTDialog,
          NTDialogView: NTDialogView,
          NTDialogs: NTDialogs,
          NTToaster: NTToaster,
          NTAlerts: NTAlerts,
          NTNotices: NTNotices,
          NTTabs: NTTabs,
          NTPaperStory: NTPaperStory,
          NTLeafDrift: NTLeafDrift,
        },
      };
    }
  }

  var kit = new NTUiKit();

  function boot() {
    kit.scan();
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }

  var api = kit.api();
  window.NTUI = api;
  // Fold into the existing NT namespace (assets/js/common.js) so page
  // scripts can just call NT.toast() / NT.confirm() / NT.dialog.show().
  window.NT = Object.assign(window.NT || {}, api);
})();
