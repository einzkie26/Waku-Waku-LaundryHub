<div class="laundry-assistant" data-laundry-assistant data-endpoint="{{ route('assistant.message') }}">
    <button class="laundry-chat-launcher" type="button" data-chat-open aria-haspopup="dialog" aria-controls="laundry-chat-panel" aria-expanded="false">
        <span class="laundry-chat-avatar" aria-hidden="true"><svg viewBox="0 0 40 40" focusable="false"><rect x="7" y="4" width="26" height="32" rx="7"/><path d="M8 12h24"/><circle cx="20" cy="23" r="8"/><path class="chat-avatar-water" d="M13 26q3-3 7 0t7 0"/><path d="M12 8h6m8 0h2"/><circle class="chat-avatar-eye" cx="17" cy="21" r="1"/><circle class="chat-avatar-eye" cx="23" cy="21" r="1"/></svg><i class="bi bi-stars"></i></span>
        <span class="laundry-chat-launcher-copy"><strong>Ask Waku</strong><small>Laundry assistant</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i>
    </button>
    <dialog class="laundry-chat-panel" id="laundry-chat-panel" aria-labelledby="laundry-chat-title" aria-describedby="laundry-chat-description">
        <div class="laundry-chat-shell">
            <header class="laundry-chat-header">
                <span class="laundry-chat-header-logo"><img src="{{ asset('img/logo.png') }}" width="34" height="34" alt=""></span>
                <div><h2 id="laundry-chat-title">Ask Waku <i class="bi bi-stars" aria-hidden="true"></i></h2><p id="laundry-chat-description"><span></span>{{ config('laundry-assistant.openai_enabled', false) && filled(config('laundry-assistant.openai_key')) ? 'AI laundry assistant' : 'Your Laundryhub guide' }}</p></div>
                <div class="laundry-chat-header-actions"><button type="button" data-chat-reset aria-label="Start a new chat" title="Start a new chat" disabled><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></button><button type="button" data-chat-close aria-label="Close laundry assistant"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
            </header>
            <div class="laundry-chat-messages" data-chat-log role="log" aria-label="Conversation with Waku" aria-live="polite" aria-relevant="additions text" tabindex="0">
                <div class="laundry-chat-welcome" data-chat-welcome>
                    <span class="laundry-chat-welcome-icon" aria-hidden="true"><i class="bi bi-chat-heart"></i><i class="bi bi-stars"></i></span>
                    <h3>A little help with your laundry.</h3>
                    <p>Hi! Ask me about pickups, prices, tracking, or caring for your clothes.</p>
                    <div class="laundry-chat-topics" aria-label="Popular questions">
                        <button type="button" data-chat-question="How do I book a pickup?"><i class="bi bi-basket" aria-hidden="true"></i><span>Booking & pickup</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></button>
                        <button type="button" data-chat-question="What are your prices?"><i class="bi bi-tag" aria-hidden="true"></i><span>Prices & extras</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></button>
                        <button type="button" data-chat-question="How do I track my laundry?"><i class="bi bi-pin-map" aria-hidden="true"></i><span>Track my laundry</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></button>
                        <button type="button" data-chat-question="What do care labels mean?"><i class="bi bi-stars" aria-hidden="true"></i><span>Clothing care</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></button>
                    </div>
                </div>
            </div>
            <div class="laundry-chat-suggestions" data-chat-suggestions aria-label="Follow-up questions" hidden></div>
            @if(config('laundry-assistant.bixby_published'))
                <details class="laundry-chat-bixby" data-chat-bixby hidden><summary>Also available through Bixby</summary><p>Open Bixby on your Samsung phone and ask {{ config('laundry-assistant.bixby_name') }} about laundry booking or care.</p></details>
            @endif
            <form class="laundry-chat-composer" data-chat-form>
                <div class="laundry-chat-input-wrap"><label class="visually-hidden" for="laundry-chat-question">Your laundry question</label><textarea id="laundry-chat-question" name="message" rows="1" maxlength="1000" required placeholder="Ask a laundry question…" data-chat-input></textarea><button type="submit" data-chat-send aria-label="Send question" disabled><i class="bi bi-arrow-up" aria-hidden="true"></i></button></div>
                <div class="laundry-chat-footer"><span>Care tips follow garment labels.</span><span data-chat-counter aria-hidden="true">0 / 1000</span></div>
                <span class="visually-hidden" data-chat-status role="status" aria-live="polite"></span>
            </form>
        </div>
    </dialog>
</div>
