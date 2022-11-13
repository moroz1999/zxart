window.emulatorComponent = new function () {
    const self = this;
    let url;
    let canvasElement;
    let componentElement;
    let fullscreenButton;
    let selector;
    let blob;
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

            if (selector.value === '48') {
                const screenData = fileContents.slice(27, 27 + 6912);
                blob = new Blob([screenData]);
                sendScreenshot();
            }
            else if (selector.value === '128') {
                const start2 = 27 + 16384 * 3 + 4 + 16384 * 4;
                const screenData = fileContents.slice(start2, start2 + 6912);
                blob = new Blob([screenData]);
                sendScreenshot();
            } else if (selector.value === 'giga') {
                const screenData = fileContents.slice(27, 27 + 6912);
                const start2 = 27 + 16384 * 3 + 4 + 16384 * 4;
                const screenData2 = fileContents.slice(start2, start2 + 6912);
                blob = new Blob([screenData, screenData2]);
                sendScreenshot();
            } else if (selector.value === 'double') {
                const screenData = fileContents.slice(27, 27 + 6912);
                setTimeout(()=>{
                    FS.chdir('/');

                    const fileContents = FS.readFile(file[2]);
                    const screenData2 = fileContents.slice(27, 27 + 6912);
                    blob = new Blob([screenData, screenData2]);
                    sendScreenshot();

                }, 30);
            }

        }
    }
    const sendScreenshot = function (){
        const submitUrl = window.currentElementURL + 'id:' + window.currentElementId + '/action:uploadScreenshot/'
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
    };
    const fullscreenClick = function () {
        Module.ccall('OnCommand',
            null,
            ['string'],
            ['full screen=on']);
    };
    self.start = function (newUrl) {
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
    controller.addListener('initDom', init);
};