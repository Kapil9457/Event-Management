document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-qr-token]').forEach((button) => {
        button.addEventListener('click', () => {
            const token = button.getAttribute('data-qr-token');
            const container = button.closest('.list-group-item').querySelector('.qr-preview');
            container.innerHTML = '';
            const url = `${window.location.origin}${window.location.pathname.replace('events.php', '')}view_event.php?token=${token}`;
            new QRCode(container, {
                text: url,
                width: 160,
                height: 160,
            });
        });
    });

    const qrReader = document.getElementById('qr-reader');
    if (qrReader && window.Html5Qrcode) {
        const resultBox = document.getElementById('qr-result');
        const html5QrCode = new Html5Qrcode('qr-reader');
        const qrConfig = { fps: 10, qrbox: 200 };

        Html5Qrcode.getCameras().then((devices) => {
            if (devices && devices.length) {
                html5QrCode.start(
                    { facingMode: 'environment' },
                    qrConfig,
                    (decodedText) => {
                        resultBox.hidden = false;
                        resultBox.textContent = 'QR detected. Redirecting...';
                        window.location.href = decodedText;
                    }
                );
            } else {
                resultBox.hidden = false;
                resultBox.textContent = 'No camera found. Please use a supported device.';
            }
        }).catch(() => {
            if (resultBox) {
                resultBox.hidden = false;
                resultBox.textContent = 'Camera permission denied. Please allow access.';
            }
        });
    }

    const pinForm = document.getElementById('pin-form');
    if (pinForm) {
        const token = pinForm.getAttribute('data-event-token');
        const hasError = pinForm.getAttribute('data-pin-error') === '1';
        const storedPin = localStorage.getItem(`event_pin_${token}`);
        const pinInput = pinForm.querySelector('input[name=\"pin\"]');
        if (!hasError && storedPin && pinInput) {
            pinInput.value = storedPin;
            pinForm.submit();
        }
        pinForm.addEventListener('submit', () => {
            if (pinInput && pinInput.value) {
                localStorage.setItem(`event_pin_${token}`, pinInput.value);
            }
        });
    }

    const gallery = document.querySelector('.premium-gallery');
    if (gallery) {
        const token = gallery.getAttribute('data-event-token');
        if (token) {
            localStorage.setItem('last_event_token', token);
        }
    }
});
