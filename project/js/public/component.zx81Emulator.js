window.zx81EmulatorComponent = new function () {
    const self = this;
    let url;
    let canvasElement;
    let statusElement;
    let componentElement;
    let emulatorUI;
    const init = function () {
        if ((componentElement = document.querySelector('.emulator'))) {
            if ((canvasElement = componentElement.querySelector('.emulator_canvas'))) {

            }
        }
        statusElement = document.querySelector('.emulator_status');
    };
    const emulatorReadyHandler = function () {
        zx81opts.rom = '/libs/zx81/roms/zx81.rom';
        emulatorUI = new ZX81EmulatorUI(statusElement, canvasElement, url);
        if (typeof ym !== "undefined") {
            ym(94686067, 'reachGoal', 'emulatorstart')
        }
    };
    self.start = function (newUrl) {
        if (emulatorUI) {
            emulatorUI.stop();
        }
        if (canvasElement) {
            url = newUrl;
            componentElement.style.display = 'block';

            const script = document.createElement('script');
            script.src = "/libs/zx81/zx81_emu.js";
            script.onload = emulatorReadyHandler;
            document.body.appendChild(script);
        }
    };
    controller.addListener('initDom', init);
};