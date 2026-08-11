/**
 * VibeCodeWeb chat widget — floating bubble + panel, talks to chat.php (same origin).
 * Replaces the chatbot app's embed.js: no bot key, no cross-app dependency.
 * Add one line before </body>:  <script src="chat-widget.js" defer></script>
 */
(function () {
  var API   = 'chat.php';
  var STORE = 'vcw_chat_history';
  var history = [];
  try { history = JSON.parse(sessionStorage.getItem(STORE) || '[]') || []; } catch (e) { history = []; }

  var C = '#2563eb';
  function el(tag, css, html) { var n = document.createElement(tag); if (css) n.style.cssText = css; if (html != null) n.innerHTML = html; return n; }

  var btn = el('button',
    'position:fixed;bottom:20px;right:20px;width:56px;height:56px;border:none;border-radius:50%;'
    + 'background:' + C + ';color:#fff;font-size:24px;cursor:pointer;z-index:2147483000;'
    + 'box-shadow:0 6px 20px rgba(0,0,0,.25);', '\u{1F4AC}');

  var panel = el('div',
    'position:fixed;bottom:88px;right:20px;width:340px;max-width:calc(100vw - 40px);height:460px;'
    + 'max-height:calc(100vh - 120px);background:#fff;border-radius:14px;display:none;flex-direction:column;'
    + 'overflow:hidden;z-index:2147483000;box-shadow:0 12px 40px rgba(0,0,0,.28);'
    + 'font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;');

  var head = el('div',
    'background:' + C + ';color:#fff;padding:14px 16px;font-weight:600;font-size:15px;', 'Chat with us');
  var log = el('div',
    'flex:1;overflow-y:auto;padding:14px;background:#f7f8fa;font-size:14px;line-height:1.4;');
  var form = el('form', 'display:flex;border-top:1px solid #e5e7eb;background:#fff;');
  var input = el('input',
    'flex:1;border:none;padding:12px 14px;font-size:14px;outline:none;background:#fff;color:#111;');
  input.type = 'text'; input.placeholder = 'Type a message…';
  var send = el('button',
    'border:none;background:' + C + ';color:#fff;padding:0 18px;font-size:14px;cursor:pointer;', 'Send');
  send.type = 'submit';

  form.appendChild(input); form.appendChild(send);
  panel.appendChild(head); panel.appendChild(log); panel.appendChild(form);
  document.body.appendChild(btn); document.body.appendChild(panel);

  function bubble(text, who) {
    var mine = who === 'user';
    var row = el('div', 'display:flex;margin:6px 0;justify-content:' + (mine ? 'flex-end' : 'flex-start') + ';');
    var b = el('div',
      'max-width:78%;padding:9px 12px;border-radius:14px;white-space:pre-wrap;word-wrap:break-word;'
      + (mine ? 'background:' + C + ';color:#fff;border-bottom-right-radius:4px;'
              : 'background:#fff;color:#111;border:1px solid #e5e7eb;border-bottom-left-radius:4px;'));
    b.textContent = text; row.appendChild(b); log.appendChild(row); log.scrollTop = log.scrollHeight;
    return b;
  }

  var open = false;
  btn.addEventListener('click', function () {
    open = !open; panel.style.display = open ? 'flex' : 'none';
    if (open) {
      input.focus();
      if (!log.childNodes.length) {
        // Replay this tab's earlier conversation, else greet.
        if (history.length) {
          history.forEach(function (m) { bubble(m.content, m.role === 'user' ? 'user' : 'bot'); });
        } else {
          bubble('Hi! How can I help you today?', 'bot');
        }
      }
    }
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var msg = input.value.trim(); if (!msg) return;
    input.value = ''; bubble(msg, 'user');
    var typing = bubble('…', 'bot'); send.disabled = true;
    history.push({ role: 'user', content: msg });

    fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ messages: history })
    })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        send.disabled = false;
        if (j.ok) { history.push({ role: 'assistant', content: j.reply }); }
        else { history.pop(); }
        try { sessionStorage.setItem(STORE, JSON.stringify(history)); } catch (e) {}
        typing.textContent = j.ok ? j.reply : ('⚠️ ' + (j.error || 'Something went wrong.'));
        log.scrollTop = log.scrollHeight;
      })
      .catch(function () { send.disabled = false; history.pop(); typing.textContent = '⚠️ Network error. Please try again.'; });
  });
})();
