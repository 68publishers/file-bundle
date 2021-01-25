(function () {
	let DropzoneFactory;

	if (typeof module !== "undefined" && module !== null) {
		DropzoneFactory = require('./dropzone-factory');
	} else {
		DropzoneFactory = window.DropzoneFactory;
	}

	/**
	 * @param {Array} files
	 * @return {number[]}
	 */
	function getAcceptedFilesCount(files) {
		let success = 0,
			error = 0;

		for (let i in files) {
			if (!files.hasOwnProperty(i)) {
				continue;
			}

			if (files[i].status === 'success') {
				success++;
			} else {
				error++;
			}
		}

		return [ success, error ];
	}

	/************* extension: completed_signal *************/

	DropzoneFactory.registerExtension('completed_signal', function (args, options) {
		if (!options.hasOwnProperty('url')) {
			throw new Error('Missing options "url".');
		}

		if (!options.hasOwnProperty('count_parameter')) {
			throw new Error('Missing options "count_parameter".');
		}

		args.dropzone.on('queuecomplete', function () {
			const success = getAcceptedFilesCount(args.dropzone.getAcceptedFiles())[0];
			const data = {};

			if (0 >= success) {
				return;
			}

			data[options.count_parameter] = success;

			$.nette.ajax({
				url: options.url,
				data: data,
				off: ['unique']
			});
		});
	});

	/************* extension: toastr_error *************/

	DropzoneFactory.registerExtension('toastr_error', function (args) {
		let Toastr;

		if (typeof module !== "undefined" && module !== null) {
			Toastr = require('toastr');
		} else {
			Toastr = window.toastr;
		}

		args.dropzone.on('error', function (file, payload) {
			if ('string' === typeof payload) {
				Toastr.error(payload.replace('{{fileName}}', '<strong>' + file.name + '</strong>'));
			}
		});
	});

	/************* extension: progressbar *************/

	DropzoneFactory.registerExtension('progressbar', function (args) {
		const bar = $(args.element).find('.progress-bar');

		function getBarWidth (percentage) {
			return Math.floor(bar.parent().width() / 100 * percentage).toString() + 'px'
		}

		/**
		 * Update progress bar width (max to 97%)
		 */
		args.dropzone.on('totaluploadprogress', function (progress) {
			bar.stop().animate({
				width: getBarWidth(progress > 97 ? 97 : progress)
			});
		});

		/**
		 * Finish progressbar and redraw files and uploader
		 */
		args.dropzone.on('queuecomplete', function () {
			const counters = getAcceptedFilesCount(args.dropzone.getAcceptedFiles());

			if (0 < counters[0]) {
				bar.stop().animate({ width: '100%' }, 50, function () {
					bar.addClass('bg-success');
				});
			} else if (0 < counters[1]) {
				bar.addClass('bg-danger no-transition')
					.stop()
					.animate({ width: getBarWidth(100) }, 200, function () {
						bar.fadeOut(100, function () {
							bar.css({ width: 0 })
								.removeClass('bg-danger')
								.show(0, function () {
									bar.removeClass('no-transition');
								});
						});
					});
			}
		});
	});
})();
