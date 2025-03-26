window.zx81EmulatorComponent = new function () {
    const self = this;
    let url;
    let selector;
    let canvasElement;
    let statusElement;
    let componentElement;
    let emulatorUI;
    let inited = false;
    let currentHardware = '';
    const init = function () {
        if ((componentElement = document.querySelector('.emulator'))) {
            if ((canvasElement = componentElement.querySelector('.emulator_canvas'))) {

            }
            // if ((fullscreenButton = componentElement.querySelector('.emulator_fullscreen'))) {
            //     fullscreenButton.addEventListener('click', fullscreenClick);
            // }
            if (selector = componentElement.querySelector(('.emulator_type'))) {
                window.addEventListener('keydown', (event) => {
                    if (event.key === 'F2') {
                        setTimeout(getScreenshot, 300);
                    }
                });
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
    self.start = function (newUrl, newFormat) {
        currentHardware = newFormat;
        if (!inited) {
            inited = true;
            init();
        }
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
    const getScreenshot = function () {
        if (emulatorUI) {
            const file = emulatorUI.jtyOne.getDisplayFile().filter(byte => byte !== 118);
            const blob = new Blob([file]);
            sendScreenshot(blob, currentHardware === 'zx80' ? 's80' : 's81');
        }
    }

    const sendScreenshot = function (blob, format) {
        const submitUrl = window.currentElementURL + 'id:' + window.currentElementId + '/action:uploadScreenshot/format:' + format
        fetch(submitUrl, {method: "POST", body: blob})
            .then(response => {
                if (response.ok) return response;
                else throw Error(`Server returned ${response.status}: ${response.statusText}`)
            })
            .then(response => console.log(response.text()))
            .catch(err => {
                console.log(err);
            });
    }

};