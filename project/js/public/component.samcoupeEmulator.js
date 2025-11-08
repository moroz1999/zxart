window.samcoupeEmulatorComponent = new function () {
    const self = this;
    let url;
    let canvasElement;
    let statusElement;
    let componentElement;
    let fullscreenButton;
    let emulator;
    let inited = false;

    // Добавляем глобальный обработчик ошибок
    window.addEventListener('unhandledrejection', function (event) {
        console.error('=== EMULATOR ERROR ===');
        console.error('Error code:', event.reason);
        console.error('Full event:', event);
        if (statusElement) {
            statusElement.textContent = 'Emulator error code: ' + event.reason;
        }
    });

    const init = function () {
        if ((componentElement = document.querySelector('.emulator'))) {
            if ((canvasElement = componentElement.querySelector('.emulator_canvas'))) {
            }
            if ((fullscreenButton = componentElement.querySelector('.emulator_fullscreen'))) {
                fullscreenButton.addEventListener('click', fullscreenClick);
            }
        }
        statusElement = document.querySelector('.emulator_status');
    };

    const emulatorReadyHandler = function () {
        const urlObj = new URL(url);
        const filename = urlObj.pathname.split('/').pop();

        console.log('=== SAM COUPÉ EMULATOR START ===');
        console.log('Filename:', filename);
        console.log('URL:', url);

        // Попробуйте самую минимальную конфигурацию
        const loader = new MAMELoader(
            MAMELoader.driver('samcoupe'),
            MAMELoader.emulatorJS('/libs/mame/mame.js'),
            MAMELoader.emulatorWASM('/libs/mame/mame.wasm'),
            MAMELoader.mountFile('samcoupe.zip',
                MAMELoader.fetchFile('Bios', '/libs/mame/roms/samcoupe.zip')),
            MAMELoader.mountFile(filename, MAMELoader.fetchFile(filename, url)),
            MAMELoader.peripheral('flop1', filename)
        );

        console.log('Loader created:', loader);

        emulator = new Emulator(canvasElement, null, loader);

        console.log('Emulator created:', emulator);

        emulator.start({waitAfterDownloading: false});

        console.log('Emulator start called');

        if (typeof ym !== "undefined") {
            ym(94686067, 'reachGoal', 'emulatorstart')
        }
    };

    self.start = function (newUrl) {
        if (!inited) {
            inited = true;
            init();
        }
        if (canvasElement) {
            url = newUrl;
            componentElement.style.display = 'block';
            const script = document.createElement('script');
            script.src = "/libs/mame/loader.js";
            script.onload = emulatorReadyHandler;
            document.body.appendChild(script);
        }
    };

    const fullscreenClick = function () {
        emulator.requestFullScreen()
    };
};