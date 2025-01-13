window.tsconfEmulatorComponent = new function () {
    const self = this;
    let url;
    let canvasElement;
    let statusElement;
    let componentElement;
    let fullscreenButton;
    let emulator;
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
        const extension = filename.split('.')?.pop()?.toLowerCase();
        const peripheral = extension === 'img' ? 'hard2' : extension === 'spg' ? 'dump' : 'flop1';
        const nvramDirectory = extension === 'img' ? 'nvramsd' : 'nvram';

        const loader = new MAMELoader(MAMELoader.driver("tsconf"),
            MAMELoader.nativeResolution(760, 576),
            MAMELoader.emulatorJS("/libs/mame/mame.js"),
            MAMELoader.emulatorWASM("/libs/mame/mame.wasm"),
            MAMELoader.mountFile("nvram/tsconf/glukrs_nvram", MAMELoader.fetchFile("CMOS", "/libs/mame/nvram/tsconf_trdos/glukrs_nvram")),
            MAMELoader.mountFile("nvramsd/tsconf/glukrs_nvram", MAMELoader.fetchFile("CMOS", "/libs/mame/nvram/tsconf_sd/glukrs_nvram")),
            MAMELoader.mountFile("cfg/tsconf.cfg", MAMELoader.fetchFile("Cfg", "/libs/mame/cfg/tsconf.cfg")),
            MAMELoader.mountFile("tsconf.zip", MAMELoader.fetchFile("Bios", "/libs/mame/roms/tsconf.zip")),
            MAMELoader.mountFile("betadisk.zip", MAMELoader.fetchFile("Beta", "/libs/mame/roms/betadisk.zip")),
            MAMELoader.mountFile("kb_ms_natural.zip", MAMELoader.fetchFile("Keyboard", "/libs/mame/roms/kb_ms_natural.zip")),
            MAMELoader.mountFile("zxbus_neogs.zip", MAMELoader.fetchFile("GS", "/libs/mame/roms/zxbus_neogs.zip")),

            MAMELoader.peripheral("cfg_directory", "cfg"),
            MAMELoader.peripheral("nvram_directory", nvramDirectory),
            MAMELoader.extraArgs(["-zxbus1", "neogs", "-uimodekey", "DEL"]), MAMELoader.mountFile(filename, MAMELoader.fetchFile(filename, url)),
            MAMELoader.peripheral(peripheral, filename)
        );
        emulator = new Emulator(canvasElement, null, loader);
        emulator.start({waitAfterDownloading: false});

        if (typeof ym !== "undefined") {
            ym(94686067, 'reachGoal', 'emulatorstart')
        }
    };
    self.start = function (newUrl) {

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
    controller.addListener('initDom', init);
};