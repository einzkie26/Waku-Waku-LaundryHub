(() => {
    'use strict';
    const form = document.querySelector('[data-staff-profile]');
    if (!form) return;
    const input = form.querySelector('#staff-photo');
    const preview = form.querySelector('[data-photo-preview]');
    const image = preview.querySelector('[data-photo-image]');
    const initial = preview.querySelector('[data-photo-initial]');
    const feedback = form.querySelector('[data-photo-feedback]');
    const remove = form.querySelector('[data-remove-photo]');
    let objectUrl;
    let version = 0;
    const release = () => { if (objectUrl) URL.revokeObjectURL(objectUrl); objectUrl = null; };
    const display = url => { image.hidden = !url; initial.hidden = Boolean(url); if (url) image.src = url; };
    const error = message => {
        input.value = '';
        release();
        display(remove?.checked ? '' : preview.dataset.savedPhoto);
        feedback.textContent = message;
        feedback.classList.add('is-error');
    };
    input.addEventListener('change', async () => {
        const current = ++version;
        release();
        feedback.classList.remove('is-error');
        const file = input.files?.[0];
        if (!file) { display(preview.dataset.savedPhoto); feedback.textContent = ''; return; }
        if (!['image/jpeg','image/png','image/webp'].includes(file.type)) { error('Choose a JPG, PNG or WebP image.'); return; }
        if (file.size > 3 * 1024 * 1024) { error('Choose an image smaller than 3 MB.'); return; }
        objectUrl = URL.createObjectURL(file);
        const candidate = new Image();
        candidate.src = objectUrl;
        try {
            await candidate.decode();
            if (current !== version) return;
            if (candidate.width < 64 || candidate.height < 64 || candidate.width > 4096 || candidate.height > 4096) {
                error('Choose an image with each side between 64 and 4,096 pixels.'); return;
            }
            if (remove) remove.checked = false;
            display(objectUrl);
            feedback.textContent = 'Photo ready. Save changes to upload.';
        } catch { if (current === version) error('This image could not be opened. Try another photo.'); }
    });
    remove?.addEventListener('change', () => {
        ++version;
        release();
        input.value = '';
        display(remove.checked ? '' : preview.dataset.savedPhoto);
        feedback.classList.remove('is-error');
        feedback.textContent = remove.checked ? 'Photo will be removed when you save.' : '';
    });
    form.addEventListener('reset', () => {
        ++version;
        release();
        display(preview.dataset.savedPhoto);
        feedback.textContent = '';
        feedback.classList.remove('is-error');
        form.querySelectorAll('[data-toggle-password]').forEach(button => {
            document.getElementById(button.dataset.togglePassword).type = 'password';
            button.textContent = 'Show'; button.setAttribute('aria-pressed','false');
        });
    });
    window.addEventListener('pagehide', () => { ++version; release(); });
})();
