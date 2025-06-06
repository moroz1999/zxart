window.emulatorComponent = new function () {
    const self = this;
    let url;
    let canvasElement;
    let componentElement;
    let fullscreenButton;
    let selector;
    let inited = false;
    const init = function () {
        if ((componentElement = document.querySelector('.emulator'))) {
            if ((canvasElement = componentElement.querySelector('.emulator_canvas'))) {
                if ((fullscreenButton = componentElement.querySelector('.emulator_fullscreen'))) {
                    fullscreenButton.addEventListener('click', fullscreenClick);
                }
                if (selector = componentElement.querySelector(('.emulator_type'))) {
                    window.addEventListener('keydown', (event) => {
                        if (event.key === 'F2') {
                            setTimeout(getScreenshot, 300);
                        }
                    });
                }
            }
        }

    };
    const spawnModule = function () {
        window.Module = {
            onReady: emulatorReadyHandler,
            canvas: canvasElement,
            locateFile: function (file) {
                return '/libs/us/' + file;
            }
        };
    };
    const getScreenshot = function () {
        const dir = url.substring(0, url.lastIndexOf('/') + 1);
        const file = FS.readdir(dir);
        if (file[2]) {
            FS.chdir(dir);
            const fileContents = FS.readFile(file[2]);
            const reg7ffd = fileContents[27 + 49152 + 2];
            const start2 = (reg7ffd & 0b111 === 7) ?
                (27 + 32768) :
                27 + 16384 * 3 + 4 + 16384 * 4
            ;

            if (selector.value === '48') {
                const screenData = fileContents.slice(27, 27 + 6912);
                const blob = new Blob([screenData]);
                sendScreenshot(blob, 'standard');
            } else if (selector.value === '128') {
                const screenData = fileContents.slice(start2, start2 + 6912);
                const blob = new Blob([screenData]);
                sendScreenshot(blob, 'standard');
            } else if (selector.value === 'giga') {
                const screenData = fileContents.slice(27, 27 + 6912);
                const screenData2 = fileContents.slice(start2, start2 + 6912);
                const blob = new Blob([screenData, screenData2]);
                sendScreenshot(blob, 'gigascreen');
            }
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
    const emulatorReadyHandler = function () {
        // Module.setCanvasSize(960, 720);
        Module.ccall('OpenFile', // name of C function
            null,
            ['string'],
            [url]);
        Module.ccall('OnCommand',
            null,
            ['string'],
            ['joystick=cursor']);
        Module.ccall('OnCommand',
            null,
            ['string'],
            ['filtering=off']);

        if (typeof ym !== "undefined") {
            ym(94686067, 'reachGoal', 'emulatorstart')
        }
    };
    const fullscreenClick = function () {
        Module.ccall('OnCommand',
            null,
            ['string'],
            ['full screen=on']);
    };
    self.start = function (newUrl) {
        if (!inited) {
            inited = true;
            init();
        }
        if (canvasElement) {
            url = newUrl;
            componentElement.style.display = 'block';

            if (typeof window.Module === 'undefined') {
                spawnModule();

                var script = document.createElement('script');
                script.src = "/libs/us/unreal_speccy_portable.js";
                document.body.appendChild(script);
            } else {
                emulatorReadyHandler();
            }
        }
    };
};