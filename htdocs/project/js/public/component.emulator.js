window.emulatorComponent = new function() {
	const self = this;
	let url;
	let canvasElement;
	let componentElement;
	let fullscreenButton;
	const init = function() {
		if (componentElement = document.querySelector('.emulator')) {
			if (canvasElement = componentElement.querySelector('.emulator_canvas')) {
				canvasElement.addEventListener("webglcontextlost", function(e) {
					alert('WebGL context lost. You will need to reload the page.');
					e.preventDefault();
				}, false);
			}
			if (fullscreenButton = componentElement.querySelector('.emulator_fullscreen')) {
				fullscreenButton.addEventListener('click', fullscreenClick);
			}
		}
	};
	const spawnModule = function() {
		window.Module = {
			onReady: emulatorReadyHandler,
			canvas: (function() {
				return canvasElement;
			})(),
			locateFile: function(file) {
				return '/libs/us/' + file;
			}
		};
	};
	const emulatorReadyHandler = function() {
		Module.setCanvasSize(960, 720);
		Module.ccall('OpenFile', // name of C function
			null, // return type
			['string'], // argument types
			[url]);
		Module.ccall('OnCommand',
			null, // return type
			['string'], // argument types
			['joystick=cursor']);
		Module.ccall('OnCommand',
			null, // return type
			['string'], // argument types
			['filtering=off']);
	};
	const fullscreenClick = function() {
		Module.requestFullscreen(false, true);
	};
	self.start = function(newUrl) {
		if (canvasElement) {
			url = newUrl;
			componentElement.style.display = 'block';

			if (typeof Module == 'undefined') {
				spawnModule();

				var script = document.createElement('script');
				script.src = "/libs/us/unreal_speccy_portable.js";
				document.body.appendChild(script);
			} else {
				emulatorReadyHandler();
			}
		}
	};
	controller.addListener('initDom', init);
};