(function () {
    async function copyText(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(text);
            return;
        }

        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', 'readonly');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-copy-share]');
        if (!button) {
            return;
        }

        const text = button.dataset.copyText || window.location.href;
        const original = button.innerHTML;

        try {
            await copyText(text);
            button.innerHTML = '<i class="fas fa-check"></i> Copiado';
            button.disabled = true;
            window.setTimeout(() => {
                button.disabled = false;
                button.innerHTML = original;
            }, 1800);
        } catch (error) {
            alert('No se pudo copiar el enlace.');
        }
    });
})();
