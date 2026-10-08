(function () {
    'use strict';

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

        var image = document.createElement('img');
        image.className = 'dlm-lightbox-image';
        image.alt = '';

        dialog.appendChild(image);
        document.body.appendChild(dialog);

        dialog.addEventListener('click', function () {
            if (dialog.open) {
                dialog.close();
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
