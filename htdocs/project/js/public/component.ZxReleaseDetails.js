window.ZxReleaseDetailsComponent = function (componentElement) {
    let id;
    let data;
    let playButtons;
    let tapeRunning = false;
    let audio;
    const init = function () {
        if ((id = componentElement.dataset.id)) {
            data = zxReleasesLogics.getReleaseData(id);

            playButtons = document.querySelectorAll('.release-run-tape', componentElement);
            if (playButtons) {
                playButtons.forEach(button => button.addEventListener('click', playButtonClick));
            }
        }
    };
    const playButtonClick = function (event) {
        const playButton = event.target ?? null;
        if (playButton) {
            if (!audio) {
                audio = new Audio();
            }
            tapeRunning = !tapeRunning;
            if (tapeRunning) {
                const playUrl = playButton.dataset.url;
                playButton.classList.add('release-run-tape-running');
                tape2Wav.convertUrl(playUrl).then(wavUrl => {
                    audio.addEventListener('loadeddata', function () {
                        audio.play();
                    }, false);

                    if (typeof ym !== "undefined") {
                        ym(94686067, 'reachGoal', 'run-tape')
                    }
                    audio.src = wavUrl;
                })
            } else {
                audio.pause();

                playButton.classList.remove('release-run-tape-running')
            }
        }
    }
    const salesClick = function () {
        if (typeof ym !== 'undefined') {
            ym(94686067, 'reachGoal', 'open-cart-link', {}, () => window.open(this.model.externalLink));
        }
    }

    init();
};