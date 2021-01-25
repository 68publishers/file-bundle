(function () {

	let $;
	let Dropzone;

	if (typeof module !== "undefined" && module !== null) {
		$ = require('jquery');
		Dropzone = require('dropzone');
	} else {
		$ = window.$;
		Dropzone = window.dropzone;
	}

	Dropzone.autoDiscover = false;

	/**
	 * @param dropzone
	 * @param element
	 * @return {{dropzone: Dropzone, element: JQuery}}
	 * @constructor
	 */
	function DropzoneArgs(dropzone, element) {
		if (!(dropzone instanceof Dropzone)) {
			throw "Argument 1 given to constructor of WatcherArgs must be instance of Dropzone";
		}

		element = getJqueryElement(element);

		return {
			dropzone: dropzone,
			element: element
		};
	}

	/**
	 * @param {(HTMLElement|jQuery)} el
	 * @returns {jQuery}
	 */
	const getJqueryElement = function (el) {
		if (el instanceof jQuery) {
			return el;
		} else {
			return $(el);
		}
	};

	/**
	 * @type {{getExtension, addExtension}}
	 */
	const DropzoneExtensions = (function () {

		const extensions = {};

		return {
			addExtension: function (name, callback) {
				if ('function' !== typeof callback) {
					throw new Error('Second argument must be callable.');
				}

				extensions[name] = callback;
			},
			getExtension: function (name) {
				if (name in extensions) {
					return extensions[name];
				}

				throw new Error('Extension ' + name + ' is not defined.');
			}
		};
	})();

	/**
	 * @type {{init, registerExtension, hasDropzone, watchDropzone}}
	 */
	const DropzoneFactory = (function (Dropzone, $, Extensions) {

		/**
		 * @type {{WatcherArgs}}
		 */
		const dropzones = {};

		/**
		 * @type {{function(WatcherArgs)}}
		 */
		const dropzoneWatchers = {};

		/**
		 * @type {boolean}
		 */
		let initialized = false;

		/**
		 * @param {String} id
		 *
		 * @returns {void}
		 */
		const callWatchers = function (id) {
			if (id in dropzoneWatchers && id in dropzones) {
				let watchers = dropzoneWatchers[id];
				if (isArray(watchers)) {
					for (let i in watchers) {
						if (watchers.hasOwnProperty(i) && 'function' === typeof watchers[i]) {
							watchers[i](dropzones[id]);
						}
					}
				}
			}
		};

		/**
		 * @param variable
		 * @returns {boolean}
		 */
		const isArray = function (variable) {
			return Object.prototype.toString.call(variable) === '[object Array]';
		};

		/**
		 * @param {(HTMLElement|jQuery)}el
		 * @return {void}
		 */
		const registerDropzone = function (el) {
			el = getJqueryElement(el);

			let settings = el.data('dropzone-settings') || {},
				extensions = el.data('dropzone-extensions') || {},
				id = el.attr('id');

			let dropzone = new Dropzone('#' + id, settings);

			/**
			 * Fix? @link https://github.com/enyo/dropzone/issues/690
			 */
			dropzone.updateTotalUploadProgress = function() {
				let totalProgress = 0;
				let totalFileBytes = 0;
				let totalSentBytes = 0;

				for(let a = 0; a < this.files.length; a++) {
					totalFileBytes = totalFileBytes + this.files[a].size;
					totalSentBytes = totalSentBytes + this.files[a].upload.bytesSent;
					totalProgress = (totalSentBytes / totalFileBytes) * 100;
				}

				return this.emit("totaluploadprogress", totalProgress, totalFileBytes, totalSentBytes);
			};

			dropzones[id] = new DropzoneArgs(dropzone, el);

			for (let extName in extensions) {
				if (extensions.hasOwnProperty(extName)) {
					Extensions.getExtension(extName)(dropzones[id], extensions[extName]);
				}
			}

			callWatchers(id);

			// Nette snippets after single upload
			dropzone.on('success', function (file, payload) {
				if (typeof payload === 'object' && payload.hasOwnProperty('snippets')) {
					$.nette.ext('snippets').updateSnippets(payload.snippets);
				}
			});

			dropzone.on('error', function (file, payload, xhr) {
				if (typeof payload === 'object' && payload.hasOwnProperty('snippets')) {
					$.nette.ext('snippets').updateSnippets(payload.snippets);
				}

				if ('object' === typeof xhr && 406 === xhr.status) {
					file.accepted = false;
					this.removeFile(file);
				}
			});
		};

		return {
			/**
			 * @returns {void}
			 */
			init: function () {
				$.nette.ext('plugin-dropzone-factory-reinit', {
					init: function () {
						this.ext('snippets', true).after(function (el) {
							el.find('[data-dropzone]').each(function () {
								registerDropzone(this);
							});
						});
					}
				});

				$('[data-dropzone]').each(function () {
					registerDropzone(this);
				});

				initialized = true;
			},

			/**
			 * @param {String} id
			 * @return {boolean}
			 */
			hasDropzone: function (id) {
				return id in dropzones;
			},

			/**
			 * @param {String} id
			 * @param {function(WatcherArgs)} callback
			 * @returns {void}
			 */
			watchDropzone: function (id, callback) {
				if (id in dropzoneWatchers) {
					dropzoneWatchers[id].push(callback);
				} else {
					dropzoneWatchers[id] = [
						callback
					];
				}

				if (initialized) {
					callWatchers(id);
				}
			},

			registerExtension: function (name, callback) {
				Extensions.addExtension(name, callback);
			}
		}
	})(Dropzone, $, DropzoneExtensions);

	if (typeof module !== "undefined" && module !== null) {
		module.exports = DropzoneFactory;
	} else {
		window.DropzoneFactory = DropzoneFactory;
	}
})();
