(() => {
    const modal = document.getElementById('image-cropper-modal');
    if (!modal) return;
    if (modal.parentElement !== document.body) document.body.append(modal);

    const canvas = document.getElementById('image-cropper-canvas');
    const context = canvas.getContext('2d');
    const zoomControl = document.getElementById('image-cropper-zoom');
    const zoomValue = document.getElementById('image-cropper-zoom-value');
    const selectButton = modal.querySelector('[data-crop-select]');
    const cancelButtons = modal.querySelectorAll('[data-crop-cancel]');
    const cropInset = 32;
    const cropSize = canvas.width - cropInset * 2;
    const outputSize = 512;
    let active = null;
    let previewUrl = null;
    let sourceUrl = null;
    let acceptedFile = null;
    let image = null;
    let scale = 1;
    let baseScale = 1;
    let imageX = cropInset;
    let imageY = cropInset;
    let drag = null;
    let parentModalFocusTrap = null;
    let closeTimer = null;
    let isClosing = false;

    const clampImage = () => {
        const width = image.naturalWidth * baseScale * scale;
        const height = image.naturalHeight * baseScale * scale;
        imageX = Math.min(cropInset, Math.max(cropInset + cropSize - width, imageX));
        imageY = Math.min(cropInset, Math.max(cropInset + cropSize - height, imageY));
    };

    const draw = () => {
        if (!image) return;
        const width = image.naturalWidth * baseScale * scale;
        const height = image.naturalHeight * baseScale * scale;
        context.clearRect(0, 0, canvas.width, canvas.height);
        context.fillStyle = '#26262a';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(image, imageX, imageY, width, height);
        context.fillStyle = 'rgb(0 0 0 / 48%)';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.clearRect(cropInset, cropInset, cropSize, cropSize);
        context.drawImage(image, imageX, imageY, width, height);
        context.strokeStyle = '#fff';
        context.lineWidth = 4;
        context.lineCap = 'square';
        const corner = 16;
        const left = cropInset, top = cropInset, right = cropInset + cropSize, bottom = cropInset + cropSize;
        [[left, top, 1, 1], [right, top, -1, 1], [left, bottom, 1, -1], [right, bottom, -1, -1]].forEach(([x, y, dx, dy]) => {
            context.beginPath();
            context.moveTo(x, y + dy * corner);
            context.lineTo(x, y);
            context.lineTo(x + dx * corner, y);
            context.stroke();
        });
    };

    const drawMessage = (message) => {
        context.clearRect(0, 0, canvas.width, canvas.height);
        context.fillStyle = '#f4f6fa';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.fillStyle = '#52627a';
        context.font = '14px system-ui, sans-serif';
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        context.fillText(message, canvas.width / 2, canvas.height / 2, canvas.width - 32);
    };

    const setInputFile = (input, file) => {
        const transfer = new DataTransfer();
        if (file) transfer.items.add(file);
        input.files = transfer.files;
    };

    const closeModal = (restoreAccepted) => {
        if (!active || isClosing) return;
        isClosing = true;
        if (restoreAccepted) setInputFile(active.input, acceptedFile);
        const finishClose = () => {
            if (modal.open && typeof modal.close === 'function') modal.close();
            else modal.removeAttribute('open');
            modal.classList.remove('is-closing');
            document.body.classList.remove('image-cropper-open');
            active.input.focus();
            parentModalFocusTrap?.activate();
            parentModalFocusTrap = null;
            if (sourceUrl) URL.revokeObjectURL(sourceUrl);
            sourceUrl = null;
            active = null;
            image = null;
            drag = null;
            isClosing = false;
            selectButton.disabled = true;
        };
        if (modal.open && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            modal.classList.add('is-closing');
            closeTimer = window.setTimeout(finishClose, 220);
        } else finishClose();
    };

    const openModal = (input, file) => {
        if (closeTimer) window.clearTimeout(closeTimer);
        closeTimer = null;
        isClosing = false;
        modal.classList.remove('is-closing');
        active = {
            input,
            preview: document.getElementById(input.dataset.preview),
            fallback: document.getElementById(input.dataset.fallback),
            remove: input.dataset.remove ? document.getElementById(input.dataset.remove) : null,
        };

        const parentModal = active.input.closest('.modal.show');
        const parentModalInstance = parentModal && window.bootstrap?.Modal?.getInstance(parentModal);
        parentModalFocusTrap = parentModalInstance?._focustrap ?? null;
        parentModalFocusTrap?.deactivate();
        drawMessage('Memuat gambar…');
        selectButton.disabled = true;
        if (typeof modal.showModal === 'function' && !modal.open) modal.showModal();
        else if (!modal.open) modal.setAttribute('open', '');
        document.body.classList.add('image-cropper-open');
        cancelButtons[0].focus();

        image = new Image();
        image.onload = () => {
            if (!active) return;
            baseScale = Math.max(cropSize / image.naturalWidth, cropSize / image.naturalHeight);
            scale = 1;
            zoomControl.value = '100';
            zoomValue.value = '100%';
            const width = image.naturalWidth * baseScale;
            const height = image.naturalHeight * baseScale;
            imageX = cropInset + (cropSize - width) / 2;
            imageY = cropInset + (cropSize - height) / 2;
            clampImage();
            draw();
            selectButton.disabled = false;
        };
        image.onerror = () => {
            if (!active) return;
            setInputFile(input, acceptedFile);
            drawMessage('Gambar gagal dibuka. Coba pilih JPG, PNG, atau WebP.');
            if (sourceUrl) URL.revokeObjectURL(sourceUrl);
            sourceUrl = null;
        };
        if (sourceUrl) URL.revokeObjectURL(sourceUrl);
        sourceUrl = URL.createObjectURL(file);
        image.src = sourceUrl;
    };

    document.querySelectorAll('[data-crop-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                setInputFile(input, acceptedFile);
                return;
            }
            openModal(input, file);
        });
    });

    cancelButtons.forEach((button) => button.addEventListener('click', () => closeModal(true)));
    modal.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeModal(true);
    });
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal(true);
    });
    document.addEventListener('keydown', (event) => {
        if (!modal.open) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal(true);
            return;
        }
        if (event.key === 'Tab') {
            const focusable = [cancelButtons[0], selectButton, canvas, zoomControl].filter((element) => !element.disabled);
            const first = focusable[0], last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
        if (event.target === canvas) {
            const step = event.shiftKey ? 12 : 3;
            if (event.key === 'ArrowLeft') imageX += step;
            else if (event.key === 'ArrowRight') imageX -= step;
            else if (event.key === 'ArrowUp') imageY += step;
            else if (event.key === 'ArrowDown') imageY -= step;
            else return;
            event.preventDefault();
            clampImage();
            draw();
        }
    });

    canvas.addEventListener('pointerdown', (event) => {
        if (!image) return;
        canvas.setPointerCapture(event.pointerId);
        drag = { x: event.clientX, y: event.clientY, imageX, imageY };
        canvas.classList.add('is-dragging');
        event.preventDefault();
    });
    canvas.addEventListener('pointermove', (event) => {
        if (!drag) return;
        const rect = canvas.getBoundingClientRect();
        const canvasScale = canvas.width / rect.width;
        imageX = drag.imageX + (event.clientX - drag.x) * canvasScale;
        imageY = drag.imageY + (event.clientY - drag.y) * canvasScale;
        clampImage();
        draw();
    });
    const stopDrag = () => { drag = null; canvas.classList.remove('is-dragging'); };
    canvas.addEventListener('pointerup', stopDrag);
    canvas.addEventListener('pointercancel', stopDrag);

    zoomControl.addEventListener('input', () => {
        zoomValue.value = `${zoomControl.value}%`;
        if (!image) return;
        const oldWidth = image.naturalWidth * baseScale * scale;
        const oldHeight = image.naturalHeight * baseScale * scale;
        const centerX = imageX + oldWidth / 2;
        const centerY = imageY + oldHeight / 2;
        scale = Number(zoomControl.value) / 100;
        imageX = centerX - image.naturalWidth * baseScale * scale / 2;
        imageY = centerY - image.naturalHeight * baseScale * scale / 2;
        clampImage();
        draw();
    });

    selectButton.addEventListener('click', () => {
        if (!active || !image) return;
        const sourceX = (cropInset - imageX) / (baseScale * scale);
        const sourceY = (cropInset - imageY) / (baseScale * scale);
        const sourceSize = cropSize / (baseScale * scale);
        const output = document.createElement('canvas');
        output.width = outputSize;
        output.height = outputSize;
        output.getContext('2d').drawImage(image, sourceX, sourceY, sourceSize, sourceSize, 0, 0, outputSize, outputSize);
        output.toBlob((blob) => {
            if (!blob || !active) return;
            const croppedFile = new File([blob], 'simkeu-image.jpg', { type: 'image/jpeg', lastModified: Date.now() });
            setInputFile(active.input, croppedFile);
            acceptedFile = croppedFile;
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(croppedFile);
            active.preview.src = previewUrl;
            active.preview.hidden = false;
            if (active.fallback) active.fallback.hidden = true;
            if (active.remove?.checked) active.remove.checked = false;
            closeModal(false);
        }, 'image/jpeg', 0.9);
    });

    document.querySelectorAll('[data-crop-input]').forEach((input) => {
        const remove = input.dataset.remove ? document.getElementById(input.dataset.remove) : null;
        const preview = document.getElementById(input.dataset.preview);
        const fallback = document.getElementById(input.dataset.fallback);
        const originalSource = preview?.src || '';
        const hadOriginal = Boolean(preview && !preview.hidden);

        remove?.addEventListener('change', () => {
            if (remove.checked) {
                acceptedFile = null;
                input.value = '';
                if (previewUrl) URL.revokeObjectURL(previewUrl);
                previewUrl = null;
                if (preview) preview.hidden = true;
                if (fallback) fallback.hidden = false;
            } else if (hadOriginal) {
                preview.src = originalSource;
                preview.hidden = false;
                if (fallback) fallback.hidden = true;
            } else {
                if (preview) preview.hidden = true;
                if (fallback) fallback.hidden = false;
            }
        });
    });
})();
