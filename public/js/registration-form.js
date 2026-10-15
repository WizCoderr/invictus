/**
 * The public registration wizard — one step shown at a time. Continue is the form's
 * submit button, so Enter moves forward too; tapping a card or option moves forward on its own.
 */
(function () {
  'use strict';

  var config = window.REGISTRATION || {};

  var form = document.getElementById('wizard-form');
  if (!form) return;

  var S = {
    post: function (url, payload) {
      return fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload || {})
      }).then(function (response) {
        return response.json()
          .catch(function () { return {}; })
          .then(function (body) { return { ok: response.ok, status: response.status, body: body }; });
      });
    },

    escapeHtml: function (value) {
      return String(value === null || value === undefined ? '' : value)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    },

    // Same grouping as App\Support\Money: ₹1,10,448 — whole rupees unless there are paise.
    inr: function (amount) {
      var rounded = Math.round(amount * 100) / 100;
      return '₹' + rounded.toLocaleString('en-IN', rounded % 1 === 0
        ? { maximumFractionDigits: 0 }
        : { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
  };

  var card = document.getElementById('wizard-card');
  var elError = document.getElementById('wizard-error');
  var elCount = document.getElementById('wizard-count');
  var elBar = document.getElementById('wizard-bar');
  var btnBack = document.getElementById('wizard-back');
  var btnNext = document.getElementById('wizard-next');
  var dots = document.querySelectorAll('#wizard-steps li');

  var search = document.getElementById('reg_category_search');
  var options = document.querySelectorAll('#reg_category_list .reg-option');
  var noMatch = document.getElementById('reg_category_empty');
  var coarse = window.matchMedia('(pointer: coarse)').matches;

  var sections = {};
  form.querySelectorAll('[data-step]').forEach(function (el) {
    sections[el.getAttribute('data-step')] = el;
  });

  var current = 0;
  var order = [];
  var pendingOrder = null;
  var viaPointer = false;

  function type() {
    var picked = form.querySelector('input[name="type"]:checked');
    return picked ? picked.value : null;
  }

  function rebuild() {
    order = ['1', '2', '3', type() === 'visitor' ? '4-visitor' : '4-member', '5'];

    form.querySelectorAll('[data-required-when]').forEach(function (el) {
      el.required = el.getAttribute('data-required-when') === type();
    });
  }

  function show(index) {
    rebuild();
    var target = Math.max(0, Math.min(index, order.length - 1));

    // Lets the CSS slide the step in from the left when going back.
    card.classList.toggle('is-back', target < current);
    current = target;

    Object.keys(sections).forEach(function (key) {
      sections[key].hidden = key !== order[current];
    });

    var step = current + 1;
    elCount.textContent = 'Step ' + step + ' of ' + order.length;
    elBar.style.width = (step / order.length * 100) + '%';

    dots.forEach(function (li) {
      var n = Number(li.getAttribute('data-for'));
      li.classList.toggle('is-done', n < step);
      li.classList.toggle('is-on', n === step);
    });

    btnBack.hidden = current === 0;
    elError.hidden = true;

    if (order[current] === '5') {
      renderReview();
      var total = fee().total_amount;
      btnNext.textContent = total > 0 ? 'Pay ' + S.inr(total) : 'Complete registration';
    } else {
      btnNext.textContent = 'Continue';
    }

    if (card.getBoundingClientRect().top < 0) card.scrollIntoView({ behavior: 'smooth' });

    var section = sections[order[current]];
    var first = section.querySelector('input:checked') || section.querySelector('input');

    // On a phone, don't throw the keyboard up over a list the visitor may only want to scroll.
    if (first && !(coarse && first === search)) first.focus({ preventScroll: true });
  }

  function message(field) {
    var v = field.validity;
    if (v.valueMissing && field.dataset.missing) return field.dataset.missing;
    if (v.valueMissing) return field.type === 'radio' ? 'Please pick one to continue.' : 'This field is required.';
    if (v.typeMismatch && field.type === 'email') return 'Enter a valid email address, like you@company.com.';
    return field.validationMessage;
  }

  // Restarts the shake even if the last one hasn't finished.
  function shake(el) {
    el.classList.remove('is-shaking');
    void el.offsetWidth;
    el.classList.add('is-shaking');
  }

  // Marked by hand rather than :user-invalid — a submit attempt flags every field in the
  // form as touched, so steps not yet reached would arrive already red.
  function currentValid() {
    var fields = sections[order[current]].querySelectorAll('input');

    for (var i = 0; i < fields.length; i++) {
      var field = fields[i];
      if (field.checkValidity()) continue;

      var wrap = field.closest('.reg-field');
      var slot = wrap && wrap.querySelector('.reg-err');

      // Said inline under the field rather than in the browser's bubble.
      if (slot) {
        slot.textContent = message(field);
        slot.hidden = false;
      }

      if (field.type === 'radio') {
        shake(field.closest('.reg-list, .reg-tiles, .reg-choices') || wrap || field);
        if (!slot) field.reportValidity();
      } else {
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
        if (!slot) field.reportValidity();
        shake(field.closest('.reg-input') || field);
        field.focus();
      }

      return false;
    }

    return true;
  }

  function fee() {
    return (config.fees || {})[type()] || { amount: 0, gst_percent: 0, gst_amount: 0, total_amount: 0 };
  }

  function picked(name) {
    var input = form.querySelector('input[name="' + name + '"]:checked');
    return input ? input.parentNode.textContent.trim() : '';
  }

  function renderReview() {
    var rows = [
      ['Name', form.elements.name.value.trim(), '1'],
      ['Attending as', type() === 'visitor' ? 'Visitor' : 'Member', '2'],
      ['Business', picked('business_category_id'), '3']
    ];

    if (type() === 'visitor') {
      rows.push(['Mobile', form.elements.phone.value.trim(), '4-visitor']);
      rows.push(['Email', form.elements.email.value.trim(), '4-visitor']);
      rows.push(['Invited by', form.elements.invited_by.value.trim(), '4-visitor']);
    } else {
      rows.push(['Chapter', picked('chapter_id'), '4-member']);
    }

    document.getElementById('wizard-summary').innerHTML = rows.map(function (row) {
      return '<div><dt>' + S.escapeHtml(row[0]) + '</dt><dd>' + S.escapeHtml(row[1] || '—') + '</dd>'
        + '<button type="button" class="reg-edit" data-goto="' + row[2] + '">Edit</button></div>';
    }).join('');

    var f = fee();
    var bill = document.getElementById('wizard-fee');
    var paid = f.total_amount > 0;

    bill.hidden = !paid;
    document.getElementById('wizard-secure-note').hidden = !paid;

    bill.innerHTML = !paid ? '' : line('Registration fee', S.inr(f.amount))
      + line('GST @ ' + f.gst_percent + '%', S.inr(f.gst_amount))
      + line('Total', S.inr(f.total_amount), 'is-total');
  }

  function line(label, value, cls) {
    return '<div class="' + (cls || '') + '"><span>' + S.escapeHtml(label) + '</span><span>' + S.escapeHtml(value) + '</span></div>';
  }

  function payload() {
    var out = {};
    new FormData(form).forEach(function (value, key) { out[key] = value; });
    return out;
  }

  function showErrors(errors) {
    var first = null;

    Object.keys(errors).forEach(function (name) {
      var field = form.querySelector('[name="' + name + '"]');
      var wrap = field && field.closest('.reg-field');
      if (!wrap) return;

      var slot = wrap.querySelector('.reg-err');
      slot.textContent = errors[name][0];
      slot.hidden = false;
      field.classList.add('is-invalid');

      if (!first) first = field;
    });

    if (first) show(order.indexOf(first.closest('[data-step]').getAttribute('data-step')));

    return first;
  }

  function showError(message) {
    elError.textContent = message;
    elError.hidden = false;
  }

  if (search) {
    search.addEventListener('input', function () {
      var term = search.value.trim().toLowerCase();
      var shown = 0;

      options.forEach(function (option) {
        var match = option.textContent.toLowerCase().indexOf(term) !== -1;

        // The current pick stays in view whatever is typed.
        option.hidden = !match && !option.querySelector('input').checked;
        if (match) shown++;
      });

      noMatch.hidden = shown > 0;
    });
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (btnNext.disabled) return; // already working on it
    if (!currentValid()) return;

    if (order[current] === '5') submit();
    else show(current + 1);
  });

  form.addEventListener('animationend', function (event) {
    event.target.classList.remove('is-shaking');
  });

  form.addEventListener('pointerdown', function () { viaPointer = true; });

  form.addEventListener('keydown', function (event) {
    viaPointer = false;
    if (event.key !== 'Enter') return;

    var target = event.target;

    // Enter in the search picks the only match and moves on; otherwise it just closes the keyboard.
    if (target === search) {
      event.preventDefault();

      var visible = Array.prototype.filter.call(options, function (option) { return !option.hidden; });

      if (visible.length === 1) {
        visible[0].querySelector('input').click();
        form.requestSubmit();
      } else {
        search.blur();
      }

      return;
    }

    // Enter — the phone keyboard's Next — moves to the step's next box; on the last one it continues.
    if (target.matches('input:not([type=radio])')) {
      var boxes = Array.prototype.slice.call(sections[order[current]].querySelectorAll('input:not([type=radio])'));
      var next = boxes[boxes.indexOf(target) + 1];

      if (next) {
        event.preventDefault();
        next.focus();
      }
    }
  });

  form.addEventListener('change', function (event) {
    var input = event.target;
    if (input.type !== 'radio') return;

    if (input.name === 'type') pendingOrder = null;
    rebuild();

    // Arrow keys move through a radio group by selecting each option in turn, so only a
    // tap or click moves on — otherwise the keyboard could never reach the later options.
    if (!viaPointer) return;

    var at = current;
    setTimeout(function () { if (current === at && currentValid()) show(current + 1); }, 220);
  });

  form.addEventListener('input', function (event) {
    event.target.classList.remove('is-invalid');
    event.target.removeAttribute('aria-invalid');

    var wrap = event.target.closest('.reg-field');
    var slot = wrap && wrap.querySelector('.reg-err');
    if (slot) slot.hidden = true;
  });

  form.addEventListener('click', function (event) {
    var edit = event.target.closest('[data-goto]');
    if (!edit) return;

    pendingOrder = null; // details may change — a stale order would charge the old amount
    show(order.indexOf(edit.getAttribute('data-goto')));
  });

  btnBack.addEventListener('click', function () {
    pendingOrder = null;
    show(current - 1);
  });

  function submit() {
    if (pendingOrder) { openCheckout(pendingOrder); return; }

    var original = btnNext.textContent;
    busy('Processing…');
    elError.hidden = true;

    S.post(config.urls.store, payload()).then(function (result) {
      busy(false, original);

      if (!result.ok) {
        if (!showErrors(result.body.errors || {})) {
          showError(result.body.message || 'That did not go through — please try again.');
        }
        return;
      }

      if (result.body.redirect) { window.location.href = result.body.redirect; return; }

      pendingOrder = result.body.order;
      openCheckout(pendingOrder);
    }).catch(function () {
      busy(false, original);
      showError('Network error — please check your connection and try again.');
    });
  }

  function openCheckout(order) {
    if (!window.Razorpay) {
      showError('Payment could not start — please reload the page and try again.');
      return;
    }

    var settled = false;

    new window.Razorpay({
      key: order.key,
      order_id: order.id,
      amount: order.amount,
      currency: order.currency,
      name: config.event || 'Registration',
      description: 'Registration fee',
      prefill: {
        name: form.elements.name.value.trim(),
        email: form.elements.email.value.trim(),
        contact: form.elements.phone.value.trim()
      },
      theme: { color: '#cf2030' },
      handler: function (response) { settled = true; confirmPaid(response); },
      modal: {
        ondismiss: function () {
          if (!settled) showError('Payment was not completed. Press Pay to try again.');
        }
      }
    }).open();
  }

  // Spinner and a locked button while a request is out, so it can't be sent twice.
  function busy(label, restore) {
    btnNext.disabled = !!label;
    btnNext.classList.toggle('is-loading', !!label);
    btnNext.setAttribute('aria-busy', label ? 'true' : 'false');
    btnNext.textContent = label || restore;
  }

  function confirmPaid(response) {
    busy('Confirming payment…');

    S.post(config.urls.paid, {
      razorpay_order_id: response.razorpay_order_id,
      razorpay_payment_id: response.razorpay_payment_id,
      razorpay_signature: response.razorpay_signature
    }).then(function (result) {
      if (result.ok && result.body.redirect) { window.location.href = result.body.redirect; return; }
      pollStatus(response.razorpay_order_id, 0);
    }).catch(function () {
      pollStatus(response.razorpay_order_id, 0);
    });
  }

  // Asks the server to check with Razorpay directly, for when /register/paid didn't get through.
  function pollStatus(orderId, tries) {
    showError('Checking your payment with the bank…');

    S.post(config.urls.status, { razorpay_order_id: orderId }).then(function (result) {
      if (result.body && result.body.redirect) { window.location.href = result.body.redirect; return; }
      retry(orderId, tries);
    }).catch(function () {
      retry(orderId, tries);
    });
  }

  function retry(orderId, tries) {
    if (tries < 20) { setTimeout(function () { pollStatus(orderId, tries + 1); }, 3000); return; }

    busy(false, 'Pay now');
    showError('We have not heard back about your payment yet. If money was taken, please contact the organiser.');
  }

  show(0);
})();
