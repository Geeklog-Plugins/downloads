(function () {
    'use strict';

    function closeLightbox(dialog) {
        if (dialog && dialog.open) {
            dialog.close();
        }
    }

    function init() {
        if (!('HTMLDialogElement' in window)) {
            return;
        }

        var links = document.querySelectorAll('a.dlm-lightbox');
        if (!links.length) {
            return;
        }

        var dialog = document.createElement('dialog');
        dialog.className = 'dlm-lightbox-dialog';
        dialog.setAttribute('aria-label', 'Image preview');

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'dlm-lightbox-close';
        button.setAttribute('aria-label', 'Close image preview');
        button.textContent = '\u00d7';

        var image = document.createElement('img');
        image.className = 'dlm-lightbox-image';
        image.alt = '';

        dialog.appendChild(button);
        dialog.appendChild(image);
        document.body.appendChild(dialog);

        button.addEventListener('click', function () {
            closeLightbox(dialog);
        });

        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) {
                closeLightbox(dialog);
            }
        });

        links.forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                image.src = link.href;
                image.alt = link.querySelector('img') ? link.querySelector('img').alt : '';
                dialog.showModal();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
