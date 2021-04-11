'use strict';

(function () {

    if (!window.fetch) {
        return;
    }

    const $ = require('jquery');

    const download = (url, name, errorCallback) => {
        fetch(url)
            .then(response => response.blob())
            .then(blob => {
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.style = 'display: none';
                a.download = (name && 0 < name.length) ? name : '';

                document.body.appendChild(a);
                a.click();
            })
            .catch(errorCallback);
    };

    $(document).on('click', 'a[data-file-manager-download]', function (e) {
        const self = $(this);
        const url = self.attr('href');

        if (url && !self.data('file-manager-download-failed')) {
            e.preventDefault();
            download(url, self.data('file-manager-download'), function () {
                self.data('file-manager-download-failed', true);

                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                a.style = 'display: none';

                document.body.appendChild(a);
                a.click();
            });
        }
    });

})();
