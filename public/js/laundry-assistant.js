(() => {
    'use strict';

    const widget = document.querySelector('[data-laundry-assistant]');
    if (!widget) return;
    const panel = widget.querySelector('dialog');
    const openers = [...document.querySelectorAll('[data-chat-open]')];
    const input = widget.querySelector('[data-chat-input]');
    const form = widget.querySelector('[data-chat-form]');
    const log = widget.querySelector('[data-chat-log]');
    const welcome = widget.querySelector('[data-chat-welcome]');
    const suggestions = widget.querySelector('[data-chat-suggestions]');
    const reset = widget.querySelector('[data-chat-reset]');
    const send = widget.querySelector('[data-chat-send]');
    const counter = widget.querySelector('[data-chat-counter]');
    const status = widget.querySelector('[data-chat-status]');
    let history = [];
    let activeRequest = null;
    let lastOpener;
    let previousOverflow = '';

    const element = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    };
    const scrollToLatest = () => { log.scrollTop = log.scrollHeight; };
    const updateControls = () => {
        send.disabled = Boolean(activeRequest) || !input.value.trim();
        reset.disabled = !log.querySelector('[data-chat-message]');
        panel.querySelectorAll('[data-chat-question], .laundry-chat-retry').forEach(button => button.disabled = Boolean(activeRequest));
        counter.textContent = input.value.length + ' / 1000';
        input.style.height = 'auto';
        input.style.height = Math.min(105, input.scrollHeight) + 'px';
    };
    const fitViewport = () => {
        if (!panel.open || !window.visualViewport) return;
        const visual = window.visualViewport;
        panel.style.setProperty('--chat-visible-height', visual.height + 'px');
        panel.style.setProperty('--chat-keyboard-offset', Math.max(0, innerHeight - visual.height - visual.offsetTop) + 'px');
    };
    const open = trigger => {
        if (panel.open) return;
        lastOpener = trigger;
        previousOverflow = document.body.style.overflow;
        panel.showModal();
        document.body.style.overflow = 'hidden';
        openers.forEach(button => button.setAttribute('aria-expanded', 'true'));
        fitViewport();
        if (matchMedia('(min-width: 576px)').matches) input.focus({ preventScroll: true });
        else panel.querySelector('[data-chat-close]').focus({ preventScroll: true });
        updateControls();
        scrollToLatest();
    };
    const close = () => {
        panel.close();
        document.body.style.overflow = previousOverflow;
        openers.forEach(button => button.setAttribute('aria-expanded', 'false'));
        lastOpener?.focus({ preventScroll: true });
    };
    openers.forEach(button => button.addEventListener('click', () => open(button)));
    panel.querySelector('[data-chat-close]').addEventListener('click', close);
    panel.addEventListener('cancel', event => { event.preventDefault(); close(); });
    panel.addEventListener('click', event => {
        if (event.target !== panel) return;
        const rect = panel.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) close();
    });
    input.addEventListener('input', updateControls);
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            if (!send.disabled) form.requestSubmit();
        }
    });
    window.visualViewport?.addEventListener('resize', fitViewport);
    window.visualViewport?.addEventListener('scroll', fitViewport);
    window.addEventListener('resize', fitViewport);

    const message = (role, text) => {
        const row = element('div', 'laundry-chat-message' + (role === 'user' ? ' is-user' : ''));
        row.dataset.chatMessage = role;
        const meta = element('div', 'laundry-chat-message-meta', role === 'user' ? 'You' : 'Waku');
        const content = element('div', 'laundry-chat-message-content', text);
        row.append(meta, content);
        log.append(row);
        return { row, meta, content };
    };
    const showLinks = (row, links) => {
        const group = element('div', 'laundry-chat-message-links');
        for (const link of Array.isArray(links) ? links.slice(0, 3) : []) {
            if (typeof link.label !== 'string' || typeof link.url !== 'string') continue;
            let url;
            try { url = new URL(link.url, location.origin); } catch { continue; }
            const external = url.origin !== location.origin;
            if (!['https:', 'http:'].includes(url.protocol) || (external && url.hostname !== 'www.cleaninginstitute.org')) continue;
            const anchor = element('a', '', link.label);
            anchor.href = url.href;
            if (external) { anchor.target = '_blank'; anchor.rel = 'noopener noreferrer'; }
            anchor.append(element('i', 'bi bi-arrow-up-right'));
            group.append(anchor);
        }
        if (group.childElementCount) row.append(group);
    };
    const showSuggestions = questions => {
        suggestions.replaceChildren();
        for (const question of Array.isArray(questions) ? questions.slice(0, 3) : []) {
            if (typeof question !== 'string' || question.length > 1000) continue;
            const button = element('button', '', question);
            button.type = 'button';
            button.dataset.chatQuestion = question;
            suggestions.append(button);
        }
        suggestions.hidden = !suggestions.childElementCount;
    };
    const ask = async (question, retryRow = null) => {
        const text = question.trim();
        if (!text || text.length > 1000 || activeRequest) return;
        welcome.hidden = true;
        suggestions.hidden = true;
        if (retryRow) retryRow.remove();
        else {
            message('user', text);
            input.value = '';
        }
        const reply = message('assistant', '');
        reply.row.classList.add('is-thinking');
        reply.content.append(element('span', 'visually-hidden', 'Finding an answer…'));
        const dots = element('span', 'laundry-chat-thinking');
        dots.setAttribute('aria-hidden', 'true');
        for (let index = 0; index < 3; index++) dots.append(element('span'));
        reply.content.append(dots);
        const request = { controller: new AbortController(), timedOut: false };
        activeRequest = request;
        updateControls();
        scrollToLatest();
        const timer = setTimeout(() => { request.timedOut = true; request.controller.abort(); }, 30000);
        try {
            const response = await fetch(widget.dataset.endpoint, {
                method: 'POST', credentials: 'same-origin', cache: 'no-store', signal: request.controller.signal,
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ message: text, history: history.slice(-8) }),
            });
            if (!response.ok) { const error = new Error('Request failed'); error.status = response.status; throw error; }
            const data = await response.json();
            if (typeof data.answer !== 'string' || !data.answer.trim()) throw new Error('Empty reply');
            if (activeRequest !== request) return;
            reply.row.classList.remove('is-thinking');
            reply.meta.textContent = data.source === 'openai' ? 'Waku · AI answer' : 'Waku · Laundryhub guide';
            reply.content.textContent = data.answer;
            showLinks(reply.row, data.links);
            if (typeof data.notice === 'string') reply.row.append(element('p', 'laundry-chat-notice', data.notice));
            showSuggestions(data.suggestions);
            history.push({ role: 'user', content: text }, { role: 'assistant', content: data.answer.slice(0, 4000) });
            history = history.slice(-8);
        } catch (error) {
            if (activeRequest !== request) return;
            reply.row.classList.remove('is-thinking');
            reply.row.classList.add('is-error');
            reply.content.textContent = error.status === 429 ? 'A few too many questions at once. Please wait a minute and try again.'
                : error.status === 419 ? 'Your session has expired. Refresh the page to continue chatting.'
                : request.timedOut ? 'That took too long. Your question is still here; please try again.'
                : 'I could not connect just now. Your question is still here; please try again.';
            const retry = element('button', 'laundry-chat-retry', error.status === 419 ? 'Refresh page' : 'Try again');
            retry.type = 'button';
            retry.addEventListener('click', () => error.status === 419 ? location.reload() : ask(text, reply.row));
            reply.row.append(retry);
        } finally {
            clearTimeout(timer);
            if (activeRequest === request) {
                activeRequest = null;
                updateControls();
                scrollToLatest();
            }
        }
    };
    form.addEventListener('submit', event => { event.preventDefault(); ask(input.value); });
    panel.addEventListener('click', event => {
        const button = event.target.closest('[data-chat-question]');
        if (button && !button.disabled) ask(button.dataset.chatQuestion);
    });
    reset.addEventListener('click', () => {
        const pending = activeRequest;
        activeRequest = null;
        pending?.controller.abort();
        history = [];
        log.querySelectorAll('[data-chat-message]').forEach(row => row.remove());
        welcome.hidden = false;
        showSuggestions([]);
        input.value = '';
        status.textContent = 'New chat started.';
        updateControls();
        input.focus({ preventScroll: true });
        log.scrollTop = 0;
    });

    const bixby = widget.querySelector('[data-chat-bixby]');
    if (bixby && /SamsungBrowser|SAMSUNG|SM-[A-Z0-9]+|GT-[A-Z0-9]+/i.test(navigator.userAgent)) bixby.hidden = false;
    updateControls();
})();
