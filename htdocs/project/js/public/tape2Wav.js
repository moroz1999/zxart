window.tape2Wav = new function () {

    // Web TAP player
// Dimon '2024
// Константы
// Тут некоторые цифры вызывают вопросы, но я в итоге делал по образу WAV, которую формирует Taper 3.0
    var PILOT_LEN = 2000;		// длина пилот-тона
    var SYNC_LEN = 1;			// длина синргоимпульса
    var BIT_LEN = 1				// длина бита данных
    var PAUSE_LEN = 60000;		// длина паузы
    var PILOT_LH = 27;			// длина полупериода пилота
    var SYNC_LO = 8;			// длина синхроимпульса, низкий уровень
    var SYNC_HI = 8;			// длина синхроимпульса, высокий уровень
    var BIT0_LO = 11;			// длина бита 0, низкий уровень
    var BIT0_HI = 10;			// длина бита 0, высокий уровень
    var BIT1_LO = 22;			// длина бита 1, низкий уровень
    var BIT1_HI = 21;			// длина бита 1, высокий уровень
    var LO = -600;				// значение низкого уровня в WAV
    var HI = 15000;				// значение высокого уровня в WAV

    var fileInput = document.getElementById("tap_file");	// Исходный файл TAP
    var pos = 0;											// позиция в TAP
    var out = [];									// Итоговый звук
    var opos = 0;											// Позиция в WAV

    this.convertUrl = async function(fileUrl) {
        try {
            const uint8Array = await fetchFileAndConvertToUint8Array(fileUrl);
            return make_wav(uint8Array);
        } catch (error) {
            console.error(error);
            throw error;
        }
    }

    async function fetchFileAndConvertToUint8Array(url) {
        try {
            // Загружаем файл
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Сетевая ошибка при попытке загрузить файл: ' + response.statusText);
            }
            const buffer = await response.arrayBuffer();
            return new Uint8Array(buffer);
        } catch (error) {
            console.error('Ошибка при загрузке или обработке файла:', error);
            throw error; // Перебрасываем ошибку дальше
        }
    }


// Конвертит TAP в WAV
    function make_wav(data) {
        pos = 0;
        opos = 0;

        var tap_len = data.length;		// Длина TAP файла
        console.log("TAP length: ", tap_len);

        while (pos < tap_len) {
            var block_len = data[pos] + data[pos + 1] * 256;
            pos += 2;
            console.log("Block length: ", block_len - 2);

            // Определим тип блока
            var block_type = data[pos];
            if (block_type === 0) console.log("Block type: header");
            else if (block_type === 255) console.log("Block type: body");
            else console.log("Block type: UNKNOWN");

            rec_pilot_tone();								// Пишем пилот
            if (block_type === 0) rec_pilot_tone();			// Если заголовок, то пилот в два раза длиннее

            rec_sychro_impulse();							// Пишем синхроимпульс

            console.log("BLOCK");

            // Цикл по байтам блока данных
            for (var n = 0; n < block_len; n++) {
                // Цикл по байту
                for (var bit = 7; bit >= 0; bit--)			// Биты идут в обратном порядке
                {
                    if (data[pos] & (2 ** bit)) rec_bit1();	// Пишем бит 1
                    else rec_bit0();						// либо 0
                }
                pos++;
            }

            if (pos < tap_len) rec_pause();					// Если не последний блок, пишем в WAV паузу
        }

        console.log("WAV length:", opos);

        return make_download(out, opos);							// Генерим WAV
    }

// Добавляет в WAV пилот-тон примерно 1.5 сек
    function rec_pilot_tone() {
        console.log("PILOT");

        // Цикл по длине пилота
        for (var n = 0; n < PILOT_LEN; n++) {
            // Цикл отсчётов низкого уровня сигнала
            for (var a = 0; a < PILOT_LH; a++) {
                out[opos] = LO;
                opos++;
            }

            // Цикл отсчётов высокого уровня сигнала
            for (var b = 0; b < PILOT_LH; b++) {
                out[opos] = HI;
                opos++;
            }
        }
    }

// Добавляет в WAV синхроимпульс
    function rec_sychro_impulse() {
        console.log("SYNC");

        for (var n = 0; n < SYNC_LEN; n++) {
            for (var a = 0; a < SYNC_LO; a++) {
                out[opos] = LO;
                opos++;
            }

            for (var b = 0; b < SYNC_HI; b++) {
                out[opos] = HI;
                opos++;
            }
        }
    }

// Добавляет в WAV бит 0
    function rec_bit0() {
        for (var n = 0; n < BIT_LEN; n++) {
            for (var a = 0; a < BIT0_LO; a++) {
                out[opos] = LO;
                opos++;
            }

            for (var b = 0; b < BIT0_HI; b++) {
                out[opos] = HI;
                opos++;
            }
        }
    }


// Добавляет в WAV бит 1
    function rec_bit1() {
        for (var n = 0; n < BIT_LEN; n++) {
            for (var a = 0; a < BIT1_LO; a++) {
                out[opos] = LO;
                opos++;
            }

            for (var b = 0; b < BIT1_HI; b++) {
                out[opos] = HI;
                opos++;
            }
        }
    }


// Добавляет в WAV паузу 1.5 сек
    function rec_pause() {
        console.log("PAUSE");
        for (var n = 0; n < PAUSE_LEN; n++) {
            out[opos] = LO;
            opos++;
        }
    }

// Делает линк для скачивания, запускает проигрывание в браузере
    function make_download(abuffer, total_samples) {
        // Генерим WAV
        var blob = bufferToWave(abuffer, total_samples * 2);
        console.log(blob);

        // Создаём файл
        var new_file = URL.createObjectURL(blob);
        console.log(new_file);

        // Выводим ссылку для скачивания
        // var download_link = document.getElementById("download_link");
        // download_link.href = new_file;
        // download_link.download = generateFileName();

        return new_file;
    }

// Генерит имя файла
    function generateFileName() {
        var origin_name = fileInput.files[0].name;
        var pos = origin_name.lastIndexOf('.');
        var no_ext = origin_name.slice(0, pos);
        return no_ext + ".wav";
    }

// Генерит WAV из данных в буфере
    function bufferToWave(abuffer, len) {
        var length = len + 44;
        buffer = new ArrayBuffer(length);
        view = new DataView(buffer);
        var sample;
        var offset = 0;
        var pos = 0;

        // write WAVE header
        setUint32(0x46464952);                         // "RIFF"
        setUint32(length - 8);                         // file length - 8
        setUint32(0x45564157);                         // "WAVE"

        setUint32(0x20746d66);                         // "fmt " chunk
        setUint32(16);                                 // length = 16
        setUint16(1);                                  // PCM (uncompressed)
        setUint16(1);									// numOfChan
        setUint32(44100);								// sampleRate
        setUint32(44100 * 2);							// avg. bytes/sec
        setUint16(2);                      			// block-align
        setUint16(16);                                 // 16-bit (hardcoded in this demo)

        setUint32(0x61746164);                         // "data" - chunk
        setUint32(length - pos - 4);                   // chunk length

        while (pos < length) {
            sample = abuffer[offset];
            view.setInt16(pos, sample, true);          // write 16-bit sample
            pos += 2;
            offset++;                                    // next source sample
        }

        return new Blob([view], {type: "audio/wav"});

        function setUint16(data) {
            view.setUint16(pos, data, true);
            pos += 2;
        }

        function setUint32(data) {
            view.setUint32(pos, data, true);
            pos += 4;
        }
    }

}