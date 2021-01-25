$(function () {
	let DropzoneFactory;

	if (typeof module !== "undefined" && module !== null) {
		DropzoneFactory = require('./dropzone-factory');
	} else {
		DropzoneFactory = window.DropzoneFactory;
	}

	// Init dropzones
	DropzoneFactory.init();
});
