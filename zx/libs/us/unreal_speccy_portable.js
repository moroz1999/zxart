var Module = void 0 !== Module ? Module : {};
Module.expectedDataFileDownloads || (Module.expectedDataFileDownloads = 0), Module.expectedDataFileDownloads++, function (e) {
    if ("object" == typeof window) window.encodeURIComponent(window.location.pathname.toString().substring(0, window.location.pathname.toString().lastIndexOf("/")) + "/"); else {
        if ("undefined" == typeof location) throw"using preloaded data can only be done on a web page or in a web worker";
        encodeURIComponent(location.pathname.toString().substring(0, location.pathname.toString().lastIndexOf("/")) + "/")
    }
    var t = "unreal_speccy_portable.data";
    "function" != typeof Module.locateFilePackage || Module.locateFile || (Module.locateFile = Module.locateFilePackage, err("warning: you defined Module.locateFilePackage, that has been renamed to Module.locateFile (using your locateFilePackage for now)"));
    var r = Module.locateFile ? Module.locateFile("unreal_speccy_portable.data", "") : "unreal_speccy_portable.data",
        n = e.remote_package_size;
    e.package_uuid;
    var o, a, i, s, c = null, l = Module.getPreloadedPackage ? Module.getPreloadedPackage(r, n) : null;

    function u() {
        function r(e, t) {
            if (!e) throw t + (new Error).stack
        }

        function n(e, t, r) {
            this.start = e, this.end = t, this.audio = r
        }

        Module.FS_createPath("/", "res", !0, !0), Module.FS_createPath("/res", "font", !0, !0), Module.FS_createPath("/res", "rom", !0, !0), n.prototype = {
            requests: {},
            open: function (e, t) {
                this.name = t, this.requests[t] = this, Module.addRunDependency("fp " + this.name)
            },
            send: function () {
            },
            onload: function () {
                var e = this.byteArray.subarray(this.start, this.end);
                this.finish(e)
            },
            finish: function (e) {
                Module.FS_createDataFile(this.name, null, e, !0, !0, !0), Module.removeRunDependency("fp " + this.name), this.requests[this.name] = null
            }
        };
        for (var o = e.files, a = 0; a < o.length; ++a) new n(o[a].start, o[a].end, o[a].audio).open("GET", o[a].filename);

        function i(t) {
            r(t, "Loading data file failed."), r(t instanceof ArrayBuffer, "bad input to processPackageData");
            var o = new Uint8Array(t);
            n.prototype.byteArray = o;
            for (var a = e.files, i = 0; i < a.length; ++i) n.prototype.requests[a[i].filename].onload();
            Module.removeRunDependency("datafile_unreal_speccy_portable.data")
        }

        Module.addRunDependency("datafile_unreal_speccy_portable.data"), Module.preloadResults || (Module.preloadResults = {}), Module.preloadResults[t] = {fromCache: !1}, l ? (i(l), l = null) : c = i
    }

    l || (o = r, a = n, i = function (e) {
        c ? (c(e), c = null) : l = e
    }, (s = new XMLHttpRequest).open("GET", o, !0), s.responseType = "arraybuffer", s.onprogress = function (e) {
        var t = o, r = a;
        if (e.total && (r = e.total), e.loaded) {
            s.addedTotal ? Module.dataFileDownloads[t].loaded = e.loaded : (s.addedTotal = !0, Module.dataFileDownloads || (Module.dataFileDownloads = {}), Module.dataFileDownloads[t] = {
                loaded: e.loaded,
                total: r
            });
            var n = 0, i = 0, c = 0;
            for (var l in Module.dataFileDownloads) {
                var u = Module.dataFileDownloads[l];
                n += u.total, i += u.loaded, c++
            }
            n = Math.ceil(n * Module.expectedDataFileDownloads / c), Module.setStatus && Module.setStatus("Downloading data... (" + i + "/" + n + ")")
        } else Module.dataFileDownloads || Module.setStatus && Module.setStatus("Downloading data...")
    }, s.onerror = function (e) {
        throw new Error("NetworkError for: " + o)
    }, s.onload = function (e) {
        if (!(200 == s.status || 304 == s.status || 206 == s.status || 0 == s.status && s.response)) throw new Error(s.statusText + " : " + s.responseURL);
        var t = s.response;
        i(t)
    }, s.send(null)), Module.calledRun ? u() : (Module.preRun || (Module.preRun = []), Module.preRun.push(u))
}({
    files: [{filename: "/res/font/spxtrm4f.fnt", start: 0, end: 2048, audio: 0}, {
        filename: "/res/rom/dos513f.rom",
        start: 2048,
        end: 18432,
        audio: 0
    }, {filename: "/res/rom/service.rom", start: 18432, end: 34816, audio: 0}, {
        filename: "/res/rom/sos128_0.rom",
        start: 34816,
        end: 51200,
        audio: 0
    }, {filename: "/res/rom/sos128_1.rom", start: 51200, end: 67584, audio: 0}, {
        filename: "/res/rom/sos48.rom",
        start: 67584,
        end: 83968,
        audio: 0
    }, {filename: "/res/rom/test.rom", start: 83968, end: 92160, audio: 0}],
    remote_package_size: 92160,
    package_uuid: "4c80ad1e-e34f-468a-990a-3e7215bd7d87"
});
var key, moduleOverrides = {};
for (key in Module) Module.hasOwnProperty(key) && (moduleOverrides[key] = Module[key]);
var arguments_ = [], thisProgram = "./this.program", quit_ = function (e, t) {
    throw t
}, ENVIRONMENT_IS_WEB = !1, ENVIRONMENT_IS_WORKER = !1, ENVIRONMENT_IS_NODE = !1, ENVIRONMENT_IS_SHELL = !1;
ENVIRONMENT_IS_WEB = "object" == typeof window, ENVIRONMENT_IS_WORKER = "function" == typeof importScripts, ENVIRONMENT_IS_NODE = "object" == typeof process && "object" == typeof process.versions && "string" == typeof process.versions.node, ENVIRONMENT_IS_SHELL = !ENVIRONMENT_IS_WEB && !ENVIRONMENT_IS_NODE && !ENVIRONMENT_IS_WORKER;
var read_, readAsync, readBinary, setWindowTitle, nodeFS, nodePath, scriptDirectory = "";

function locateFile(e) {
    return Module.locateFile ? Module.locateFile(e, scriptDirectory) : scriptDirectory + e
}

ENVIRONMENT_IS_NODE ? (scriptDirectory = ENVIRONMENT_IS_WORKER ? require("path").dirname(scriptDirectory) + "/" : __dirname + "/", read_ = function (e, t) {
    return nodeFS || (nodeFS = require("fs")), nodePath || (nodePath = require("path")), e = nodePath.normalize(e), nodeFS.readFileSync(e, t ? null : "utf8")
}, readBinary = function (e) {
    var t = read_(e, !0);
    return t.buffer || (t = new Uint8Array(t)), assert(t.buffer), t
}, process.argv.length > 1 && (thisProgram = process.argv[1].replace(/\\/g, "/")), arguments_ = process.argv.slice(2), "undefined" != typeof module && (module.exports = Module), process.on("uncaughtException", function (e) {
    if (!(e instanceof ExitStatus)) throw e
}), process.on("unhandledRejection", abort), quit_ = function (e) {
    process.exit(e)
}, Module.inspect = function () {
    return "[Emscripten Module object]"
}) : ENVIRONMENT_IS_SHELL ? ("undefined" != typeof read && (read_ = function (e) {
    return read(e)
}), readBinary = function (e) {
    var t;
    return "function" == typeof readbuffer ? new Uint8Array(readbuffer(e)) : (assert("object" == typeof (t = read(e, "binary"))), t)
}, "undefined" != typeof scriptArgs ? arguments_ = scriptArgs : "undefined" != typeof arguments && (arguments_ = arguments), "function" == typeof quit && (quit_ = function (e) {
    quit(e)
}), "undefined" != typeof print && ("undefined" == typeof console && (console = {}), console.log = print, console.warn = console.error = "undefined" != typeof printErr ? printErr : print)) : (ENVIRONMENT_IS_WEB || ENVIRONMENT_IS_WORKER) && (ENVIRONMENT_IS_WORKER ? scriptDirectory = self.location.href : "undefined" != typeof document && document.currentScript && (scriptDirectory = document.currentScript.src), scriptDirectory = 0 !== scriptDirectory.indexOf("blob:") ? scriptDirectory.substr(0, scriptDirectory.lastIndexOf("/") + 1) : "", read_ = function (e) {
    var t = new XMLHttpRequest;
    return t.open("GET", e, !1), t.send(null), t.responseText
}, ENVIRONMENT_IS_WORKER && (readBinary = function (e) {
    var t = new XMLHttpRequest;
    return t.open("GET", e, !1), t.responseType = "arraybuffer", t.send(null), new Uint8Array(t.response)
}), readAsync = function (e, t, r) {
    var n = new XMLHttpRequest;
    n.open("GET", e, !0), n.responseType = "arraybuffer", n.onload = function () {
        200 == n.status || 0 == n.status && n.response ? t(n.response) : r()
    }, n.onerror = r, n.send(null)
}, setWindowTitle = function (e) {
    document.title = e
});
var out = Module.print || console.log.bind(console), err = Module.printErr || console.warn.bind(console);
for (key in moduleOverrides) moduleOverrides.hasOwnProperty(key) && (Module[key] = moduleOverrides[key]);
moduleOverrides = null, Module.arguments && (arguments_ = Module.arguments), Module.thisProgram && (thisProgram = Module.thisProgram), Module.quit && (quit_ = Module.quit);
var STACK_ALIGN = 16;

function alignMemory(e, t) {
    return t || (t = STACK_ALIGN), Math.ceil(e / t) * t
}

function warnOnce(e) {
    warnOnce.shown || (warnOnce.shown = {}), warnOnce.shown[e] || (warnOnce.shown[e] = 1, err(e))
}

var wasmBinary, tempRet0 = 0, setTempRet0 = function (e) {
    tempRet0 = e
};
Module.wasmBinary && (wasmBinary = Module.wasmBinary);
var wasmMemory, noExitRuntime = Module.noExitRuntime || !0;

function setValue(e, t, r, n) {
    switch ("*" === (r = r || "i8").charAt(r.length - 1) && (r = "i32"), r) {
        case"i1":
        case"i8":
            HEAP8[e >> 0] = t;
            break;
        case"i16":
            HEAP16[e >> 1] = t;
            break;
        case"i32":
            HEAP32[e >> 2] = t;
            break;
        case"i64":
            tempI64 = [t >>> 0, (tempDouble = t, +Math.abs(tempDouble) >= 1 ? tempDouble > 0 ? (0 | Math.min(+Math.floor(tempDouble / 4294967296), 4294967295)) >>> 0 : ~~+Math.ceil((tempDouble - +(~~tempDouble >>> 0)) / 4294967296) >>> 0 : 0)], HEAP32[e >> 2] = tempI64[0], HEAP32[e + 4 >> 2] = tempI64[1];
            break;
        case"float":
            HEAPF32[e >> 2] = t;
            break;
        case"double":
            HEAPF64[e >> 3] = t;
            break;
        default:
            abort("invalid type for setValue: " + r)
    }
}

"object" != typeof WebAssembly && abort("no native wasm support detected");
var EXITSTATUS, ABORT = !1;

function assert(e, t) {
    e || abort("Assertion failed: " + t)
}

function getCFunc(e) {
    var t = Module["_" + e];
    return assert(t, "Cannot call unknown function " + e + ", make sure it is exported"), t
}

function ccall(e, t, r, n, o) {
    var a = {
        string: function (e) {
            var t = 0;
            if (null != e && 0 !== e) {
                var r = 1 + (e.length << 2);
                stringToUTF8(e, t = stackAlloc(r), r)
            }
            return t
        }, array: function (e) {
            var t = stackAlloc(e.length);
            return writeArrayToMemory(e, t), t
        }
    };
    var i = getCFunc(e), s = [], c = 0;
    if (n) for (var l = 0; l < n.length; l++) {
        var u = a[r[l]];
        u ? (0 === c && (c = stackSave()), s[l] = u(n[l])) : s[l] = n[l]
    }
    var d = i.apply(null, s);
    return d = function (e) {
        return "string" === t ? UTF8ToString(e) : "boolean" === t ? Boolean(e) : e
    }(d), 0 !== c && stackRestore(c), d
}

var ALLOC_NORMAL = 0, ALLOC_STACK = 1;

function allocate(e, t) {
    var r;
    return r = t == ALLOC_STACK ? stackAlloc(e.length) : _malloc(e.length), e.subarray || e.slice ? HEAPU8.set(e, r) : HEAPU8.set(new Uint8Array(e), r), r
}

var buffer, HEAP8, HEAPU8, HEAP16, HEAPU16, HEAP32, HEAPU32, HEAPF32, HEAPF64,
    UTF8Decoder = "undefined" != typeof TextDecoder ? new TextDecoder("utf8") : void 0;

function UTF8ArrayToString(e, t, r) {
    for (var n = t + r, o = t; e[o] && !(o >= n);) ++o;
    if (o - t > 16 && e.subarray && UTF8Decoder) return UTF8Decoder.decode(e.subarray(t, o));
    for (var a = ""; t < o;) {
        var i = e[t++];
        if (128 & i) {
            var s = 63 & e[t++];
            if (192 != (224 & i)) {
                var c = 63 & e[t++];
                if ((i = 224 == (240 & i) ? (15 & i) << 12 | s << 6 | c : (7 & i) << 18 | s << 12 | c << 6 | 63 & e[t++]) < 65536) a += String.fromCharCode(i); else {
                    var l = i - 65536;
                    a += String.fromCharCode(55296 | l >> 10, 56320 | 1023 & l)
                }
            } else a += String.fromCharCode((31 & i) << 6 | s)
        } else a += String.fromCharCode(i)
    }
    return a
}

function UTF8ToString(e, t) {
    return e ? UTF8ArrayToString(HEAPU8, e, t) : ""
}

function stringToUTF8Array(e, t, r, n) {
    if (!(n > 0)) return 0;
    for (var o = r, a = r + n - 1, i = 0; i < e.length; ++i) {
        var s = e.charCodeAt(i);
        if (s >= 55296 && s <= 57343) s = 65536 + ((1023 & s) << 10) | 1023 & e.charCodeAt(++i);
        if (s <= 127) {
            if (r >= a) break;
            t[r++] = s
        } else if (s <= 2047) {
            if (r + 1 >= a) break;
            t[r++] = 192 | s >> 6, t[r++] = 128 | 63 & s
        } else if (s <= 65535) {
            if (r + 2 >= a) break;
            t[r++] = 224 | s >> 12, t[r++] = 128 | s >> 6 & 63, t[r++] = 128 | 63 & s
        } else {
            if (r + 3 >= a) break;
            t[r++] = 240 | s >> 18, t[r++] = 128 | s >> 12 & 63, t[r++] = 128 | s >> 6 & 63, t[r++] = 128 | 63 & s
        }
    }
    return t[r] = 0, r - o
}

function stringToUTF8(e, t, r) {
    return stringToUTF8Array(e, HEAPU8, t, r)
}

function lengthBytesUTF8(e) {
    for (var t = 0, r = 0; r < e.length; ++r) {
        var n = e.charCodeAt(r);
        n >= 55296 && n <= 57343 && (n = 65536 + ((1023 & n) << 10) | 1023 & e.charCodeAt(++r)), n <= 127 ? ++t : t += n <= 2047 ? 2 : n <= 65535 ? 3 : 4
    }
    return t
}

function allocateUTF8(e) {
    var t = lengthBytesUTF8(e) + 1, r = _malloc(t);
    return r && stringToUTF8Array(e, HEAP8, r, t), r
}

function allocateUTF8OnStack(e) {
    var t = lengthBytesUTF8(e) + 1, r = stackAlloc(t);
    return stringToUTF8Array(e, HEAP8, r, t), r
}

function writeArrayToMemory(e, t) {
    HEAP8.set(e, t)
}

function writeAsciiToMemory(e, t, r) {
    for (var n = 0; n < e.length; ++n) HEAP8[t++ >> 0] = e.charCodeAt(n);
    r || (HEAP8[t >> 0] = 0)
}

function updateGlobalBufferAndViews(e) {
    buffer = e, Module.HEAP8 = HEAP8 = new Int8Array(e), Module.HEAP16 = HEAP16 = new Int16Array(e), Module.HEAP32 = HEAP32 = new Int32Array(e), Module.HEAPU8 = HEAPU8 = new Uint8Array(e), Module.HEAPU16 = HEAPU16 = new Uint16Array(e), Module.HEAPU32 = HEAPU32 = new Uint32Array(e), Module.HEAPF32 = HEAPF32 = new Float32Array(e), Module.HEAPF64 = HEAPF64 = new Float64Array(e)
}

var wasmTable, INITIAL_MEMORY = Module.INITIAL_MEMORY || 16777216, __ATPRERUN__ = [], __ATINIT__ = [], __ATMAIN__ = [],
    __ATEXIT__ = [], __ATPOSTRUN__ = [], runtimeInitialized = !1, runtimeExited = !1;

function preRun() {
    if (Module.preRun) for ("function" == typeof Module.preRun && (Module.preRun = [Module.preRun]); Module.preRun.length;) addOnPreRun(Module.preRun.shift());
    callRuntimeCallbacks(__ATPRERUN__)
}

function initRuntime() {
    runtimeInitialized = !0, Module.noFSInit || FS.init.initialized || FS.init(), TTY.init(), callRuntimeCallbacks(__ATINIT__)
}

function preMain() {
    FS.ignorePermissions = !1, callRuntimeCallbacks(__ATMAIN__)
}

function exitRuntime() {
    runtimeExited = !0
}

function postRun() {
    if (Module.postRun) for ("function" == typeof Module.postRun && (Module.postRun = [Module.postRun]); Module.postRun.length;) addOnPostRun(Module.postRun.shift());
    callRuntimeCallbacks(__ATPOSTRUN__)
}

function addOnPreRun(e) {
    __ATPRERUN__.unshift(e)
}

function addOnPostRun(e) {
    __ATPOSTRUN__.unshift(e)
}

__ATINIT__.push({
    func: function () {
        ___wasm_call_ctors()
    }
});
var runDependencies = 0, runDependencyWatcher = null, dependenciesFulfilled = null;

function getUniqueRunDependency(e) {
    return e
}

function addRunDependency(e) {
    runDependencies++, Module.monitorRunDependencies && Module.monitorRunDependencies(runDependencies)
}

function removeRunDependency(e) {
    if (runDependencies--, Module.monitorRunDependencies && Module.monitorRunDependencies(runDependencies), 0 == runDependencies && (null !== runDependencyWatcher && (clearInterval(runDependencyWatcher), runDependencyWatcher = null), dependenciesFulfilled)) {
        var t = dependenciesFulfilled;
        dependenciesFulfilled = null, t()
    }
}

function abort(e) {
    throw Module.onAbort && Module.onAbort(e), err(e += ""), ABORT = !0, EXITSTATUS = 1, e = "abort(" + e + "). Build with -s ASSERTIONS=1 for more info.", new WebAssembly.RuntimeError(e)
}

function hasPrefix(e, t) {
    return String.prototype.startsWith ? e.startsWith(t) : 0 === e.indexOf(t)
}

Module.preloadedImages = {}, Module.preloadedAudios = {};
var dataURIPrefix = "data:application/octet-stream;base64,";

function isDataURI(e) {
    return hasPrefix(e, dataURIPrefix)
}

var fileURIPrefix = "file://";

function isFileURI(e) {
    return hasPrefix(e, fileURIPrefix)
}

var tempDouble, tempI64, wasmBinaryFile = "unreal_speccy_portable.wasm";

function getBinary(e) {
    try {
        if (e == wasmBinaryFile && wasmBinary) return new Uint8Array(wasmBinary);
        if (readBinary) return readBinary(e);
        throw"both async and sync fetching of the wasm failed"
    } catch (e) {
        abort(e)
    }
}

function getBinaryPromise() {
    if (!wasmBinary && (ENVIRONMENT_IS_WEB || ENVIRONMENT_IS_WORKER)) {
        if ("function" == typeof fetch && !isFileURI(wasmBinaryFile)) return fetch(wasmBinaryFile, {credentials: "same-origin"}).then(function (e) {
            if (!e.ok) throw"failed to load wasm binary file at '" + wasmBinaryFile + "'";
            return e.arrayBuffer()
        }).catch(function () {
            return getBinary(wasmBinaryFile)
        });
        if (readAsync) return new Promise(function (e, t) {
            readAsync(wasmBinaryFile, function (t) {
                e(new Uint8Array(t))
            }, t)
        })
    }
    return Promise.resolve().then(function () {
        return getBinary(wasmBinaryFile)
    })
}

function createWasm() {
    var e = {a: asmLibraryArg};

    function t(e, t) {
        var r = e.exports;
        Module.asm = r, updateGlobalBufferAndViews((wasmMemory = Module.asm.je).buffer), wasmTable = Module.asm.le, removeRunDependency("wasm-instantiate")
    }

    function r(e) {
        t(e.instance)
    }

    function n(t) {
        return getBinaryPromise().then(function (t) {
            return WebAssembly.instantiate(t, e)
        }).then(t, function (e) {
            err("failed to asynchronously prepare wasm: " + e), abort(e)
        })
    }

    if (addRunDependency("wasm-instantiate"), Module.instantiateWasm) try {
        return Module.instantiateWasm(e, t)
    } catch (e) {
        return err("Module.instantiateWasm callback failed with error: " + e), !1
    }
    return wasmBinary || "function" != typeof WebAssembly.instantiateStreaming || isDataURI(wasmBinaryFile) || isFileURI(wasmBinaryFile) || "function" != typeof fetch ? n(r) : fetch(wasmBinaryFile, {credentials: "same-origin"}).then(function (t) {
        return WebAssembly.instantiateStreaming(t, e).then(r, function (e) {
            return err("wasm streaming compile failed: " + e), err("falling back to ArrayBuffer instantiation"), n(r)
        })
    }), {}
}

isDataURI(wasmBinaryFile) || (wasmBinaryFile = locateFile(wasmBinaryFile));
var ASM_CONSTS = {
    166548: function () {
        Module.onReady()
    }, 166566: function (e) {
        var t = UTF8ToString(e) + "\n\nAbort/Retry/Ignore/AlwaysIgnore? [ariA] :", r = window.prompt(t, "i");
        return null === r && (r = "i"), allocate(intArrayFromString(r), "i8", ALLOC_NORMAL)
    }, 166791: function (e, t, r) {
        var n = e, o = t, a = r;
        Module.SDL2 || (Module.SDL2 = {});
        var i = Module.SDL2;
        i.ctxCanvas !== Module.canvas && (i.ctx = Module.createContext(Module.canvas, !1, !0), i.ctxCanvas = Module.canvas), i.w === n && i.h === o && i.imageCtx === i.ctx || (i.image = i.ctx.createImageData(n, o), i.w = n, i.h = o, i.imageCtx = i.ctx);
        var s, c = i.image.data, l = a >> 2, u = 0;
        if ("undefined" != typeof CanvasPixelArray && c instanceof CanvasPixelArray) for (s = c.length; u < s;) {
            var d = HEAP32[l];
            c[u] = 255 & d, c[u + 1] = d >> 8 & 255, c[u + 2] = d >> 16 & 255, c[u + 3] = 255, l++, u += 4
        } else {
            i.data32Data !== c && (i.data32 = new Int32Array(c.buffer), i.data8 = new Uint8Array(c.buffer));
            var f = i.data32;
            s = f.length, f.set(HEAP32.subarray(l, l + s));
            var m = i.data8, p = 3, _ = p + 4 * s;
            if (s % 8 == 0) for (; p < _;) m[p] = 255, m[p = p + 4 | 0] = 255, m[p = p + 4 | 0] = 255, m[p = p + 4 | 0] = 255, m[p = p + 4 | 0] = 255, m[p = p + 4 | 0] = 255, m[p = p + 4 | 0] = 255, m[p = p + 4 | 0] = 255, p = p + 4 | 0; else for (; p < _;) m[p] = 255, p = p + 4 | 0
        }
        return i.ctx.putImageData(i.image, 0, 0), 0
    }, 168246: function (e, t, r, n, o) {
        var a = e, i = t, s = r, c = n, l = o, u = document.createElement("canvas");
        u.width = a, u.height = i;
        var d, f = u.getContext("2d"), m = f.createImageData(a, i), p = m.data, _ = l >> 2, g = 0;
        if ("undefined" != typeof CanvasPixelArray && p instanceof CanvasPixelArray) for (d = p.length; g < d;) {
            var v = HEAP32[_];
            p[g] = 255 & v, p[g + 1] = v >> 8 & 255, p[g + 2] = v >> 16 & 255, p[g + 3] = v >> 24 & 255, _++, g += 4
        } else {
            var E = new Int32Array(p.buffer);
            d = E.length, E.set(HEAP32.subarray(_, _ + d))
        }
        f.putImageData(m, 0, 0);
        var h = 0 === s && 0 === c ? "url(" + u.toDataURL() + "), auto" : "url(" + u.toDataURL() + ") " + s + " " + c + ", auto",
            S = _malloc(h.length + 1);
        return stringToUTF8(h, S, h.length + 1), S
    }, 169235: function (e) {
        return Module.canvas && (Module.canvas.style.cursor = UTF8ToString(e)), 0
    }, 169328: function () {
        Module.canvas && (Module.canvas.style.cursor = "none")
    }, 169397: function () {
        return screen.width
    }, 169422: function () {
        return screen.height
    }, 169448: function () {
        return window.innerWidth
    }, 169478: function () {
        return window.innerHeight
    }, 169509: function (e) {
        return void 0 !== setWindowTitle && setWindowTitle(UTF8ToString(e)), 0
    }, 169604: function () {
        return "undefined" != typeof AudioContext ? 1 : "undefined" != typeof webkitAudioContext ? 1 : 0
    }, 169741: function () {
        return void 0 !== navigator.mediaDevices && void 0 !== navigator.mediaDevices.getUserMedia ? 1 : void 0 !== navigator.webkitGetUserMedia ? 1 : 0
    }, 169965: function (e) {
        void 0 === Module.SDL2 && (Module.SDL2 = {});
        var t = Module.SDL2;
        return e ? t.capture = {} : t.audio = {}, t.audioContext || ("undefined" != typeof AudioContext ? t.audioContext = new AudioContext : "undefined" != typeof webkitAudioContext && (t.audioContext = new webkitAudioContext), t.audioContext && autoResumeAudioContext(t.audioContext)), void 0 === t.audioContext ? -1 : 0
    }, 170458: function () {
        return Module.SDL2.audioContext.sampleRate
    }, 170526: function (e, t, r, n) {
        var o = Module.SDL2, a = function (a) {
            void 0 !== o.capture.silenceTimer && (clearTimeout(o.capture.silenceTimer), o.capture.silenceTimer = void 0), o.capture.mediaStreamNode = o.audioContext.createMediaStreamSource(a), o.capture.scriptProcessorNode = o.audioContext.createScriptProcessor(t, e, 1), o.capture.scriptProcessorNode.onaudioprocess = function (e) {
                void 0 !== o && void 0 !== o.capture && (e.outputBuffer.getChannelData(0).fill(0), o.capture.currentCaptureBuffer = e.inputBuffer, dynCall("vi", r, [n]))
            }, o.capture.mediaStreamNode.connect(o.capture.scriptProcessorNode), o.capture.scriptProcessorNode.connect(o.audioContext.destination), o.capture.stream = a
        }, i = function (e) {
        };
        o.capture.silenceBuffer = o.audioContext.createBuffer(e, t, o.audioContext.sampleRate), o.capture.silenceBuffer.getChannelData(0).fill(0);
        o.capture.silenceTimer = setTimeout(function () {
            o.capture.currentCaptureBuffer = o.capture.silenceBuffer, dynCall("vi", r, [n])
        }, t / o.audioContext.sampleRate * 1e3), void 0 !== navigator.mediaDevices && void 0 !== navigator.mediaDevices.getUserMedia ? navigator.mediaDevices.getUserMedia({
            audio: !0,
            video: !1
        }).then(a).catch(i) : void 0 !== navigator.webkitGetUserMedia && navigator.webkitGetUserMedia({
            audio: !0,
            video: !1
        }, a, i)
    }, 172178: function (e, t, r, n) {
        var o = Module.SDL2;
        o.audio.scriptProcessorNode = o.audioContext.createScriptProcessor(t, 0, e), o.audio.scriptProcessorNode.onaudioprocess = function (e) {
            void 0 !== o && void 0 !== o.audio && (o.audio.currentOutputBuffer = e.outputBuffer, dynCall("vi", r, [n]))
        }, o.audio.scriptProcessorNode.connect(o.audioContext.destination)
    }, 172588: function (e, t) {
        for (var r = Module.SDL2, n = r.capture.currentCaptureBuffer.numberOfChannels, o = 0; o < n; ++o) {
            var a = r.capture.currentCaptureBuffer.getChannelData(o);
            if (a.length != t) throw"Web Audio capture buffer length mismatch! Destination size: " + a.length + " samples vs expected " + t + " samples!";
            if (1 == n) for (var i = 0; i < t; ++i) setValue(e + 4 * i, a[i], "float"); else for (i = 0; i < t; ++i) setValue(e + 4 * (i * n + o), a[i], "float")
        }
    }, 173193: function (e, t) {
        for (var r = Module.SDL2, n = r.audio.currentOutputBuffer.numberOfChannels, o = 0; o < n; ++o) {
            var a = r.audio.currentOutputBuffer.getChannelData(o);
            if (a.length != t) throw"Web Audio output buffer length mismatch! Destination size: " + a.length + " samples vs expected " + t + " samples!";
            for (var i = 0; i < t; ++i) a[i] = HEAPF32[e + (i * n + o << 2) >> 2]
        }
    }, 173673: function (e) {
        var t = Module.SDL2;
        if (e) {
            if (void 0 !== t.capture.silenceTimer && clearTimeout(t.capture.silenceTimer), void 0 !== t.capture.stream) {
                for (var r = t.capture.stream.getAudioTracks(), n = 0; n < r.length; n++) t.capture.stream.removeTrack(r[n]);
                t.capture.stream = void 0
            }
            void 0 !== t.capture.scriptProcessorNode && (t.capture.scriptProcessorNode.onaudioprocess = function (e) {
            }, t.capture.scriptProcessorNode.disconnect(), t.capture.scriptProcessorNode = void 0), void 0 !== t.capture.mediaStreamNode && (t.capture.mediaStreamNode.disconnect(), t.capture.mediaStreamNode = void 0), void 0 !== t.capture.silenceBuffer && (t.capture.silenceBuffer = void 0), t.capture = void 0
        } else null != t.audio.scriptProcessorNode && (t.audio.scriptProcessorNode.disconnect(), t.audio.scriptProcessorNode = void 0), t.audio = void 0;
        void 0 !== t.audioContext && void 0 === t.audio && void 0 === t.capture && (t.audioContext.close(), t.audioContext = void 0)
    }
};

function listenOnce(e, t, r) {
    e.addEventListener(t, r, {once: !0})
}

function autoResumeAudioContext(e, t) {
    t || (t = [document, document.getElementById("canvas")]), ["keydown", "mousedown", "touchstart"].forEach(function (r) {
        t.forEach(function (t) {
            t && listenOnce(t, r, function () {
                "suspended" === e.state && e.resume()
            })
        })
    })
}

function callRuntimeCallbacks(e) {
    for (; e.length > 0;) {
        var t = e.shift();
        if ("function" != typeof t) {
            var r = t.func;
            "number" == typeof r ? void 0 === t.arg ? wasmTable.get(r)() : wasmTable.get(r)(t.arg) : r(void 0 === t.arg ? null : t.arg)
        } else t(Module)
    }
}

function dynCallLegacy(e, t, r) {
    var n = Module["dynCall_" + e];
    return r && r.length ? n.apply(null, [t].concat(r)) : n.call(null, t)
}

function dynCall(e, t, r) {
    return -1 != e.indexOf("j") ? dynCallLegacy(e, t, r) : wasmTable.get(t).apply(null, r)
}

function setErrNo(e) {
    return HEAP32[___errno_location() >> 2] = e, e
}

var PATH = {
    splitPath: function (e) {
        return /^(\/?|)([\s\S]*?)((?:\.{1,2}|[^\/]+?|)(\.[^.\/]*|))(?:[\/]*)$/.exec(e).slice(1)
    }, normalizeArray: function (e, t) {
        for (var r = 0, n = e.length - 1; n >= 0; n--) {
            var o = e[n];
            "." === o ? e.splice(n, 1) : ".." === o ? (e.splice(n, 1), r++) : r && (e.splice(n, 1), r--)
        }
        if (t) for (; r; r--) e.unshift("..");
        return e
    }, normalize: function (e) {
        var t = "/" === e.charAt(0), r = "/" === e.substr(-1);
        return (e = PATH.normalizeArray(e.split("/").filter(function (e) {
            return !!e
        }), !t).join("/")) || t || (e = "."), e && r && (e += "/"), (t ? "/" : "") + e
    }, dirname: function (e) {
        var t = PATH.splitPath(e), r = t[0], n = t[1];
        return r || n ? (n && (n = n.substr(0, n.length - 1)), r + n) : "."
    }, basename: function (e) {
        if ("/" === e) return "/";
        var t = (e = (e = PATH.normalize(e)).replace(/\/$/, "")).lastIndexOf("/");
        return -1 === t ? e : e.substr(t + 1)
    }, extname: function (e) {
        return PATH.splitPath(e)[3]
    }, join: function () {
        var e = Array.prototype.slice.call(arguments, 0);
        return PATH.normalize(e.join("/"))
    }, join2: function (e, t) {
        return PATH.normalize(e + "/" + t)
    }
};

function getRandomDevice() {
    if ("object" == typeof crypto && "function" == typeof crypto.getRandomValues) {
        var e = new Uint8Array(1);
        return function () {
            return crypto.getRandomValues(e), e[0]
        }
    }
    if (ENVIRONMENT_IS_NODE) try {
        var t = require("crypto");
        return function () {
            return t.randomBytes(1)[0]
        }
    } catch (e) {
    }
    return function () {
        abort("randomDevice")
    }
}

var PATH_FS = {
    resolve: function () {
        for (var e = "", t = !1, r = arguments.length - 1; r >= -1 && !t; r--) {
            var n = r >= 0 ? arguments[r] : FS.cwd();
            if ("string" != typeof n) throw new TypeError("Arguments to path.resolve must be strings");
            if (!n) return "";
            e = n + "/" + e, t = "/" === n.charAt(0)
        }
        return (t ? "/" : "") + (e = PATH.normalizeArray(e.split("/").filter(function (e) {
            return !!e
        }), !t).join("/")) || "."
    }, relative: function (e, t) {
        function r(e) {
            for (var t = 0; t < e.length && "" === e[t]; t++) ;
            for (var r = e.length - 1; r >= 0 && "" === e[r]; r--) ;
            return t > r ? [] : e.slice(t, r - t + 1)
        }

        e = PATH_FS.resolve(e).substr(1), t = PATH_FS.resolve(t).substr(1);
        for (var n = r(e.split("/")), o = r(t.split("/")), a = Math.min(n.length, o.length), i = a, s = 0; s < a; s++) if (n[s] !== o[s]) {
            i = s;
            break
        }
        var c = [];
        for (s = i; s < n.length; s++) c.push("..");
        return (c = c.concat(o.slice(i))).join("/")
    }
}, TTY = {
    ttys: [], init: function () {
    }, shutdown: function () {
    }, register: function (e, t) {
        TTY.ttys[e] = {input: [], output: [], ops: t}, FS.registerDevice(e, TTY.stream_ops)
    }, stream_ops: {
        open: function (e) {
            var t = TTY.ttys[e.node.rdev];
            if (!t) throw new FS.ErrnoError(43);
            e.tty = t, e.seekable = !1
        }, close: function (e) {
            e.tty.ops.flush(e.tty)
        }, flush: function (e) {
            e.tty.ops.flush(e.tty)
        }, read: function (e, t, r, n, o) {
            if (!e.tty || !e.tty.ops.get_char) throw new FS.ErrnoError(60);
            for (var a = 0, i = 0; i < n; i++) {
                var s;
                try {
                    s = e.tty.ops.get_char(e.tty)
                } catch (e) {
                    throw new FS.ErrnoError(29)
                }
                if (void 0 === s && 0 === a) throw new FS.ErrnoError(6);
                if (null == s) break;
                a++, t[r + i] = s
            }
            return a && (e.node.timestamp = Date.now()), a
        }, write: function (e, t, r, n, o) {
            if (!e.tty || !e.tty.ops.put_char) throw new FS.ErrnoError(60);
            try {
                for (var a = 0; a < n; a++) e.tty.ops.put_char(e.tty, t[r + a])
            } catch (e) {
                throw new FS.ErrnoError(29)
            }
            return n && (e.node.timestamp = Date.now()), a
        }
    }, default_tty_ops: {
        get_char: function (e) {
            if (!e.input.length) {
                var t = null;
                if (ENVIRONMENT_IS_NODE) {
                    var r = Buffer.alloc ? Buffer.alloc(256) : new Buffer(256), n = 0;
                    try {
                        n = nodeFS.readSync(process.stdin.fd, r, 0, 256, null)
                    } catch (e) {
                        if (-1 == e.toString().indexOf("EOF")) throw e;
                        n = 0
                    }
                    t = n > 0 ? r.slice(0, n).toString("utf-8") : null
                } else "undefined" != typeof window && "function" == typeof window.prompt ? null !== (t = window.prompt("Input: ")) && (t += "\n") : "function" == typeof readline && null !== (t = readline()) && (t += "\n");
                if (!t) return null;
                e.input = intArrayFromString(t, !0)
            }
            return e.input.shift()
        }, put_char: function (e, t) {
            null === t || 10 === t ? (out(UTF8ArrayToString(e.output, 0)), e.output = []) : 0 != t && e.output.push(t)
        }, flush: function (e) {
            e.output && e.output.length > 0 && (out(UTF8ArrayToString(e.output, 0)), e.output = [])
        }
    }, default_tty1_ops: {
        put_char: function (e, t) {
            null === t || 10 === t ? (err(UTF8ArrayToString(e.output, 0)), e.output = []) : 0 != t && e.output.push(t)
        }, flush: function (e) {
            e.output && e.output.length > 0 && (err(UTF8ArrayToString(e.output, 0)), e.output = [])
        }
    }
};

function mmapAlloc(e) {
    for (var t = alignMemory(e, 16384), r = _malloc(t); e < t;) HEAP8[r + e++] = 0;
    return r
}

var _emscripten_get_now, MEMFS = {
    ops_table: null, mount: function (e) {
        return MEMFS.createNode(null, "/", 16895, 0)
    }, createNode: function (e, t, r, n) {
        if (FS.isBlkdev(r) || FS.isFIFO(r)) throw new FS.ErrnoError(63);
        MEMFS.ops_table || (MEMFS.ops_table = {
            dir: {
                node: {
                    getattr: MEMFS.node_ops.getattr,
                    setattr: MEMFS.node_ops.setattr,
                    lookup: MEMFS.node_ops.lookup,
                    mknod: MEMFS.node_ops.mknod,
                    rename: MEMFS.node_ops.rename,
                    unlink: MEMFS.node_ops.unlink,
                    rmdir: MEMFS.node_ops.rmdir,
                    readdir: MEMFS.node_ops.readdir,
                    symlink: MEMFS.node_ops.symlink
                }, stream: {llseek: MEMFS.stream_ops.llseek}
            },
            file: {
                node: {getattr: MEMFS.node_ops.getattr, setattr: MEMFS.node_ops.setattr},
                stream: {
                    llseek: MEMFS.stream_ops.llseek,
                    read: MEMFS.stream_ops.read,
                    write: MEMFS.stream_ops.write,
                    allocate: MEMFS.stream_ops.allocate,
                    mmap: MEMFS.stream_ops.mmap,
                    msync: MEMFS.stream_ops.msync
                }
            },
            link: {
                node: {
                    getattr: MEMFS.node_ops.getattr,
                    setattr: MEMFS.node_ops.setattr,
                    readlink: MEMFS.node_ops.readlink
                }, stream: {}
            },
            chrdev: {
                node: {getattr: MEMFS.node_ops.getattr, setattr: MEMFS.node_ops.setattr},
                stream: FS.chrdev_stream_ops
            }
        });
        var o = FS.createNode(e, t, r, n);
        return FS.isDir(o.mode) ? (o.node_ops = MEMFS.ops_table.dir.node, o.stream_ops = MEMFS.ops_table.dir.stream, o.contents = {}) : FS.isFile(o.mode) ? (o.node_ops = MEMFS.ops_table.file.node, o.stream_ops = MEMFS.ops_table.file.stream, o.usedBytes = 0, o.contents = null) : FS.isLink(o.mode) ? (o.node_ops = MEMFS.ops_table.link.node, o.stream_ops = MEMFS.ops_table.link.stream) : FS.isChrdev(o.mode) && (o.node_ops = MEMFS.ops_table.chrdev.node, o.stream_ops = MEMFS.ops_table.chrdev.stream), o.timestamp = Date.now(), e && (e.contents[t] = o, e.timestamp = o.timestamp), o
    }, getFileDataAsTypedArray: function (e) {
        return e.contents ? e.contents.subarray ? e.contents.subarray(0, e.usedBytes) : new Uint8Array(e.contents) : new Uint8Array(0)
    }, expandFileStorage: function (e, t) {
        var r = e.contents ? e.contents.length : 0;
        if (!(r >= t)) {
            t = Math.max(t, r * (r < 1048576 ? 2 : 1.125) >>> 0), 0 != r && (t = Math.max(t, 256));
            var n = e.contents;
            e.contents = new Uint8Array(t), e.usedBytes > 0 && e.contents.set(n.subarray(0, e.usedBytes), 0)
        }
    }, resizeFileStorage: function (e, t) {
        if (e.usedBytes != t) if (0 == t) e.contents = null, e.usedBytes = 0; else {
            var r = e.contents;
            e.contents = new Uint8Array(t), r && e.contents.set(r.subarray(0, Math.min(t, e.usedBytes))), e.usedBytes = t
        }
    }, node_ops: {
        getattr: function (e) {
            var t = {};
            return t.dev = FS.isChrdev(e.mode) ? e.id : 1, t.ino = e.id, t.mode = e.mode, t.nlink = 1, t.uid = 0, t.gid = 0, t.rdev = e.rdev, FS.isDir(e.mode) ? t.size = 4096 : FS.isFile(e.mode) ? t.size = e.usedBytes : FS.isLink(e.mode) ? t.size = e.link.length : t.size = 0, t.atime = new Date(e.timestamp), t.mtime = new Date(e.timestamp), t.ctime = new Date(e.timestamp), t.blksize = 4096, t.blocks = Math.ceil(t.size / t.blksize), t
        }, setattr: function (e, t) {
            void 0 !== t.mode && (e.mode = t.mode), void 0 !== t.timestamp && (e.timestamp = t.timestamp), void 0 !== t.size && MEMFS.resizeFileStorage(e, t.size)
        }, lookup: function (e, t) {
            throw FS.genericErrors[44]
        }, mknod: function (e, t, r, n) {
            return MEMFS.createNode(e, t, r, n)
        }, rename: function (e, t, r) {
            if (FS.isDir(e.mode)) {
                var n;
                try {
                    n = FS.lookupNode(t, r)
                } catch (e) {
                }
                if (n) for (var o in n.contents) throw new FS.ErrnoError(55)
            }
            delete e.parent.contents[e.name], e.parent.timestamp = Date.now(), e.name = r, t.contents[r] = e, t.timestamp = e.parent.timestamp, e.parent = t
        }, unlink: function (e, t) {
            delete e.contents[t], e.timestamp = Date.now()
        }, rmdir: function (e, t) {
            var r = FS.lookupNode(e, t);
            for (var n in r.contents) throw new FS.ErrnoError(55);
            delete e.contents[t], e.timestamp = Date.now()
        }, readdir: function (e) {
            var t = [".", ".."];
            for (var r in e.contents) e.contents.hasOwnProperty(r) && t.push(r);
            return t
        }, symlink: function (e, t, r) {
            var n = MEMFS.createNode(e, t, 41471, 0);
            return n.link = r, n
        }, readlink: function (e) {
            if (!FS.isLink(e.mode)) throw new FS.ErrnoError(28);
            return e.link
        }
    }, stream_ops: {
        read: function (e, t, r, n, o) {
            var a = e.node.contents;
            if (o >= e.node.usedBytes) return 0;
            var i = Math.min(e.node.usedBytes - o, n);
            if (i > 8 && a.subarray) t.set(a.subarray(o, o + i), r); else for (var s = 0; s < i; s++) t[r + s] = a[o + s];
            return i
        }, write: function (e, t, r, n, o, a) {
            if (!n) return 0;
            var i = e.node;
            if (i.timestamp = Date.now(), t.subarray && (!i.contents || i.contents.subarray)) {
                if (a) return i.contents = t.subarray(r, r + n), i.usedBytes = n, n;
                if (0 === i.usedBytes && 0 === o) return i.contents = t.slice(r, r + n), i.usedBytes = n, n;
                if (o + n <= i.usedBytes) return i.contents.set(t.subarray(r, r + n), o), n
            }
            if (MEMFS.expandFileStorage(i, o + n), i.contents.subarray && t.subarray) i.contents.set(t.subarray(r, r + n), o); else for (var s = 0; s < n; s++) i.contents[o + s] = t[r + s];
            return i.usedBytes = Math.max(i.usedBytes, o + n), n
        }, llseek: function (e, t, r) {
            var n = t;
            if (1 === r ? n += e.position : 2 === r && FS.isFile(e.node.mode) && (n += e.node.usedBytes), n < 0) throw new FS.ErrnoError(28);
            return n
        }, allocate: function (e, t, r) {
            MEMFS.expandFileStorage(e.node, t + r), e.node.usedBytes = Math.max(e.node.usedBytes, t + r)
        }, mmap: function (e, t, r, n, o, a) {
            if (0 !== t) throw new FS.ErrnoError(28);
            if (!FS.isFile(e.node.mode)) throw new FS.ErrnoError(43);
            var i, s, c = e.node.contents;
            if (2 & a || c.buffer !== buffer) {
                if ((n > 0 || n + r < c.length) && (c = c.subarray ? c.subarray(n, n + r) : Array.prototype.slice.call(c, n, n + r)), s = !0, !(i = mmapAlloc(r))) throw new FS.ErrnoError(48);
                HEAP8.set(c, i)
            } else s = !1, i = c.byteOffset;
            return {ptr: i, allocated: s}
        }, msync: function (e, t, r, n, o) {
            if (!FS.isFile(e.node.mode)) throw new FS.ErrnoError(43);
            if (2 & o) return 0;
            MEMFS.stream_ops.write(e, t, 0, n, r, !1);
            return 0
        }
    }
}, FS = {
    root: null,
    mounts: [],
    devices: {},
    streams: [],
    nextInode: 1,
    nameTable: null,
    currentPath: "/",
    initialized: !1,
    ignorePermissions: !0,
    trackingDelegate: {},
    tracking: {openFlags: {READ: 1, WRITE: 2}},
    ErrnoError: null,
    genericErrors: {},
    filesystems: null,
    syncFSRequests: 0,
    lookupPath: function (e, t) {
        if (t = t || {}, !(e = PATH_FS.resolve(FS.cwd(), e))) return {path: "", node: null};
        var r = {follow_mount: !0, recurse_count: 0};
        for (var n in r) void 0 === t[n] && (t[n] = r[n]);
        if (t.recurse_count > 8) throw new FS.ErrnoError(32);
        for (var o = PATH.normalizeArray(e.split("/").filter(function (e) {
            return !!e
        }), !1), a = FS.root, i = "/", s = 0; s < o.length; s++) {
            var c = s === o.length - 1;
            if (c && t.parent) break;
            if (a = FS.lookupNode(a, o[s]), i = PATH.join2(i, o[s]), FS.isMountpoint(a) && (!c || c && t.follow_mount) && (a = a.mounted.root), !c || t.follow) for (var l = 0; FS.isLink(a.mode);) {
                var u = FS.readlink(i);
                if (i = PATH_FS.resolve(PATH.dirname(i), u), a = FS.lookupPath(i, {recurse_count: t.recurse_count}).node, l++ > 40) throw new FS.ErrnoError(32)
            }
        }
        return {path: i, node: a}
    },
    getPath: function (e) {
        for (var t; ;) {
            if (FS.isRoot(e)) {
                var r = e.mount.mountpoint;
                return t ? "/" !== r[r.length - 1] ? r + "/" + t : r + t : r
            }
            t = t ? e.name + "/" + t : e.name, e = e.parent
        }
    },
    hashName: function (e, t) {
        for (var r = 0, n = 0; n < t.length; n++) r = (r << 5) - r + t.charCodeAt(n) | 0;
        return (e + r >>> 0) % FS.nameTable.length
    },
    hashAddNode: function (e) {
        var t = FS.hashName(e.parent.id, e.name);
        e.name_next = FS.nameTable[t], FS.nameTable[t] = e
    },
    hashRemoveNode: function (e) {
        var t = FS.hashName(e.parent.id, e.name);
        if (FS.nameTable[t] === e) FS.nameTable[t] = e.name_next; else for (var r = FS.nameTable[t]; r;) {
            if (r.name_next === e) {
                r.name_next = e.name_next;
                break
            }
            r = r.name_next
        }
    },
    lookupNode: function (e, t) {
        var r = FS.mayLookup(e);
        if (r) throw new FS.ErrnoError(r, e);
        for (var n = FS.hashName(e.id, t), o = FS.nameTable[n]; o; o = o.name_next) {
            var a = o.name;
            if (o.parent.id === e.id && a === t) return o
        }
        return FS.lookup(e, t)
    },
    createNode: function (e, t, r, n) {
        var o = new FS.FSNode(e, t, r, n);
        return FS.hashAddNode(o), o
    },
    destroyNode: function (e) {
        FS.hashRemoveNode(e)
    },
    isRoot: function (e) {
        return e === e.parent
    },
    isMountpoint: function (e) {
        return !!e.mounted
    },
    isFile: function (e) {
        return 32768 == (61440 & e)
    },
    isDir: function (e) {
        return 16384 == (61440 & e)
    },
    isLink: function (e) {
        return 40960 == (61440 & e)
    },
    isChrdev: function (e) {
        return 8192 == (61440 & e)
    },
    isBlkdev: function (e) {
        return 24576 == (61440 & e)
    },
    isFIFO: function (e) {
        return 4096 == (61440 & e)
    },
    isSocket: function (e) {
        return 49152 == (49152 & e)
    },
    flagModes: {r: 0, "r+": 2, w: 577, "w+": 578, a: 1089, "a+": 1090},
    modeStringToFlags: function (e) {
        var t = FS.flagModes[e];
        if (void 0 === t) throw new Error("Unknown file open mode: " + e);
        return t
    },
    flagsToPermissionString: function (e) {
        var t = ["r", "w", "rw"][3 & e];
        return 512 & e && (t += "w"), t
    },
    nodePermissions: function (e, t) {
        return FS.ignorePermissions ? 0 : (-1 === t.indexOf("r") || 292 & e.mode) && (-1 === t.indexOf("w") || 146 & e.mode) && (-1 === t.indexOf("x") || 73 & e.mode) ? 0 : 2
    },
    mayLookup: function (e) {
        var t = FS.nodePermissions(e, "x");
        return t || (e.node_ops.lookup ? 0 : 2)
    },
    mayCreate: function (e, t) {
        try {
            FS.lookupNode(e, t);
            return 20
        } catch (e) {
        }
        return FS.nodePermissions(e, "wx")
    },
    mayDelete: function (e, t, r) {
        var n;
        try {
            n = FS.lookupNode(e, t)
        } catch (e) {
            return e.errno
        }
        var o = FS.nodePermissions(e, "wx");
        if (o) return o;
        if (r) {
            if (!FS.isDir(n.mode)) return 54;
            if (FS.isRoot(n) || FS.getPath(n) === FS.cwd()) return 10
        } else if (FS.isDir(n.mode)) return 31;
        return 0
    },
    mayOpen: function (e, t) {
        return e ? FS.isLink(e.mode) ? 32 : FS.isDir(e.mode) && ("r" !== FS.flagsToPermissionString(t) || 512 & t) ? 31 : FS.nodePermissions(e, FS.flagsToPermissionString(t)) : 44
    },
    MAX_OPEN_FDS: 4096,
    nextfd: function (e, t) {
        e = e || 0, t = t || FS.MAX_OPEN_FDS;
        for (var r = e; r <= t; r++) if (!FS.streams[r]) return r;
        throw new FS.ErrnoError(33)
    },
    getStream: function (e) {
        return FS.streams[e]
    },
    createStream: function (e, t, r) {
        FS.FSStream || (FS.FSStream = function () {
        }, FS.FSStream.prototype = {
            object: {
                get: function () {
                    return this.node
                }, set: function (e) {
                    this.node = e
                }
            }, isRead: {
                get: function () {
                    return 1 != (2097155 & this.flags)
                }
            }, isWrite: {
                get: function () {
                    return 0 != (2097155 & this.flags)
                }
            }, isAppend: {
                get: function () {
                    return 1024 & this.flags
                }
            }
        });
        var n = new FS.FSStream;
        for (var o in e) n[o] = e[o];
        e = n;
        var a = FS.nextfd(t, r);
        return e.fd = a, FS.streams[a] = e, e
    },
    closeStream: function (e) {
        FS.streams[e] = null
    },
    chrdev_stream_ops: {
        open: function (e) {
            var t = FS.getDevice(e.node.rdev);
            e.stream_ops = t.stream_ops, e.stream_ops.open && e.stream_ops.open(e)
        }, llseek: function () {
            throw new FS.ErrnoError(70)
        }
    },
    major: function (e) {
        return e >> 8
    },
    minor: function (e) {
        return 255 & e
    },
    makedev: function (e, t) {
        return e << 8 | t
    },
    registerDevice: function (e, t) {
        FS.devices[e] = {stream_ops: t}
    },
    getDevice: function (e) {
        return FS.devices[e]
    },
    getMounts: function (e) {
        for (var t = [], r = [e]; r.length;) {
            var n = r.pop();
            t.push(n), r.push.apply(r, n.mounts)
        }
        return t
    },
    syncfs: function (e, t) {
        "function" == typeof e && (t = e, e = !1), FS.syncFSRequests++, FS.syncFSRequests > 1 && err("warning: " + FS.syncFSRequests + " FS.syncfs operations in flight at once, probably just doing extra work");
        var r = FS.getMounts(FS.root.mount), n = 0;

        function o(e) {
            return FS.syncFSRequests--, t(e)
        }

        function a(e) {
            if (e) return a.errored ? void 0 : (a.errored = !0, o(e));
            ++n >= r.length && o(null)
        }

        r.forEach(function (t) {
            if (!t.type.syncfs) return a(null);
            t.type.syncfs(t, e, a)
        })
    },
    mount: function (e, t, r) {
        var n, o = "/" === r, a = !r;
        if (o && FS.root) throw new FS.ErrnoError(10);
        if (!o && !a) {
            var i = FS.lookupPath(r, {follow_mount: !1});
            if (r = i.path, n = i.node, FS.isMountpoint(n)) throw new FS.ErrnoError(10);
            if (!FS.isDir(n.mode)) throw new FS.ErrnoError(54)
        }
        var s = {type: e, opts: t, mountpoint: r, mounts: []}, c = e.mount(s);
        return c.mount = s, s.root = c, o ? FS.root = c : n && (n.mounted = s, n.mount && n.mount.mounts.push(s)), c
    },
    unmount: function (e) {
        var t = FS.lookupPath(e, {follow_mount: !1});
        if (!FS.isMountpoint(t.node)) throw new FS.ErrnoError(28);
        var r = t.node, n = r.mounted, o = FS.getMounts(n);
        Object.keys(FS.nameTable).forEach(function (e) {
            for (var t = FS.nameTable[e]; t;) {
                var r = t.name_next;
                -1 !== o.indexOf(t.mount) && FS.destroyNode(t), t = r
            }
        }), r.mounted = null;
        var a = r.mount.mounts.indexOf(n);
        r.mount.mounts.splice(a, 1)
    },
    lookup: function (e, t) {
        return e.node_ops.lookup(e, t)
    },
    mknod: function (e, t, r) {
        var n = FS.lookupPath(e, {parent: !0}).node, o = PATH.basename(e);
        if (!o || "." === o || ".." === o) throw new FS.ErrnoError(28);
        var a = FS.mayCreate(n, o);
        if (a) throw new FS.ErrnoError(a);
        if (!n.node_ops.mknod) throw new FS.ErrnoError(63);
        return n.node_ops.mknod(n, o, t, r)
    },
    create: function (e, t) {
        return t = void 0 !== t ? t : 438, t &= 4095, t |= 32768, FS.mknod(e, t, 0)
    },
    mkdir: function (e, t) {
        return t = void 0 !== t ? t : 511, t &= 1023, t |= 16384, FS.mknod(e, t, 0)
    },
    mkdirTree: function (e, t) {
        for (var r = e.split("/"), n = "", o = 0; o < r.length; ++o) if (r[o]) {
            n += "/" + r[o];
            try {
                FS.mkdir(n, t)
            } catch (e) {
                if (20 != e.errno) throw e
            }
        }
    },
    mkdev: function (e, t, r) {
        return void 0 === r && (r = t, t = 438), t |= 8192, FS.mknod(e, t, r)
    },
    symlink: function (e, t) {
        if (!PATH_FS.resolve(e)) throw new FS.ErrnoError(44);
        var r = FS.lookupPath(t, {parent: !0}).node;
        if (!r) throw new FS.ErrnoError(44);
        var n = PATH.basename(t), o = FS.mayCreate(r, n);
        if (o) throw new FS.ErrnoError(o);
        if (!r.node_ops.symlink) throw new FS.ErrnoError(63);
        return r.node_ops.symlink(r, n, e)
    },
    rename: function (e, t) {
        var r, n, o = PATH.dirname(e), a = PATH.dirname(t), i = PATH.basename(e), s = PATH.basename(t);
        if (r = FS.lookupPath(e, {parent: !0}).node, n = FS.lookupPath(t, {parent: !0}).node, !r || !n) throw new FS.ErrnoError(44);
        if (r.mount !== n.mount) throw new FS.ErrnoError(75);
        var c, l = FS.lookupNode(r, i), u = PATH_FS.relative(e, a);
        if ("." !== u.charAt(0)) throw new FS.ErrnoError(28);
        if ("." !== (u = PATH_FS.relative(t, o)).charAt(0)) throw new FS.ErrnoError(55);
        try {
            c = FS.lookupNode(n, s)
        } catch (e) {
        }
        if (l !== c) {
            var d = FS.isDir(l.mode), f = FS.mayDelete(r, i, d);
            if (f) throw new FS.ErrnoError(f);
            if (f = c ? FS.mayDelete(n, s, d) : FS.mayCreate(n, s)) throw new FS.ErrnoError(f);
            if (!r.node_ops.rename) throw new FS.ErrnoError(63);
            if (FS.isMountpoint(l) || c && FS.isMountpoint(c)) throw new FS.ErrnoError(10);
            if (n !== r && (f = FS.nodePermissions(r, "w"))) throw new FS.ErrnoError(f);
            try {
                FS.trackingDelegate.willMovePath && FS.trackingDelegate.willMovePath(e, t)
            } catch (r) {
                err("FS.trackingDelegate['willMovePath']('" + e + "', '" + t + "') threw an exception: " + r.message)
            }
            FS.hashRemoveNode(l);
            try {
                r.node_ops.rename(l, n, s)
            } catch (e) {
                throw e
            } finally {
                FS.hashAddNode(l)
            }
            try {
                FS.trackingDelegate.onMovePath && FS.trackingDelegate.onMovePath(e, t)
            } catch (r) {
                err("FS.trackingDelegate['onMovePath']('" + e + "', '" + t + "') threw an exception: " + r.message)
            }
        }
    },
    rmdir: function (e) {
        var t = FS.lookupPath(e, {parent: !0}).node, r = PATH.basename(e), n = FS.lookupNode(t, r),
            o = FS.mayDelete(t, r, !0);
        if (o) throw new FS.ErrnoError(o);
        if (!t.node_ops.rmdir) throw new FS.ErrnoError(63);
        if (FS.isMountpoint(n)) throw new FS.ErrnoError(10);
        try {
            FS.trackingDelegate.willDeletePath && FS.trackingDelegate.willDeletePath(e)
        } catch (t) {
            err("FS.trackingDelegate['willDeletePath']('" + e + "') threw an exception: " + t.message)
        }
        t.node_ops.rmdir(t, r), FS.destroyNode(n);
        try {
            FS.trackingDelegate.onDeletePath && FS.trackingDelegate.onDeletePath(e)
        } catch (t) {
            err("FS.trackingDelegate['onDeletePath']('" + e + "') threw an exception: " + t.message)
        }
    },
    readdir: function (e) {
        var t = FS.lookupPath(e, {follow: !0}).node;
        if (!t.node_ops.readdir) throw new FS.ErrnoError(54);
        return t.node_ops.readdir(t)
    },
    unlink: function (e) {
        var t = FS.lookupPath(e, {parent: !0}).node, r = PATH.basename(e), n = FS.lookupNode(t, r),
            o = FS.mayDelete(t, r, !1);
        if (o) throw new FS.ErrnoError(o);
        if (!t.node_ops.unlink) throw new FS.ErrnoError(63);
        if (FS.isMountpoint(n)) throw new FS.ErrnoError(10);
        try {
            FS.trackingDelegate.willDeletePath && FS.trackingDelegate.willDeletePath(e)
        } catch (t) {
            err("FS.trackingDelegate['willDeletePath']('" + e + "') threw an exception: " + t.message)
        }
        t.node_ops.unlink(t, r), FS.destroyNode(n);
        try {
            FS.trackingDelegate.onDeletePath && FS.trackingDelegate.onDeletePath(e)
        } catch (t) {
            err("FS.trackingDelegate['onDeletePath']('" + e + "') threw an exception: " + t.message)
        }
    },
    readlink: function (e) {
        var t = FS.lookupPath(e).node;
        if (!t) throw new FS.ErrnoError(44);
        if (!t.node_ops.readlink) throw new FS.ErrnoError(28);
        return PATH_FS.resolve(FS.getPath(t.parent), t.node_ops.readlink(t))
    },
    stat: function (e, t) {
        var r = FS.lookupPath(e, {follow: !t}).node;
        if (!r) throw new FS.ErrnoError(44);
        if (!r.node_ops.getattr) throw new FS.ErrnoError(63);
        return r.node_ops.getattr(r)
    },
    lstat: function (e) {
        return FS.stat(e, !0)
    },
    chmod: function (e, t, r) {
        var n;
        "string" == typeof e ? n = FS.lookupPath(e, {follow: !r}).node : n = e;
        if (!n.node_ops.setattr) throw new FS.ErrnoError(63);
        n.node_ops.setattr(n, {mode: 4095 & t | -4096 & n.mode, timestamp: Date.now()})
    },
    lchmod: function (e, t) {
        FS.chmod(e, t, !0)
    },
    fchmod: function (e, t) {
        var r = FS.getStream(e);
        if (!r) throw new FS.ErrnoError(8);
        FS.chmod(r.node, t)
    },
    chown: function (e, t, r, n) {
        var o;
        "string" == typeof e ? o = FS.lookupPath(e, {follow: !n}).node : o = e;
        if (!o.node_ops.setattr) throw new FS.ErrnoError(63);
        o.node_ops.setattr(o, {timestamp: Date.now()})
    },
    lchown: function (e, t, r) {
        FS.chown(e, t, r, !0)
    },
    fchown: function (e, t, r) {
        var n = FS.getStream(e);
        if (!n) throw new FS.ErrnoError(8);
        FS.chown(n.node, t, r)
    },
    truncate: function (e, t) {
        if (t < 0) throw new FS.ErrnoError(28);
        var r;
        "string" == typeof e ? r = FS.lookupPath(e, {follow: !0}).node : r = e;
        if (!r.node_ops.setattr) throw new FS.ErrnoError(63);
        if (FS.isDir(r.mode)) throw new FS.ErrnoError(31);
        if (!FS.isFile(r.mode)) throw new FS.ErrnoError(28);
        var n = FS.nodePermissions(r, "w");
        if (n) throw new FS.ErrnoError(n);
        r.node_ops.setattr(r, {size: t, timestamp: Date.now()})
    },
    ftruncate: function (e, t) {
        var r = FS.getStream(e);
        if (!r) throw new FS.ErrnoError(8);
        if (0 == (2097155 & r.flags)) throw new FS.ErrnoError(28);
        FS.truncate(r.node, t)
    },
    utime: function (e, t, r) {
        var n = FS.lookupPath(e, {follow: !0}).node;
        n.node_ops.setattr(n, {timestamp: Math.max(t, r)})
    },
    open: function (e, t, r, n, o) {
        if ("" === e) throw new FS.ErrnoError(44);
        var a;
        if (r = void 0 === r ? 438 : r, r = 64 & (t = "string" == typeof t ? FS.modeStringToFlags(t) : t) ? 4095 & r | 32768 : 0, "object" == typeof e) a = e; else {
            e = PATH.normalize(e);
            try {
                a = FS.lookupPath(e, {follow: !(131072 & t)}).node
            } catch (e) {
            }
        }
        var i = !1;
        if (64 & t) if (a) {
            if (128 & t) throw new FS.ErrnoError(20)
        } else a = FS.mknod(e, r, 0), i = !0;
        if (!a) throw new FS.ErrnoError(44);
        if (FS.isChrdev(a.mode) && (t &= -513), 65536 & t && !FS.isDir(a.mode)) throw new FS.ErrnoError(54);
        if (!i) {
            var s = FS.mayOpen(a, t);
            if (s) throw new FS.ErrnoError(s)
        }
        512 & t && FS.truncate(a, 0), t &= -131713;
        var c = FS.createStream({
            node: a,
            path: FS.getPath(a),
            flags: t,
            seekable: !0,
            position: 0,
            stream_ops: a.stream_ops,
            ungotten: [],
            error: !1
        }, n, o);
        c.stream_ops.open && c.stream_ops.open(c), !Module.logReadFiles || 1 & t || (FS.readFiles || (FS.readFiles = {}), e in FS.readFiles || (FS.readFiles[e] = 1, err("FS.trackingDelegate error on read file: " + e)));
        try {
            if (FS.trackingDelegate.onOpenFile) {
                var l = 0;
                1 != (2097155 & t) && (l |= FS.tracking.openFlags.READ), 0 != (2097155 & t) && (l |= FS.tracking.openFlags.WRITE), FS.trackingDelegate.onOpenFile(e, l)
            }
        } catch (t) {
            err("FS.trackingDelegate['onOpenFile']('" + e + "', flags) threw an exception: " + t.message)
        }
        return c
    },
    close: function (e) {
        if (FS.isClosed(e)) throw new FS.ErrnoError(8);
        e.getdents && (e.getdents = null);
        try {
            e.stream_ops.close && e.stream_ops.close(e)
        } catch (e) {
            throw e
        } finally {
            FS.closeStream(e.fd)
        }
        e.fd = null
    },
    isClosed: function (e) {
        return null === e.fd
    },
    llseek: function (e, t, r) {
        if (FS.isClosed(e)) throw new FS.ErrnoError(8);
        if (!e.seekable || !e.stream_ops.llseek) throw new FS.ErrnoError(70);
        if (0 != r && 1 != r && 2 != r) throw new FS.ErrnoError(28);
        return e.position = e.stream_ops.llseek(e, t, r), e.ungotten = [], e.position
    },
    read: function (e, t, r, n, o) {
        if (n < 0 || o < 0) throw new FS.ErrnoError(28);
        if (FS.isClosed(e)) throw new FS.ErrnoError(8);
        if (1 == (2097155 & e.flags)) throw new FS.ErrnoError(8);
        if (FS.isDir(e.node.mode)) throw new FS.ErrnoError(31);
        if (!e.stream_ops.read) throw new FS.ErrnoError(28);
        var a = void 0 !== o;
        if (a) {
            if (!e.seekable) throw new FS.ErrnoError(70)
        } else o = e.position;
        var i = e.stream_ops.read(e, t, r, n, o);
        return a || (e.position += i), i
    },
    write: function (e, t, r, n, o, a) {
        if (n < 0 || o < 0) throw new FS.ErrnoError(28);
        if (FS.isClosed(e)) throw new FS.ErrnoError(8);
        if (0 == (2097155 & e.flags)) throw new FS.ErrnoError(8);
        if (FS.isDir(e.node.mode)) throw new FS.ErrnoError(31);
        if (!e.stream_ops.write) throw new FS.ErrnoError(28);
        e.seekable && 1024 & e.flags && FS.llseek(e, 0, 2);
        var i = void 0 !== o;
        if (i) {
            if (!e.seekable) throw new FS.ErrnoError(70)
        } else o = e.position;
        var s = e.stream_ops.write(e, t, r, n, o, a);
        i || (e.position += s);
        try {
            e.path && FS.trackingDelegate.onWriteToFile && FS.trackingDelegate.onWriteToFile(e.path)
        } catch (t) {
            err("FS.trackingDelegate['onWriteToFile']('" + e.path + "') threw an exception: " + t.message)
        }
        return s
    },
    allocate: function (e, t, r) {
        if (FS.isClosed(e)) throw new FS.ErrnoError(8);
        if (t < 0 || r <= 0) throw new FS.ErrnoError(28);
        if (0 == (2097155 & e.flags)) throw new FS.ErrnoError(8);
        if (!FS.isFile(e.node.mode) && !FS.isDir(e.node.mode)) throw new FS.ErrnoError(43);
        if (!e.stream_ops.allocate) throw new FS.ErrnoError(138);
        e.stream_ops.allocate(e, t, r)
    },
    mmap: function (e, t, r, n, o, a) {
        if (0 != (2 & o) && 0 == (2 & a) && 2 != (2097155 & e.flags)) throw new FS.ErrnoError(2);
        if (1 == (2097155 & e.flags)) throw new FS.ErrnoError(2);
        if (!e.stream_ops.mmap) throw new FS.ErrnoError(43);
        return e.stream_ops.mmap(e, t, r, n, o, a)
    },
    msync: function (e, t, r, n, o) {
        return e && e.stream_ops.msync ? e.stream_ops.msync(e, t, r, n, o) : 0
    },
    munmap: function (e) {
        return 0
    },
    ioctl: function (e, t, r) {
        if (!e.stream_ops.ioctl) throw new FS.ErrnoError(59);
        return e.stream_ops.ioctl(e, t, r)
    },
    readFile: function (e, t) {
        if ((t = t || {}).flags = t.flags || 0, t.encoding = t.encoding || "binary", "utf8" !== t.encoding && "binary" !== t.encoding) throw new Error('Invalid encoding type "' + t.encoding + '"');
        var r, n = FS.open(e, t.flags), o = FS.stat(e).size, a = new Uint8Array(o);
        return FS.read(n, a, 0, o, 0), "utf8" === t.encoding ? r = UTF8ArrayToString(a, 0) : "binary" === t.encoding && (r = a), FS.close(n), r
    },
    writeFile: function (e, t, r) {
        (r = r || {}).flags = r.flags || 577;
        var n = FS.open(e, r.flags, r.mode);
        if ("string" == typeof t) {
            var o = new Uint8Array(lengthBytesUTF8(t) + 1), a = stringToUTF8Array(t, o, 0, o.length);
            FS.write(n, o, 0, a, void 0, r.canOwn)
        } else {
            if (!ArrayBuffer.isView(t)) throw new Error("Unsupported data type");
            FS.write(n, t, 0, t.byteLength, void 0, r.canOwn)
        }
        FS.close(n)
    },
    cwd: function () {
        return FS.currentPath
    },
    chdir: function (e) {
        var t = FS.lookupPath(e, {follow: !0});
        if (null === t.node) throw new FS.ErrnoError(44);
        if (!FS.isDir(t.node.mode)) throw new FS.ErrnoError(54);
        var r = FS.nodePermissions(t.node, "x");
        if (r) throw new FS.ErrnoError(r);
        FS.currentPath = t.path
    },
    createDefaultDirectories: function () {
        FS.mkdir("/tmp"), FS.mkdir("/home"), FS.mkdir("/home/web_user")
    },
    createDefaultDevices: function () {
        FS.mkdir("/dev"), FS.registerDevice(FS.makedev(1, 3), {
            read: function () {
                return 0
            }, write: function (e, t, r, n, o) {
                return n
            }
        }), FS.mkdev("/dev/null", FS.makedev(1, 3)), TTY.register(FS.makedev(5, 0), TTY.default_tty_ops), TTY.register(FS.makedev(6, 0), TTY.default_tty1_ops), FS.mkdev("/dev/tty", FS.makedev(5, 0)), FS.mkdev("/dev/tty1", FS.makedev(6, 0));
        var e = getRandomDevice();
        FS.createDevice("/dev", "random", e), FS.createDevice("/dev", "urandom", e), FS.mkdir("/dev/shm"), FS.mkdir("/dev/shm/tmp")
    },
    createSpecialDirectories: function () {
        FS.mkdir("/proc");
        var e = FS.mkdir("/proc/self");
        FS.mkdir("/proc/self/fd"), FS.mount({
            mount: function () {
                var t = FS.createNode(e, "fd", 16895, 73);
                return t.node_ops = {
                    lookup: function (e, t) {
                        var r = +t, n = FS.getStream(r);
                        if (!n) throw new FS.ErrnoError(8);
                        var o = {
                            parent: null, mount: {mountpoint: "fake"}, node_ops: {
                                readlink: function () {
                                    return n.path
                                }
                            }
                        };
                        return o.parent = o, o
                    }
                }, t
            }
        }, {}, "/proc/self/fd")
    },
    createStandardStreams: function () {
        Module.stdin ? FS.createDevice("/dev", "stdin", Module.stdin) : FS.symlink("/dev/tty", "/dev/stdin"), Module.stdout ? FS.createDevice("/dev", "stdout", null, Module.stdout) : FS.symlink("/dev/tty", "/dev/stdout"), Module.stderr ? FS.createDevice("/dev", "stderr", null, Module.stderr) : FS.symlink("/dev/tty1", "/dev/stderr");
        FS.open("/dev/stdin", 0), FS.open("/dev/stdout", 1), FS.open("/dev/stderr", 1)
    },
    ensureErrnoError: function () {
        FS.ErrnoError || (FS.ErrnoError = function (e, t) {
            this.node = t, this.setErrno = function (e) {
                this.errno = e
            }, this.setErrno(e), this.message = "FS error"
        }, FS.ErrnoError.prototype = new Error, FS.ErrnoError.prototype.constructor = FS.ErrnoError, [44].forEach(function (e) {
            FS.genericErrors[e] = new FS.ErrnoError(e), FS.genericErrors[e].stack = "<generic error, no stack>"
        }))
    },
    staticInit: function () {
        FS.ensureErrnoError(), FS.nameTable = new Array(4096), FS.mount(MEMFS, {}, "/"), FS.createDefaultDirectories(), FS.createDefaultDevices(), FS.createSpecialDirectories(), FS.filesystems = {MEMFS: MEMFS}
    },
    init: function (e, t, r) {
        FS.init.initialized = !0, FS.ensureErrnoError(), Module.stdin = e || Module.stdin, Module.stdout = t || Module.stdout, Module.stderr = r || Module.stderr, FS.createStandardStreams()
    },
    quit: function () {
        FS.init.initialized = !1;
        var e = Module._fflush;
        e && e(0);
        for (var t = 0; t < FS.streams.length; t++) {
            var r = FS.streams[t];
            r && FS.close(r)
        }
    },
    getMode: function (e, t) {
        var r = 0;
        return e && (r |= 365), t && (r |= 146), r
    },
    findObject: function (e, t) {
        var r = FS.analyzePath(e, t);
        return r.exists ? r.object : null
    },
    analyzePath: function (e, t) {
        try {
            e = (n = FS.lookupPath(e, {follow: !t})).path
        } catch (e) {
        }
        var r = {
            isRoot: !1,
            exists: !1,
            error: 0,
            name: null,
            path: null,
            object: null,
            parentExists: !1,
            parentPath: null,
            parentObject: null
        };
        try {
            var n = FS.lookupPath(e, {parent: !0});
            r.parentExists = !0, r.parentPath = n.path, r.parentObject = n.node, r.name = PATH.basename(e), n = FS.lookupPath(e, {follow: !t}), r.exists = !0, r.path = n.path, r.object = n.node, r.name = n.node.name, r.isRoot = "/" === n.path
        } catch (e) {
            r.error = e.errno
        }
        return r
    },
    createPath: function (e, t, r, n) {
        e = "string" == typeof e ? e : FS.getPath(e);
        for (var o = t.split("/").reverse(); o.length;) {
            var a = o.pop();
            if (a) {
                var i = PATH.join2(e, a);
                try {
                    FS.mkdir(i)
                } catch (e) {
                }
                e = i
            }
        }
        return i
    },
    createFile: function (e, t, r, n, o) {
        var a = PATH.join2("string" == typeof e ? e : FS.getPath(e), t), i = FS.getMode(n, o);
        return FS.create(a, i)
    },
    createDataFile: function (e, t, r, n, o, a) {
        var i = t ? PATH.join2("string" == typeof e ? e : FS.getPath(e), t) : e, s = FS.getMode(n, o),
            c = FS.create(i, s);
        if (r) {
            if ("string" == typeof r) {
                for (var l = new Array(r.length), u = 0, d = r.length; u < d; ++u) l[u] = r.charCodeAt(u);
                r = l
            }
            FS.chmod(c, 146 | s);
            var f = FS.open(c, 577);
            FS.write(f, r, 0, r.length, 0, a), FS.close(f), FS.chmod(c, s)
        }
        return c
    },
    createDevice: function (e, t, r, n) {
        var o = PATH.join2("string" == typeof e ? e : FS.getPath(e), t), a = FS.getMode(!!r, !!n);
        FS.createDevice.major || (FS.createDevice.major = 64);
        var i = FS.makedev(FS.createDevice.major++, 0);
        return FS.registerDevice(i, {
            open: function (e) {
                e.seekable = !1
            }, close: function (e) {
                n && n.buffer && n.buffer.length && n(10)
            }, read: function (e, t, n, o, a) {
                for (var i = 0, s = 0; s < o; s++) {
                    var c;
                    try {
                        c = r()
                    } catch (e) {
                        throw new FS.ErrnoError(29)
                    }
                    if (void 0 === c && 0 === i) throw new FS.ErrnoError(6);
                    if (null == c) break;
                    i++, t[n + s] = c
                }
                return i && (e.node.timestamp = Date.now()), i
            }, write: function (e, t, r, o, a) {
                for (var i = 0; i < o; i++) try {
                    n(t[r + i])
                } catch (e) {
                    throw new FS.ErrnoError(29)
                }
                return o && (e.node.timestamp = Date.now()), i
            }
        }), FS.mkdev(o, a, i)
    },
    forceLoadFile: function (e) {
        if (e.isDevice || e.isFolder || e.link || e.contents) return !0;
        if ("undefined" != typeof XMLHttpRequest) throw new Error("Lazy loading should have been performed (contents set) in createLazyFile, but it was not. Lazy loading only works in web workers. Use --embed-file or --preload-file in emcc on the main thread.");
        if (!read_) throw new Error("Cannot load without read() or XMLHttpRequest.");
        try {
            e.contents = intArrayFromString(read_(e.url), !0), e.usedBytes = e.contents.length
        } catch (e) {
            throw new FS.ErrnoError(29)
        }
    },
    createLazyFile: function (e, t, r, n, o) {
        function a() {
            this.lengthKnown = !1, this.chunks = []
        }

        if (a.prototype.get = function (e) {
            if (!(e > this.length - 1 || e < 0)) {
                var t = e % this.chunkSize, r = e / this.chunkSize | 0;
                return this.getter(r)[t]
            }
        }, a.prototype.setDataGetter = function (e) {
            this.getter = e
        }, a.prototype.cacheLength = function () {
            var e = new XMLHttpRequest;
            if (e.open("HEAD", r, !1), e.send(null), !(e.status >= 200 && e.status < 300 || 304 === e.status)) throw new Error("Couldn't load " + r + ". Status: " + e.status);
            var t, n = Number(e.getResponseHeader("Content-length")),
                o = (t = e.getResponseHeader("Accept-Ranges")) && "bytes" === t,
                a = (t = e.getResponseHeader("Content-Encoding")) && "gzip" === t, i = 1048576;
            o || (i = n);
            var s = this;
            s.setDataGetter(function (e) {
                var t = e * i, o = (e + 1) * i - 1;
                if (o = Math.min(o, n - 1), void 0 === s.chunks[e] && (s.chunks[e] = function (e, t) {
                    if (e > t) throw new Error("invalid range (" + e + ", " + t + ") or no bytes requested!");
                    if (t > n - 1) throw new Error("only " + n + " bytes available! programmer error!");
                    var o = new XMLHttpRequest;
                    if (o.open("GET", r, !1), n !== i && o.setRequestHeader("Range", "bytes=" + e + "-" + t), "undefined" != typeof Uint8Array && (o.responseType = "arraybuffer"), o.overrideMimeType && o.overrideMimeType("text/plain; charset=x-user-defined"), o.send(null), !(o.status >= 200 && o.status < 300 || 304 === o.status)) throw new Error("Couldn't load " + r + ". Status: " + o.status);
                    return void 0 !== o.response ? new Uint8Array(o.response || []) : intArrayFromString(o.responseText || "", !0)
                }(t, o)), void 0 === s.chunks[e]) throw new Error("doXHR failed!");
                return s.chunks[e]
            }), !a && n || (i = n = 1, n = this.getter(0).length, i = n, out("LazyFiles on gzip forces download of the whole file when length is accessed")), this._length = n, this._chunkSize = i, this.lengthKnown = !0
        }, "undefined" != typeof XMLHttpRequest) {
            if (!ENVIRONMENT_IS_WORKER) throw"Cannot do synchronous binary XHRs outside webworkers in modern browsers. Use --embed-file or --preload-file in emcc";
            var i = new a;
            Object.defineProperties(i, {
                length: {
                    get: function () {
                        return this.lengthKnown || this.cacheLength(), this._length
                    }
                }, chunkSize: {
                    get: function () {
                        return this.lengthKnown || this.cacheLength(), this._chunkSize
                    }
                }
            });
            var s = {isDevice: !1, contents: i}
        } else s = {isDevice: !1, url: r};
        var c = FS.createFile(e, t, s, n, o);
        s.contents ? c.contents = s.contents : s.url && (c.contents = null, c.url = s.url), Object.defineProperties(c, {
            usedBytes: {
                get: function () {
                    return this.contents.length
                }
            }
        });
        var l = {};
        return Object.keys(c.stream_ops).forEach(function (e) {
            var t = c.stream_ops[e];
            l[e] = function () {
                return FS.forceLoadFile(c), t.apply(null, arguments)
            }
        }), l.read = function (e, t, r, n, o) {
            FS.forceLoadFile(c);
            var a = e.node.contents;
            if (o >= a.length) return 0;
            var i = Math.min(a.length - o, n);
            if (a.slice) for (var s = 0; s < i; s++) t[r + s] = a[o + s]; else for (s = 0; s < i; s++) t[r + s] = a.get(o + s);
            return i
        }, c.stream_ops = l, c
    },
    createPreloadedFile: function (e, t, r, n, o, a, i, s, c, l) {
        Browser.init();
        var u = t ? PATH_FS.resolve(PATH.join2(e, t)) : e, d = getUniqueRunDependency("cp " + u);

        function f(r) {
            function f(r) {
                l && l(), s || FS.createDataFile(e, t, r, n, o, c), a && a(), removeRunDependency(d)
            }

            var m = !1;
            Module.preloadPlugins.forEach(function (e) {
                m || e.canHandle(u) && (e.handle(r, u, f, function () {
                    i && i(), removeRunDependency(d)
                }), m = !0)
            }), m || f(r)
        }

        addRunDependency(d), "string" == typeof r ? Browser.asyncLoad(r, function (e) {
            f(e)
        }, i) : f(r)
    },
    indexedDB: function () {
        return window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB
    },
    DB_NAME: function () {
        return "EM_FS_" + window.location.pathname
    },
    DB_VERSION: 20,
    DB_STORE_NAME: "FILE_DATA",
    saveFilesToDB: function (e, t, r) {
        t = t || function () {
        }, r = r || function () {
        };
        var n = FS.indexedDB();
        try {
            var o = n.open(FS.DB_NAME(), FS.DB_VERSION)
        } catch (e) {
            return r(e)
        }
        o.onupgradeneeded = function () {
            out("creating db"), o.result.createObjectStore(FS.DB_STORE_NAME)
        }, o.onsuccess = function () {
            var n = o.result.transaction([FS.DB_STORE_NAME], "readwrite"), a = n.objectStore(FS.DB_STORE_NAME), i = 0,
                s = 0, c = e.length;

            function l() {
                0 == s ? t() : r()
            }

            e.forEach(function (e) {
                var t = a.put(FS.analyzePath(e).object.contents, e);
                t.onsuccess = function () {
                    ++i + s == c && l()
                }, t.onerror = function () {
                    i + ++s == c && l()
                }
            }), n.onerror = r
        }, o.onerror = r
    },
    loadFilesFromDB: function (e, t, r) {
        t = t || function () {
        }, r = r || function () {
        };
        var n = FS.indexedDB();
        try {
            var o = n.open(FS.DB_NAME(), FS.DB_VERSION)
        } catch (e) {
            return r(e)
        }
        o.onupgradeneeded = r, o.onsuccess = function () {
            var n = o.result;
            try {
                var a = n.transaction([FS.DB_STORE_NAME], "readonly")
            } catch (e) {
                return void r(e)
            }
            var i = a.objectStore(FS.DB_STORE_NAME), s = 0, c = 0, l = e.length;

            function u() {
                0 == c ? t() : r()
            }

            e.forEach(function (e) {
                var t = i.get(e);
                t.onsuccess = function () {
                    FS.analyzePath(e).exists && FS.unlink(e), FS.createDataFile(PATH.dirname(e), PATH.basename(e), t.result, !0, !0, !0), ++s + c == l && u()
                }, t.onerror = function () {
                    s + ++c == l && u()
                }
            }), a.onerror = r
        }, o.onerror = r
    }
}, SYSCALLS = {
    mappings: {}, DEFAULT_POLLMASK: 5, umask: 511, calculateAt: function (e, t, r) {
        if ("/" === t[0]) return t;
        var n;
        if (-100 === e) n = FS.cwd(); else {
            var o = FS.getStream(e);
            if (!o) throw new FS.ErrnoError(8);
            n = o.path
        }
        if (0 == t.length) {
            if (!r) throw new FS.ErrnoError(44);
            return n
        }
        return PATH.join2(n, t)
    }, doStat: function (e, t, r) {
        try {
            var n = e(t)
        } catch (e) {
            if (e && e.node && PATH.normalize(t) !== PATH.normalize(FS.getPath(e.node))) return -54;
            throw e
        }
        return HEAP32[r >> 2] = n.dev, HEAP32[r + 4 >> 2] = 0, HEAP32[r + 8 >> 2] = n.ino, HEAP32[r + 12 >> 2] = n.mode, HEAP32[r + 16 >> 2] = n.nlink, HEAP32[r + 20 >> 2] = n.uid, HEAP32[r + 24 >> 2] = n.gid, HEAP32[r + 28 >> 2] = n.rdev, HEAP32[r + 32 >> 2] = 0, tempI64 = [n.size >>> 0, (tempDouble = n.size, +Math.abs(tempDouble) >= 1 ? tempDouble > 0 ? (0 | Math.min(+Math.floor(tempDouble / 4294967296), 4294967295)) >>> 0 : ~~+Math.ceil((tempDouble - +(~~tempDouble >>> 0)) / 4294967296) >>> 0 : 0)], HEAP32[r + 40 >> 2] = tempI64[0], HEAP32[r + 44 >> 2] = tempI64[1], HEAP32[r + 48 >> 2] = 4096, HEAP32[r + 52 >> 2] = n.blocks, HEAP32[r + 56 >> 2] = n.atime.getTime() / 1e3 | 0, HEAP32[r + 60 >> 2] = 0, HEAP32[r + 64 >> 2] = n.mtime.getTime() / 1e3 | 0, HEAP32[r + 68 >> 2] = 0, HEAP32[r + 72 >> 2] = n.ctime.getTime() / 1e3 | 0, HEAP32[r + 76 >> 2] = 0, tempI64 = [n.ino >>> 0, (tempDouble = n.ino, +Math.abs(tempDouble) >= 1 ? tempDouble > 0 ? (0 | Math.min(+Math.floor(tempDouble / 4294967296), 4294967295)) >>> 0 : ~~+Math.ceil((tempDouble - +(~~tempDouble >>> 0)) / 4294967296) >>> 0 : 0)], HEAP32[r + 80 >> 2] = tempI64[0], HEAP32[r + 84 >> 2] = tempI64[1], 0
    }, doMsync: function (e, t, r, n, o) {
        var a = HEAPU8.slice(e, e + r);
        FS.msync(t, a, o, r, n)
    }, doMkdir: function (e, t) {
        return "/" === (e = PATH.normalize(e))[e.length - 1] && (e = e.substr(0, e.length - 1)), FS.mkdir(e, t, 0), 0
    }, doMknod: function (e, t, r) {
        switch (61440 & t) {
            case 32768:
            case 8192:
            case 24576:
            case 4096:
            case 49152:
                break;
            default:
                return -28
        }
        return FS.mknod(e, t, r), 0
    }, doReadlink: function (e, t, r) {
        if (r <= 0) return -28;
        var n = FS.readlink(e), o = Math.min(r, lengthBytesUTF8(n)), a = HEAP8[t + o];
        return stringToUTF8(n, t, r + 1), HEAP8[t + o] = a, o
    }, doAccess: function (e, t) {
        if (-8 & t) return -28;
        var r;
        if (!(r = FS.lookupPath(e, {follow: !0}).node)) return -44;
        var n = "";
        return 4 & t && (n += "r"), 2 & t && (n += "w"), 1 & t && (n += "x"), n && FS.nodePermissions(r, n) ? -2 : 0
    }, doDup: function (e, t, r) {
        var n = FS.getStream(r);
        return n && FS.close(n), FS.open(e, t, 0, r, r).fd
    }, doReadv: function (e, t, r, n) {
        for (var o = 0, a = 0; a < r; a++) {
            var i = HEAP32[t + 8 * a >> 2], s = HEAP32[t + (8 * a + 4) >> 2], c = FS.read(e, HEAP8, i, s, n);
            if (c < 0) return -1;
            if (o += c, c < s) break
        }
        return o
    }, doWritev: function (e, t, r, n) {
        for (var o = 0, a = 0; a < r; a++) {
            var i = HEAP32[t + 8 * a >> 2], s = HEAP32[t + (8 * a + 4) >> 2], c = FS.write(e, HEAP8, i, s, n);
            if (c < 0) return -1;
            o += c
        }
        return o
    }, varargs: void 0, get: function () {
        return SYSCALLS.varargs += 4, HEAP32[SYSCALLS.varargs - 4 >> 2]
    }, getStr: function (e) {
        return UTF8ToString(e)
    }, getStreamFromFD: function (e) {
        var t = FS.getStream(e);
        if (!t) throw new FS.ErrnoError(8);
        return t
    }, get64: function (e, t) {
        return e
    }
};

function ___sys_fcntl64(e, t, r) {
    SYSCALLS.varargs = r;
    try {
        var n = SYSCALLS.getStreamFromFD(e);
        switch (t) {
            case 0:
                return (o = SYSCALLS.get()) < 0 ? -28 : FS.open(n.path, n.flags, 0, o).fd;
            case 1:
            case 2:
                return 0;
            case 3:
                return n.flags;
            case 4:
                var o = SYSCALLS.get();
                return n.flags |= o, 0;
            case 12:
                o = SYSCALLS.get();
                return HEAP16[o + 0 >> 1] = 2, 0;
            case 13:
            case 14:
                return 0;
            case 16:
            case 8:
                return -28;
            case 9:
                return setErrNo(28), -1;
            default:
                return -28
        }
    } catch (e) {
        return void 0 !== FS && e instanceof FS.ErrnoError || abort(e), -e.errno
    }
}

function ___sys_getdents64(e, t, r) {
    try {
        var n = SYSCALLS.getStreamFromFD(e);
        n.getdents || (n.getdents = FS.readdir(n.path));
        for (var o = 0, a = FS.llseek(n, 0, 1), i = Math.floor(a / 280); i < n.getdents.length && o + 280 <= r;) {
            var s, c, l = n.getdents[i];
            if ("." === l[0]) s = 1, c = 4; else {
                var u = FS.lookupNode(n.node, l);
                s = u.id, c = FS.isChrdev(u.mode) ? 2 : FS.isDir(u.mode) ? 4 : FS.isLink(u.mode) ? 10 : 8
            }
            tempI64 = [s >>> 0, (tempDouble = s, +Math.abs(tempDouble) >= 1 ? tempDouble > 0 ? (0 | Math.min(+Math.floor(tempDouble / 4294967296), 4294967295)) >>> 0 : ~~+Math.ceil((tempDouble - +(~~tempDouble >>> 0)) / 4294967296) >>> 0 : 0)], HEAP32[t + o >> 2] = tempI64[0], HEAP32[t + o + 4 >> 2] = tempI64[1], tempI64 = [280 * (i + 1) >>> 0, (tempDouble = 280 * (i + 1), +Math.abs(tempDouble) >= 1 ? tempDouble > 0 ? (0 | Math.min(+Math.floor(tempDouble / 4294967296), 4294967295)) >>> 0 : ~~+Math.ceil((tempDouble - +(~~tempDouble >>> 0)) / 4294967296) >>> 0 : 0)], HEAP32[t + o + 8 >> 2] = tempI64[0], HEAP32[t + o + 12 >> 2] = tempI64[1], HEAP16[t + o + 16 >> 1] = 280, HEAP8[t + o + 18 >> 0] = c, stringToUTF8(l, t + o + 19, 256), o += 280, i += 1
        }
        return FS.llseek(n, 280 * i, 0), o
    } catch (e) {
        return void 0 !== FS && e instanceof FS.ErrnoError || abort(e), -e.errno
    }
}

function ___sys_ioctl(e, t, r) {
    SYSCALLS.varargs = r;
    try {
        var n = SYSCALLS.getStreamFromFD(e);
        switch (t) {
            case 21509:
            case 21505:
                return n.tty ? 0 : -59;
            case 21510:
            case 21511:
            case 21512:
            case 21506:
            case 21507:
            case 21508:
                return n.tty ? 0 : -59;
            case 21519:
                if (!n.tty) return -59;
                var o = SYSCALLS.get();
                return HEAP32[o >> 2] = 0, 0;
            case 21520:
                return n.tty ? -28 : -59;
            case 21531:
                o = SYSCALLS.get();
                return FS.ioctl(n, t, o);
            case 21523:
            case 21524:
                return n.tty ? 0 : -59;
            default:
                abort("bad ioctl syscall " + t)
        }
    } catch (e) {
        return void 0 !== FS && e instanceof FS.ErrnoError || abort(e), -e.errno
    }
}

function ___sys_mkdir(e, t) {
    try {
        return e = SYSCALLS.getStr(e), SYSCALLS.doMkdir(e, t)
    } catch (e) {
        return void 0 !== FS && e instanceof FS.ErrnoError || abort(e), -e.errno
    }
}

function ___sys_open(e, t, r) {
    SYSCALLS.varargs = r;
    try {
        var n = SYSCALLS.getStr(e), o = r ? SYSCALLS.get() : 0;
        return FS.open(n, t, o).fd
    } catch (e) {
        return void 0 !== FS && e instanceof FS.ErrnoError || abort(e), -e.errno
    }
}

function ___sys_stat64(e, t) {
    try {
        return e = SYSCALLS.getStr(e), SYSCALLS.doStat(FS.stat, e, t)
    } catch (e) {
        return void 0 !== FS && e instanceof FS.ErrnoError || abort(e), -e.errno
    }
}

function _abort() {
    abort()
}

_emscripten_get_now = ENVIRONMENT_IS_NODE ? function () {
    var e = process.hrtime();
    return 1e3 * e[0] + e[1] / 1e6
} : "undefined" != typeof dateNow ? dateNow : function () {
    return performance.now()
};
var _emscripten_get_now_is_monotonic = !0;

function _clock_gettime(e, t) {
    var r;
    if (0 === e) r = Date.now(); else {
        if (1 !== e && 4 !== e || !_emscripten_get_now_is_monotonic) return setErrNo(28), -1;
        r = _emscripten_get_now()
    }
    return HEAP32[t >> 2] = r / 1e3 | 0, HEAP32[t + 4 >> 2] = r % 1e3 * 1e3 * 1e3 | 0, 0
}

function _dlclose(e) {
    abort("To use dlopen, you need to use Emscripten's linking support, see https://github.com/emscripten-core/emscripten/wiki/Linking")
}

function _emscripten_set_main_loop_timing(e, t) {
    if (Browser.mainLoop.timingMode = e, Browser.mainLoop.timingValue = t, !Browser.mainLoop.func) return 1;
    if (0 == e) Browser.mainLoop.scheduler = function () {
        var e = 0 | Math.max(0, Browser.mainLoop.tickStartTime + t - _emscripten_get_now());
        setTimeout(Browser.mainLoop.runner, e)
    }, Browser.mainLoop.method = "timeout"; else if (1 == e) Browser.mainLoop.scheduler = function () {
        Browser.requestAnimationFrame(Browser.mainLoop.runner)
    }, Browser.mainLoop.method = "rAF"; else if (2 == e) {
        if ("undefined" == typeof setImmediate) {
            var r = [];
            addEventListener("message", function (e) {
                "setimmediate" !== e.data && "setimmediate" !== e.data.target || (e.stopPropagation(), r.shift()())
            }, !0), setImmediate = function (e) {
                r.push(e), ENVIRONMENT_IS_WORKER ? (void 0 === Module.setImmediates && (Module.setImmediates = []), Module.setImmediates.push(e), postMessage({target: "setimmediate"})) : postMessage("setimmediate", "*")
            }
        }
        Browser.mainLoop.scheduler = function () {
            setImmediate(Browser.mainLoop.runner)
        }, Browser.mainLoop.method = "immediate"
    }
    return 0
}

function setMainLoop(e, t, r, n, o) {
    noExitRuntime = !0, assert(!Browser.mainLoop.func, "emscripten_set_main_loop: there can only be one main loop function at once: call emscripten_cancel_main_loop to cancel the previous one before setting a new one with different parameters."), Browser.mainLoop.func = e, Browser.mainLoop.arg = n;
    var a = Browser.mainLoop.currentlyRunningMainloop;
    if (Browser.mainLoop.runner = function () {
        if (!ABORT) if (Browser.mainLoop.queue.length > 0) {
            var t = Date.now(), r = Browser.mainLoop.queue.shift();
            if (r.func(r.arg), Browser.mainLoop.remainingBlockers) {
                var n = Browser.mainLoop.remainingBlockers, o = n % 1 == 0 ? n - 1 : Math.floor(n);
                r.counted ? Browser.mainLoop.remainingBlockers = o : (o += .5, Browser.mainLoop.remainingBlockers = (8 * n + o) / 9)
            }
            if (console.log('main loop blocker "' + r.name + '" took ' + (Date.now() - t) + " ms"), Browser.mainLoop.updateStatus(), a < Browser.mainLoop.currentlyRunningMainloop) return;
            setTimeout(Browser.mainLoop.runner, 0)
        } else a < Browser.mainLoop.currentlyRunningMainloop || (Browser.mainLoop.currentFrameNumber = Browser.mainLoop.currentFrameNumber + 1 | 0, 1 == Browser.mainLoop.timingMode && Browser.mainLoop.timingValue > 1 && Browser.mainLoop.currentFrameNumber % Browser.mainLoop.timingValue != 0 ? Browser.mainLoop.scheduler() : (0 == Browser.mainLoop.timingMode && (Browser.mainLoop.tickStartTime = _emscripten_get_now()), Browser.mainLoop.runIter(e), a < Browser.mainLoop.currentlyRunningMainloop || ("object" == typeof SDL && SDL.audio && SDL.audio.queueNewAudioData && SDL.audio.queueNewAudioData(), Browser.mainLoop.scheduler())))
    }, o || (t && t > 0 ? _emscripten_set_main_loop_timing(0, 1e3 / t) : _emscripten_set_main_loop_timing(1, 1), Browser.mainLoop.scheduler()), r) throw"unwind"
}

var Browser = {
    mainLoop: {
        scheduler: null,
        method: "",
        currentlyRunningMainloop: 0,
        func: null,
        arg: 0,
        timingMode: 0,
        timingValue: 0,
        currentFrameNumber: 0,
        queue: [],
        pause: function () {
            Browser.mainLoop.scheduler = null, Browser.mainLoop.currentlyRunningMainloop++
        },
        resume: function () {
            Browser.mainLoop.currentlyRunningMainloop++;
            var e = Browser.mainLoop.timingMode, t = Browser.mainLoop.timingValue, r = Browser.mainLoop.func;
            Browser.mainLoop.func = null, setMainLoop(r, 0, !1, Browser.mainLoop.arg, !0), _emscripten_set_main_loop_timing(e, t), Browser.mainLoop.scheduler()
        },
        updateStatus: function () {
            if (Module.setStatus) {
                var e = Module.statusMessage || "Please wait...", t = Browser.mainLoop.remainingBlockers,
                    r = Browser.mainLoop.expectedBlockers;
                t ? t < r ? Module.setStatus(e + " (" + (r - t) + "/" + r + ")") : Module.setStatus(e) : Module.setStatus("")
            }
        },
        runIter: function (e) {
            if (!ABORT) {
                if (Module.preMainLoop) if (!1 === Module.preMainLoop()) return;
                try {
                    e()
                } catch (e) {
                    if (e instanceof ExitStatus) return;
                    if ("unwind" == e) return;
                    throw e && "object" == typeof e && e.stack && err("exception thrown: " + [e, e.stack]), e
                }
                Module.postMainLoop && Module.postMainLoop()
            }
        }
    },
    isFullscreen: !1,
    pointerLock: !1,
    moduleContextCreatedCallbacks: [],
    workers: [],
    init: function () {
        if (Module.preloadPlugins || (Module.preloadPlugins = []), !Browser.initted) {
            Browser.initted = !0;
            try {
                new Blob, Browser.hasBlobConstructor = !0
            } catch (e) {
                Browser.hasBlobConstructor = !1, console.log("warning: no blob constructor, cannot create blobs with mimetypes")
            }
            Browser.BlobBuilder = "undefined" != typeof MozBlobBuilder ? MozBlobBuilder : "undefined" != typeof WebKitBlobBuilder ? WebKitBlobBuilder : Browser.hasBlobConstructor ? null : console.log("warning: no BlobBuilder"), Browser.URLObject = "undefined" != typeof window ? window.URL ? window.URL : window.webkitURL : void 0, Module.noImageDecoding || void 0 !== Browser.URLObject || (console.log("warning: Browser does not support creating object URLs. Built-in browser image decoding will not be available."), Module.noImageDecoding = !0);
            var e = {
                canHandle: function (e) {
                    return !Module.noImageDecoding && /\.(jpg|jpeg|png|bmp)$/i.test(e)
                }, handle: function (e, t, r, n) {
                    var o = null;
                    if (Browser.hasBlobConstructor) try {
                        (o = new Blob([e], {type: Browser.getMimetype(t)})).size !== e.length && (o = new Blob([new Uint8Array(e).buffer], {type: Browser.getMimetype(t)}))
                    } catch (e) {
                        warnOnce("Blob constructor present but fails: " + e + "; falling back to blob builder")
                    }
                    if (!o) {
                        var a = new Browser.BlobBuilder;
                        a.append(new Uint8Array(e).buffer), o = a.getBlob()
                    }
                    var i = Browser.URLObject.createObjectURL(o), s = new Image;
                    s.onload = function () {
                        assert(s.complete, "Image " + t + " could not be decoded");
                        var n = document.createElement("canvas");
                        n.width = s.width, n.height = s.height, n.getContext("2d").drawImage(s, 0, 0), Module.preloadedImages[t] = n, Browser.URLObject.revokeObjectURL(i), r && r(e)
                    }, s.onerror = function (e) {
                        console.log("Image " + i + " could not be decoded"), n && n()
                    }, s.src = i
                }
            };
            Module.preloadPlugins.push(e);
            var t = {
                canHandle: function (e) {
                    return !Module.noAudioDecoding && e.substr(-4) in {".ogg": 1, ".wav": 1, ".mp3": 1}
                }, handle: function (e, t, r, n) {
                    var o = !1;

                    function a(n) {
                        o || (o = !0, Module.preloadedAudios[t] = n, r && r(e))
                    }

                    function i() {
                        o || (o = !0, Module.preloadedAudios[t] = new Audio, n && n())
                    }

                    if (!Browser.hasBlobConstructor) return i();
                    try {
                        var s = new Blob([e], {type: Browser.getMimetype(t)})
                    } catch (e) {
                        return i()
                    }
                    var c = Browser.URLObject.createObjectURL(s), l = new Audio;
                    l.addEventListener("canplaythrough", function () {
                        a(l)
                    }, !1), l.onerror = function (r) {
                        o || (console.log("warning: browser could not fully decode audio " + t + ", trying slower base64 approach"), l.src = "data:audio/x-" + t.substr(-3) + ";base64," + function (e) {
                            for (var t = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/", r = "", n = 0, o = 0, a = 0; a < e.length; a++) for (n = n << 8 | e[a], o += 8; o >= 6;) {
                                var i = n >> o - 6 & 63;
                                o -= 6, r += t[i]
                            }
                            return 2 == o ? (r += t[(3 & n) << 4], r += "==") : 4 == o && (r += t[(15 & n) << 2], r += "="), r
                        }(e), a(l))
                    }, l.src = c, Browser.safeSetTimeout(function () {
                        a(l)
                    }, 1e4)
                }
            };
            Module.preloadPlugins.push(t);
            var r = Module.canvas;
            r && (r.requestPointerLock = r.requestPointerLock || r.mozRequestPointerLock || r.webkitRequestPointerLock || r.msRequestPointerLock || function () {
            }, r.exitPointerLock = document.exitPointerLock || document.mozExitPointerLock || document.webkitExitPointerLock || document.msExitPointerLock || function () {
            }, r.exitPointerLock = r.exitPointerLock.bind(document), document.addEventListener("pointerlockchange", n, !1), document.addEventListener("mozpointerlockchange", n, !1), document.addEventListener("webkitpointerlockchange", n, !1), document.addEventListener("mspointerlockchange", n, !1), Module.elementPointerLock && r.addEventListener("click", function (e) {
                !Browser.pointerLock && Module.canvas.requestPointerLock && (Module.canvas.requestPointerLock(), e.preventDefault())
            }, !1))
        }

        function n() {
            Browser.pointerLock = document.pointerLockElement === Module.canvas || document.mozPointerLockElement === Module.canvas || document.webkitPointerLockElement === Module.canvas || document.msPointerLockElement === Module.canvas
        }
    },
    createContext: function (e, t, r, n) {
        if (t && Module.ctx && e == Module.canvas) return Module.ctx;
        var o, a;
        if (t) {
            var i = {antialias: !1, alpha: !1, majorVersion: 1};
            if (n) for (var s in n) i[s] = n[s];
            void 0 !== GL && (a = GL.createContext(e, i)) && (o = GL.getContext(a).GLctx)
        } else o = e.getContext("2d");
        return o ? (r && (t || assert(void 0 === GLctx, "cannot set in module if GLctx is used, but we are a non-GL context that would replace it"), Module.ctx = o, t && GL.makeContextCurrent(a), Module.useWebGL = t, Browser.moduleContextCreatedCallbacks.forEach(function (e) {
            e()
        }), Browser.init()), o) : null
    },
    destroyContext: function (e, t, r) {
    },
    fullscreenHandlersInstalled: !1,
    lockPointer: void 0,
    resizeCanvas: void 0,
    requestFullscreen: function (e, t) {
        Browser.lockPointer = e, Browser.resizeCanvas = t, void 0 === Browser.lockPointer && (Browser.lockPointer = !0), void 0 === Browser.resizeCanvas && (Browser.resizeCanvas = !1);
        var r = Module.canvas;

        function n() {
            Browser.isFullscreen = !1;
            var e = r.parentNode;
            (document.fullscreenElement || document.mozFullScreenElement || document.msFullscreenElement || document.webkitFullscreenElement || document.webkitCurrentFullScreenElement) === e ? (r.exitFullscreen = Browser.exitFullscreen, Browser.lockPointer && r.requestPointerLock(), Browser.isFullscreen = !0, Browser.resizeCanvas ? Browser.setFullscreenCanvasSize() : Browser.updateCanvasDimensions(r)) : (e.parentNode.insertBefore(r, e), e.parentNode.removeChild(e), Browser.resizeCanvas ? Browser.setWindowedCanvasSize() : Browser.updateCanvasDimensions(r)), Module.onFullScreen && Module.onFullScreen(Browser.isFullscreen), Module.onFullscreen && Module.onFullscreen(Browser.isFullscreen)
        }

        Browser.fullscreenHandlersInstalled || (Browser.fullscreenHandlersInstalled = !0, document.addEventListener("fullscreenchange", n, !1), document.addEventListener("mozfullscreenchange", n, !1), document.addEventListener("webkitfullscreenchange", n, !1), document.addEventListener("MSFullscreenChange", n, !1));
        var o = document.createElement("div");
        r.parentNode.insertBefore(o, r), o.appendChild(r), o.requestFullscreen = o.requestFullscreen || o.mozRequestFullScreen || o.msRequestFullscreen || (o.webkitRequestFullscreen ? function () {
            o.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT)
        } : null) || (o.webkitRequestFullScreen ? function () {
            o.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT)
        } : null), o.requestFullscreen()
    },
    exitFullscreen: function () {
        return !!Browser.isFullscreen && ((document.exitFullscreen || document.cancelFullScreen || document.mozCancelFullScreen || document.msExitFullscreen || document.webkitCancelFullScreen || function () {
        }).apply(document, []), !0)
    },
    nextRAF: 0,
    fakeRequestAnimationFrame: function (e) {
        var t = Date.now();
        if (0 === Browser.nextRAF) Browser.nextRAF = t + 1e3 / 60; else for (; t + 2 >= Browser.nextRAF;) Browser.nextRAF += 1e3 / 60;
        var r = Math.max(Browser.nextRAF - t, 0);
        setTimeout(e, r)
    },
    requestAnimationFrame: function (e) {
        "function" != typeof requestAnimationFrame ? (0, Browser.fakeRequestAnimationFrame)(e) : requestAnimationFrame(e)
    },
    safeRequestAnimationFrame: function (e) {
        return Browser.requestAnimationFrame(function () {
            ABORT || e()
        })
    },
    safeSetTimeout: function (e, t) {
        return noExitRuntime = !0, setTimeout(function () {
            ABORT || e()
        }, t)
    },
    getMimetype: function (e) {
        return {
            jpg: "image/jpeg",
            jpeg: "image/jpeg",
            png: "image/png",
            bmp: "image/bmp",
            ogg: "audio/ogg",
            wav: "audio/wav",
            mp3: "audio/mpeg"
        }[e.substr(e.lastIndexOf(".") + 1)]
    },
    getUserMedia: function (e) {
        window.getUserMedia || (window.getUserMedia = navigator.getUserMedia || navigator.mozGetUserMedia), window.getUserMedia(e)
    },
    getMovementX: function (e) {
        return e.movementX || e.mozMovementX || e.webkitMovementX || 0
    },
    getMovementY: function (e) {
        return e.movementY || e.mozMovementY || e.webkitMovementY || 0
    },
    getMouseWheelDelta: function (e) {
        var t = 0;
        switch (e.type) {
            case"DOMMouseScroll":
                t = e.detail / 3;
                break;
            case"mousewheel":
                t = e.wheelDelta / 120;
                break;
            case"wheel":
                switch (t = e.deltaY, e.deltaMode) {
                    case 0:
                        t /= 100;
                        break;
                    case 1:
                        t /= 3;
                        break;
                    case 2:
                        t *= 80;
                        break;
                    default:
                        throw"unrecognized mouse wheel delta mode: " + e.deltaMode
                }
                break;
            default:
                throw"unrecognized mouse wheel event: " + e.type
        }
        return t
    },
    mouseX: 0,
    mouseY: 0,
    mouseMovementX: 0,
    mouseMovementY: 0,
    touches: {},
    lastTouches: {},
    calculateMouseEvent: function (e) {
        if (Browser.pointerLock) "mousemove" != e.type && "mozMovementX" in e ? Browser.mouseMovementX = Browser.mouseMovementY = 0 : (Browser.mouseMovementX = Browser.getMovementX(e), Browser.mouseMovementY = Browser.getMovementY(e)), "undefined" != typeof SDL ? (Browser.mouseX = SDL.mouseX + Browser.mouseMovementX, Browser.mouseY = SDL.mouseY + Browser.mouseMovementY) : (Browser.mouseX += Browser.mouseMovementX, Browser.mouseY += Browser.mouseMovementY); else {
            var t = Module.canvas.getBoundingClientRect(), r = Module.canvas.width, n = Module.canvas.height,
                o = void 0 !== window.scrollX ? window.scrollX : window.pageXOffset,
                a = void 0 !== window.scrollY ? window.scrollY : window.pageYOffset;
            if ("touchstart" === e.type || "touchend" === e.type || "touchmove" === e.type) {
                var i = e.touch;
                if (void 0 === i) return;
                var s = i.pageX - (o + t.left), c = i.pageY - (a + t.top),
                    l = {x: s *= r / t.width, y: c *= n / t.height};
                if ("touchstart" === e.type) Browser.lastTouches[i.identifier] = l, Browser.touches[i.identifier] = l; else if ("touchend" === e.type || "touchmove" === e.type) {
                    var u = Browser.touches[i.identifier];
                    u || (u = l), Browser.lastTouches[i.identifier] = u, Browser.touches[i.identifier] = l
                }
                return
            }
            var d = e.pageX - (o + t.left), f = e.pageY - (a + t.top);
            d *= r / t.width, f *= n / t.height, Browser.mouseMovementX = d - Browser.mouseX, Browser.mouseMovementY = f - Browser.mouseY, Browser.mouseX = d, Browser.mouseY = f
        }
    },
    asyncLoad: function (e, t, r, n) {
        var o = n ? "" : getUniqueRunDependency("al " + e);
        readAsync(e, function (r) {
            assert(r, 'Loading data file "' + e + '" failed (no arrayBuffer).'), t(new Uint8Array(r)), o && removeRunDependency(o)
        }, function (t) {
            if (!r) throw'Loading data file "' + e + '" failed.';
            r()
        }), o && addRunDependency(o)
    },
    resizeListeners: [],
    updateResizeListeners: function () {
        var e = Module.canvas;
        Browser.resizeListeners.forEach(function (t) {
            t(e.width, e.height)
        })
    },
    setCanvasSize: function (e, t, r) {
        var n = Module.canvas;
        Browser.updateCanvasDimensions(n, e, t), r || Browser.updateResizeListeners()
    },
    windowedWidth: 0,
    windowedHeight: 0,
    setFullscreenCanvasSize: function () {
        if ("undefined" != typeof SDL) {
            var e = HEAPU32[SDL.screen >> 2];
            e |= 8388608, HEAP32[SDL.screen >> 2] = e
        }
        Browser.updateCanvasDimensions(Module.canvas), Browser.updateResizeListeners()
    },
    setWindowedCanvasSize: function () {
        if ("undefined" != typeof SDL) {
            var e = HEAPU32[SDL.screen >> 2];
            e &= -8388609, HEAP32[SDL.screen >> 2] = e
        }
        Browser.updateCanvasDimensions(Module.canvas), Browser.updateResizeListeners()
    },
    updateCanvasDimensions: function (e, t, r) {
        t && r ? (e.widthNative = t, e.heightNative = r) : (t = e.widthNative, r = e.heightNative);
        var n = t, o = r;
        if (Module.forcedAspectRatio && Module.forcedAspectRatio > 0 && (n / o < Module.forcedAspectRatio ? n = Math.round(o * Module.forcedAspectRatio) : o = Math.round(n / Module.forcedAspectRatio)), (document.fullscreenElement || document.mozFullScreenElement || document.msFullscreenElement || document.webkitFullscreenElement || document.webkitCurrentFullScreenElement) === e.parentNode && "undefined" != typeof screen) {
            var a = Math.min(screen.width / n, screen.height / o);
            n = Math.round(n * a), o = Math.round(o * a)
        }
        Browser.resizeCanvas ? (e.width != n && (e.width = n), e.height != o && (e.height = o), void 0 !== e.style && (e.style.removeProperty("width"), e.style.removeProperty("height"))) : (e.width != t && (e.width = t), e.height != r && (e.height = r), void 0 !== e.style && (n != t || o != r ? (e.style.setProperty("width", n + "px", "important"), e.style.setProperty("height", o + "px", "important")) : (e.style.removeProperty("width"), e.style.removeProperty("height"))))
    },
    wgetRequests: {},
    nextWgetRequestHandle: 0,
    getNextWgetRequestHandle: function () {
        var e = Browser.nextWgetRequestHandle;
        return Browser.nextWgetRequestHandle++, e
    }
}, EGL = {
    errorCode: 12288,
    defaultDisplayInitialized: !1,
    currentContext: 0,
    currentReadSurface: 0,
    currentDrawSurface: 0,
    contextAttributes: {alpha: !1, depth: !1, stencil: !1, antialias: !1},
    stringCache: {},
    setErrorCode: function (e) {
        EGL.errorCode = e
    },
    chooseConfig: function (e, t, r, n, o) {
        if (62e3 != e) return EGL.setErrorCode(12296), 0;
        if (t) for (; ;) {
            var a = HEAP32[t >> 2];
            if (12321 == a) {
                var i = HEAP32[t + 4 >> 2];
                EGL.contextAttributes.alpha = i > 0
            } else if (12325 == a) {
                var s = HEAP32[t + 4 >> 2];
                EGL.contextAttributes.depth = s > 0
            } else if (12326 == a) {
                var c = HEAP32[t + 4 >> 2];
                EGL.contextAttributes.stencil = c > 0
            } else if (12337 == a) {
                var l = HEAP32[t + 4 >> 2];
                EGL.contextAttributes.antialias = l > 0
            } else if (12338 == a) {
                l = HEAP32[t + 4 >> 2];
                EGL.contextAttributes.antialias = 1 == l
            } else if (12544 == a) {
                var u = HEAP32[t + 4 >> 2];
                EGL.contextAttributes.lowLatency = 12547 != u
            } else if (12344 == a) break;
            t += 8
        }
        return r && n || o ? (o && (HEAP32[o >> 2] = 1), r && n > 0 && (HEAP32[r >> 2] = 62002), EGL.setErrorCode(12288), 1) : (EGL.setErrorCode(12300), 0)
    }
};

function _eglBindAPI(e) {
    return 12448 == e ? (EGL.setErrorCode(12288), 1) : (EGL.setErrorCode(12300), 0)
}

function _eglChooseConfig(e, t, r, n, o) {
    return EGL.chooseConfig(e, t, r, n, o)
}

function __webgl_enable_ANGLE_instanced_arrays(e) {
    var t = e.getExtension("ANGLE_instanced_arrays");
    if (t) return e.vertexAttribDivisor = function (e, r) {
        t.vertexAttribDivisorANGLE(e, r)
    }, e.drawArraysInstanced = function (e, r, n, o) {
        t.drawArraysInstancedANGLE(e, r, n, o)
    }, e.drawElementsInstanced = function (e, r, n, o, a) {
        t.drawElementsInstancedANGLE(e, r, n, o, a)
    }, 1
}

function __webgl_enable_OES_vertex_array_object(e) {
    var t = e.getExtension("OES_vertex_array_object");
    if (t) return e.createVertexArray = function () {
        return t.createVertexArrayOES()
    }, e.deleteVertexArray = function (e) {
        t.deleteVertexArrayOES(e)
    }, e.bindVertexArray = function (e) {
        t.bindVertexArrayOES(e)
    }, e.isVertexArray = function (e) {
        return t.isVertexArrayOES(e)
    }, 1
}

function __webgl_enable_WEBGL_draw_buffers(e) {
    var t = e.getExtension("WEBGL_draw_buffers");
    if (t) return e.drawBuffers = function (e, r) {
        t.drawBuffersWEBGL(e, r)
    }, 1
}

function __webgl_enable_WEBGL_multi_draw(e) {
    return !!(e.multiDrawWebgl = e.getExtension("WEBGL_multi_draw"))
}

var GL = {
    counter: 1,
    buffers: [],
    programs: [],
    framebuffers: [],
    renderbuffers: [],
    textures: [],
    uniforms: [],
    shaders: [],
    vaos: [],
    contexts: [],
    offscreenCanvases: {},
    timerQueriesEXT: [],
    programInfos: {},
    stringCache: {},
    unpackAlignment: 4,
    recordError: function (e) {
        GL.lastError || (GL.lastError = e)
    },
    getNewId: function (e) {
        for (var t = GL.counter++, r = e.length; r < t; r++) e[r] = null;
        return t
    },
    getSource: function (e, t, r, n) {
        for (var o = "", a = 0; a < t; ++a) {
            var i = n ? HEAP32[n + 4 * a >> 2] : -1;
            o += UTF8ToString(HEAP32[r + 4 * a >> 2], i < 0 ? void 0 : i)
        }
        return o
    },
    createContext: function (e, t) {
        var r = e.getContext("webgl", t);
        return r ? GL.registerContext(r, t) : 0
    },
    registerContext: function (e, t) {
        var r = GL.getNewId(GL.contexts), n = {handle: r, attributes: t, version: t.majorVersion, GLctx: e};
        return e.canvas && (e.canvas.GLctxObject = n), GL.contexts[r] = n, (void 0 === t.enableExtensionsByDefault || t.enableExtensionsByDefault) && GL.initExtensions(n), r
    },
    makeContextCurrent: function (e) {
        return GL.currentContext = GL.contexts[e], Module.ctx = GLctx = GL.currentContext && GL.currentContext.GLctx, !(e && !GLctx)
    },
    getContext: function (e) {
        return GL.contexts[e]
    },
    deleteContext: function (e) {
        GL.currentContext === GL.contexts[e] && (GL.currentContext = null), "object" == typeof JSEvents && JSEvents.removeAllHandlersOnTarget(GL.contexts[e].GLctx.canvas), GL.contexts[e] && GL.contexts[e].GLctx.canvas && (GL.contexts[e].GLctx.canvas.GLctxObject = void 0), GL.contexts[e] = null
    },
    initExtensions: function (e) {
        if (e || (e = GL.currentContext), !e.initExtensionsDone) {
            e.initExtensionsDone = !0;
            var t = e.GLctx;
            __webgl_enable_ANGLE_instanced_arrays(t), __webgl_enable_OES_vertex_array_object(t), __webgl_enable_WEBGL_draw_buffers(t), t.disjointTimerQueryExt = t.getExtension("EXT_disjoint_timer_query"), __webgl_enable_WEBGL_multi_draw(t), (t.getSupportedExtensions() || []).forEach(function (e) {
                e.indexOf("lose_context") < 0 && e.indexOf("debug") < 0 && t.getExtension(e)
            })
        }
    },
    populateUniformTable: function (e) {
        for (var t = GL.programs[e], r = GL.programInfos[e] = {
            uniforms: {},
            maxUniformLength: 0,
            maxAttributeLength: -1,
            maxUniformBlockNameLength: -1
        }, n = r.uniforms, o = GLctx.getProgramParameter(t, 35718), a = 0; a < o; ++a) {
            var i = GLctx.getActiveUniform(t, a), s = i.name;
            r.maxUniformLength = Math.max(r.maxUniformLength, s.length + 1), "]" == s.slice(-1) && (s = s.slice(0, s.lastIndexOf("[")));
            var c = GLctx.getUniformLocation(t, s);
            if (c) {
                var l = GL.getNewId(GL.uniforms);
                n[s] = [i.size, l], GL.uniforms[l] = c;
                for (var u = 1; u < i.size; ++u) {
                    var d = s + "[" + u + "]";
                    c = GLctx.getUniformLocation(t, d), l = GL.getNewId(GL.uniforms), GL.uniforms[l] = c
                }
            }
        }
    }
};

function _eglCreateContext(e, t, r, n) {
    if (62e3 != e) return EGL.setErrorCode(12296), 0;
    for (var o = 1; ;) {
        var a = HEAP32[n >> 2];
        if (12440 != a) {
            if (12344 == a) break;
            return EGL.setErrorCode(12292), 0
        }
        o = HEAP32[n + 4 >> 2], n += 8
    }
    return 2 != o ? (EGL.setErrorCode(12293), 0) : (EGL.contextAttributes.majorVersion = o - 1, EGL.contextAttributes.minorVersion = 0, EGL.context = GL.createContext(Module.canvas, EGL.contextAttributes), 0 != EGL.context ? (EGL.setErrorCode(12288), GL.makeContextCurrent(EGL.context), Module.useWebGL = !0, Browser.moduleContextCreatedCallbacks.forEach(function (e) {
        e()
    }), GL.makeContextCurrent(null), 62004) : (EGL.setErrorCode(12297), 0))
}

function _eglCreateWindowSurface(e, t, r, n) {
    return 62e3 != e ? (EGL.setErrorCode(12296), 0) : 62002 != t ? (EGL.setErrorCode(12293), 0) : (EGL.setErrorCode(12288), 62006)
}

function _eglDestroyContext(e, t) {
    return 62e3 != e ? (EGL.setErrorCode(12296), 0) : 62004 != t ? (EGL.setErrorCode(12294), 0) : (GL.deleteContext(EGL.context), EGL.setErrorCode(12288), EGL.currentContext == t && (EGL.currentContext = 0), 1)
}

function _eglDestroySurface(e, t) {
    return 62e3 != e ? (EGL.setErrorCode(12296), 0) : 62006 != t ? (EGL.setErrorCode(12301), 1) : (EGL.currentReadSurface == t && (EGL.currentReadSurface = 0), EGL.currentDrawSurface == t && (EGL.currentDrawSurface = 0), EGL.setErrorCode(12288), 1)
}

function _eglGetConfigAttrib(e, t, r, n) {
    if (62e3 != e) return EGL.setErrorCode(12296), 0;
    if (62002 != t) return EGL.setErrorCode(12293), 0;
    if (!n) return EGL.setErrorCode(12300), 0;
    switch (EGL.setErrorCode(12288), r) {
        case 12320:
            return HEAP32[n >> 2] = EGL.contextAttributes.alpha ? 32 : 24, 1;
        case 12321:
            return HEAP32[n >> 2] = EGL.contextAttributes.alpha ? 8 : 0, 1;
        case 12322:
        case 12323:
        case 12324:
            return HEAP32[n >> 2] = 8, 1;
        case 12325:
            return HEAP32[n >> 2] = EGL.contextAttributes.depth ? 24 : 0, 1;
        case 12326:
            return HEAP32[n >> 2] = EGL.contextAttributes.stencil ? 8 : 0, 1;
        case 12327:
            return HEAP32[n >> 2] = 12344, 1;
        case 12328:
            return HEAP32[n >> 2] = 62002, 1;
        case 12329:
            return HEAP32[n >> 2] = 0, 1;
        case 12330:
            return HEAP32[n >> 2] = 4096, 1;
        case 12331:
            return HEAP32[n >> 2] = 16777216, 1;
        case 12332:
            return HEAP32[n >> 2] = 4096, 1;
        case 12333:
        case 12334:
            return HEAP32[n >> 2] = 0, 1;
        case 12335:
            return HEAP32[n >> 2] = 12344, 1;
        case 12337:
            return HEAP32[n >> 2] = EGL.contextAttributes.antialias ? 4 : 0, 1;
        case 12338:
            return HEAP32[n >> 2] = EGL.contextAttributes.antialias ? 1 : 0, 1;
        case 12339:
            return HEAP32[n >> 2] = 4, 1;
        case 12340:
            return HEAP32[n >> 2] = 12344, 1;
        case 12341:
        case 12342:
        case 12343:
            return HEAP32[n >> 2] = -1, 1;
        case 12345:
        case 12346:
        case 12347:
            return HEAP32[n >> 2] = 0, 1;
        case 12348:
            return HEAP32[n >> 2] = 1, 1;
        case 12349:
        case 12350:
            return HEAP32[n >> 2] = 0, 1;
        case 12351:
            return HEAP32[n >> 2] = 12430, 1;
        case 12352:
            return HEAP32[n >> 2] = 4, 1;
        case 12354:
            return HEAP32[n >> 2] = 0, 1;
        default:
            return EGL.setErrorCode(12292), 0
    }
}

function _eglGetDisplay(e) {
    return EGL.setErrorCode(12288), 62e3
}

function _eglGetError() {
    return EGL.errorCode
}

function _eglInitialize(e, t, r) {
    return 62e3 == e ? (t && (HEAP32[t >> 2] = 1), r && (HEAP32[r >> 2] = 4), EGL.defaultDisplayInitialized = !0, EGL.setErrorCode(12288), 1) : (EGL.setErrorCode(12296), 0)
}

function _eglMakeCurrent(e, t, r, n) {
    return 62e3 != e ? (EGL.setErrorCode(12296), 0) : 0 != n && 62004 != n ? (EGL.setErrorCode(12294), 0) : 0 != r && 62006 != r || 0 != t && 62006 != t ? (EGL.setErrorCode(12301), 0) : (GL.makeContextCurrent(n ? EGL.context : null), EGL.currentContext = n, EGL.currentDrawSurface = t, EGL.currentReadSurface = r, EGL.setErrorCode(12288), 1)
}

function _eglQueryString(e, t) {
    if (62e3 != e) return EGL.setErrorCode(12296), 0;
    if (EGL.setErrorCode(12288), EGL.stringCache[t]) return EGL.stringCache[t];
    var r;
    switch (t) {
        case 12371:
            r = allocateUTF8("Emscripten");
            break;
        case 12372:
            r = allocateUTF8("1.4 Emscripten EGL");
            break;
        case 12373:
            r = allocateUTF8("");
            break;
        case 12429:
            r = allocateUTF8("OpenGL_ES");
            break;
        default:
            return EGL.setErrorCode(12300), 0
    }
    return EGL.stringCache[t] = r, r
}

function _eglSwapBuffers() {
    if (EGL.defaultDisplayInitialized) if (Module.ctx) {
        if (!Module.ctx.isContextLost()) return EGL.setErrorCode(12288), 1;
        EGL.setErrorCode(12302)
    } else EGL.setErrorCode(12290); else EGL.setErrorCode(12289);
    return 0
}

function _eglSwapInterval(e, t) {
    return 62e3 != e ? (EGL.setErrorCode(12296), 0) : (0 == t ? _emscripten_set_main_loop_timing(0, 0) : _emscripten_set_main_loop_timing(1, t), EGL.setErrorCode(12288), 1)
}

function _eglTerminate(e) {
    return 62e3 != e ? (EGL.setErrorCode(12296), 0) : (EGL.currentContext = 0, EGL.currentReadSurface = 0, EGL.currentDrawSurface = 0, EGL.defaultDisplayInitialized = !1, EGL.setErrorCode(12288), 1)
}

function _eglWaitClient() {
    return EGL.setErrorCode(12288), 1
}

function _eglWaitGL() {
    return _eglWaitClient()
}

function _eglWaitNative(e) {
    return EGL.setErrorCode(12288), 1
}

function _emscripten_asm_const_int(e, t, r) {
    var n = readAsmConstArgs(t, r);
    return ASM_CONSTS[e].apply(null, n)
}

function _emscripten_async_wget_data(e, t, r, n) {
    Browser.asyncLoad(UTF8ToString(e), function (e) {
        var n = _malloc(e.length);
        HEAPU8.set(e, n), wasmTable.get(r)(t, n, e.length), _free(n)
    }, function () {
        n && wasmTable.get(n)(t)
    }, !0)
}

function _emscripten_cancel_main_loop() {
    Browser.mainLoop.pause(), Browser.mainLoop.func = null
}

var JSEvents = {
    inEventHandler: 0, removeAllEventListeners: function () {
        for (var e = JSEvents.eventHandlers.length - 1; e >= 0; --e) JSEvents._removeHandler(e);
        JSEvents.eventHandlers = [], JSEvents.deferredCalls = []
    }, registerRemoveEventListeners: function () {
        JSEvents.removeEventListenersRegistered || (__ATEXIT__.push(JSEvents.removeAllEventListeners), JSEvents.removeEventListenersRegistered = !0)
    }, deferredCalls: [], deferCall: function (e, t, r) {
        function n(e, t) {
            if (e.length != t.length) return !1;
            for (var r in e) if (e[r] != t[r]) return !1;
            return !0
        }

        for (var o in JSEvents.deferredCalls) {
            var a = JSEvents.deferredCalls[o];
            if (a.targetFunction == e && n(a.argsList, r)) return
        }
        JSEvents.deferredCalls.push({
            targetFunction: e,
            precedence: t,
            argsList: r
        }), JSEvents.deferredCalls.sort(function (e, t) {
            return e.precedence < t.precedence
        })
    }, removeDeferredCalls: function (e) {
        for (var t = 0; t < JSEvents.deferredCalls.length; ++t) JSEvents.deferredCalls[t].targetFunction == e && (JSEvents.deferredCalls.splice(t, 1), --t)
    }, canPerformEventHandlerRequests: function () {
        return JSEvents.inEventHandler && JSEvents.currentEventHandler.allowsDeferredCalls
    }, runDeferredCalls: function () {
        if (JSEvents.canPerformEventHandlerRequests()) for (var e = 0; e < JSEvents.deferredCalls.length; ++e) {
            var t = JSEvents.deferredCalls[e];
            JSEvents.deferredCalls.splice(e, 1), --e, t.targetFunction.apply(null, t.argsList)
        }
    }, eventHandlers: [], removeAllHandlersOnTarget: function (e, t) {
        for (var r = 0; r < JSEvents.eventHandlers.length; ++r) JSEvents.eventHandlers[r].target != e || t && t != JSEvents.eventHandlers[r].eventTypeString || JSEvents._removeHandler(r--)
    }, _removeHandler: function (e) {
        var t = JSEvents.eventHandlers[e];
        t.target.removeEventListener(t.eventTypeString, t.eventListenerFunc, t.useCapture), JSEvents.eventHandlers.splice(e, 1)
    }, registerOrRemoveHandler: function (e) {
        var t = function (t) {
            ++JSEvents.inEventHandler, JSEvents.currentEventHandler = e, JSEvents.runDeferredCalls(), e.handlerFunc(t), JSEvents.runDeferredCalls(), --JSEvents.inEventHandler
        };
        if (e.callbackfunc) e.eventListenerFunc = t, e.target.addEventListener(e.eventTypeString, t, e.useCapture), JSEvents.eventHandlers.push(e), JSEvents.registerRemoveEventListeners(); else for (var r = 0; r < JSEvents.eventHandlers.length; ++r) JSEvents.eventHandlers[r].target == e.target && JSEvents.eventHandlers[r].eventTypeString == e.eventTypeString && JSEvents._removeHandler(r--)
    }, getNodeNameForTarget: function (e) {
        return e ? e == window ? "#window" : e == screen ? "#screen" : e && e.nodeName ? e.nodeName : "" : ""
    }, fullscreenEnabled: function () {
        return document.fullscreenEnabled || document.webkitFullscreenEnabled
    }
}, currentFullscreenStrategy = {};

function maybeCStringToJsString(e) {
    return e > 2 ? UTF8ToString(e) : e
}

var specialHTMLTargets = [0, "undefined" != typeof document ? document : 0, "undefined" != typeof window ? window : 0];

function findEventTarget(e) {
    return e = maybeCStringToJsString(e), specialHTMLTargets[e] || ("undefined" != typeof document ? document.querySelector(e) : void 0)
}

function findCanvasEventTarget(e) {
    return findEventTarget(e)
}

function _emscripten_get_canvas_element_size(e, t, r) {
    var n = findCanvasEventTarget(e);
    if (!n) return -4;
    HEAP32[t >> 2] = n.width, HEAP32[r >> 2] = n.height
}

function getCanvasElementSize(e) {
    var t = stackSave(), r = stackAlloc(8), n = r + 4, o = stackAlloc(e.id.length + 1);
    stringToUTF8(e.id, o, e.id.length + 1);
    _emscripten_get_canvas_element_size(o, r, n);
    var a = [HEAP32[r >> 2], HEAP32[n >> 2]];
    return stackRestore(t), a
}

function _emscripten_set_canvas_element_size(e, t, r) {
    var n = findCanvasEventTarget(e);
    return n ? (n.width = t, n.height = r, 0) : -4
}

function setCanvasElementSize(e, t, r) {
    if (e.controlTransferredOffscreen) {
        var n = stackSave(), o = stackAlloc(e.id.length + 1);
        stringToUTF8(e.id, o, e.id.length + 1), _emscripten_set_canvas_element_size(o, t, r), stackRestore(n)
    } else e.width = t, e.height = r
}

function registerRestoreOldStyle(e) {
    var t = getCanvasElementSize(e), r = t[0], n = t[1], o = e.style.width, a = e.style.height,
        i = e.style.backgroundColor, s = document.body.style.backgroundColor, c = e.style.paddingLeft,
        l = e.style.paddingRight, u = e.style.paddingTop, d = e.style.paddingBottom, f = e.style.marginLeft,
        m = e.style.marginRight, p = e.style.marginTop, _ = e.style.marginBottom, g = document.body.style.margin,
        v = document.documentElement.style.overflow, E = document.body.scroll, h = e.style.imageRendering;

    function S() {
        document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement || (document.removeEventListener("fullscreenchange", S), document.removeEventListener("webkitfullscreenchange", S), setCanvasElementSize(e, r, n), e.style.width = o, e.style.height = a, e.style.backgroundColor = i, s || (document.body.style.backgroundColor = "white"), document.body.style.backgroundColor = s, e.style.paddingLeft = c, e.style.paddingRight = l, e.style.paddingTop = u, e.style.paddingBottom = d, e.style.marginLeft = f, e.style.marginRight = m, e.style.marginTop = p, e.style.marginBottom = _, document.body.style.margin = g, document.documentElement.style.overflow = v, document.body.scroll = E, e.style.imageRendering = h, e.GLctxObject && e.GLctxObject.GLctx.viewport(0, 0, r, n), currentFullscreenStrategy.canvasResizedCallback && wasmTable.get(currentFullscreenStrategy.canvasResizedCallback)(37, 0, currentFullscreenStrategy.canvasResizedCallbackUserData))
    }

    return document.addEventListener("fullscreenchange", S), document.addEventListener("webkitfullscreenchange", S), S
}

function setLetterbox(e, t, r) {
    e.style.paddingLeft = e.style.paddingRight = r + "px", e.style.paddingTop = e.style.paddingBottom = t + "px"
}

function getBoundingClientRect(e) {
    return specialHTMLTargets.indexOf(e) < 0 ? e.getBoundingClientRect() : {left: 0, top: 0}
}

function _JSEvents_resizeCanvasForFullscreen(e, t) {
    var r = registerRestoreOldStyle(e), n = t.softFullscreen ? innerWidth : screen.width,
        o = t.softFullscreen ? innerHeight : screen.height, a = getBoundingClientRect(e), i = a.width, s = a.height,
        c = getCanvasElementSize(e), l = c[0], u = c[1];
    if (3 == t.scaleMode) setLetterbox(e, (o - s) / 2, (n - i) / 2), n = i, o = s; else if (2 == t.scaleMode) if (n * u < l * o) {
        var d = u * n / l;
        setLetterbox(e, (o - d) / 2, 0), o = d
    } else {
        var f = l * o / u;
        setLetterbox(e, 0, (n - f) / 2), n = f
    }
    e.style.backgroundColor || (e.style.backgroundColor = "black"), document.body.style.backgroundColor || (document.body.style.backgroundColor = "black"), e.style.width = n + "px", e.style.height = o + "px", 1 == t.filteringMode && (e.style.imageRendering = "optimizeSpeed", e.style.imageRendering = "-moz-crisp-edges", e.style.imageRendering = "-o-crisp-edges", e.style.imageRendering = "-webkit-optimize-contrast", e.style.imageRendering = "optimize-contrast", e.style.imageRendering = "crisp-edges", e.style.imageRendering = "pixelated");
    var m = 2 == t.canvasResolutionScaleMode ? devicePixelRatio : 1;
    if (0 != t.canvasResolutionScaleMode) {
        var p = n * m | 0, _ = o * m | 0;
        setCanvasElementSize(e, p, _), e.GLctxObject && e.GLctxObject.GLctx.viewport(0, 0, p, _)
    }
    return r
}

function _JSEvents_requestFullscreen(e, t) {
    if (0 == t.scaleMode && 0 == t.canvasResolutionScaleMode || _JSEvents_resizeCanvasForFullscreen(e, t), e.requestFullscreen) e.requestFullscreen(); else {
        if (!e.webkitRequestFullscreen) return JSEvents.fullscreenEnabled() ? -3 : -1;
        e.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT)
    }
    return currentFullscreenStrategy = t, t.canvasResizedCallback && wasmTable.get(t.canvasResizedCallback)(37, 0, t.canvasResizedCallbackUserData), 0
}

function _emscripten_exit_fullscreen() {
    if (!JSEvents.fullscreenEnabled()) return -1;
    JSEvents.removeDeferredCalls(_JSEvents_requestFullscreen);
    var e = specialHTMLTargets[1];
    if (e.exitFullscreen) e.fullscreenElement && e.exitFullscreen(); else {
        if (!e.webkitExitFullscreen) return -1;
        e.webkitFullscreenElement && e.webkitExitFullscreen()
    }
    return 0
}

function requestPointerLock(e) {
    if (e.requestPointerLock) e.requestPointerLock(); else {
        if (!e.msRequestPointerLock) return document.body.requestPointerLock || document.body.msRequestPointerLock ? -3 : -1;
        e.msRequestPointerLock()
    }
    return 0
}

function _emscripten_exit_pointerlock() {
    if (JSEvents.removeDeferredCalls(requestPointerLock), document.exitPointerLock) document.exitPointerLock(); else {
        if (!document.msExitPointerLock) return -1;
        document.msExitPointerLock()
    }
    return 0
}

function _emscripten_force_exit(e) {
    noExitRuntime = !1, exit(e)
}

function _emscripten_get_device_pixel_ratio() {
    return "number" == typeof devicePixelRatio && devicePixelRatio || 1
}

function _emscripten_get_element_css_size(e, t, r) {
    if (!(e = findEventTarget(e))) return -4;
    var n = getBoundingClientRect(e);
    return HEAPF64[t >> 3] = n.width, HEAPF64[r >> 3] = n.height, 0
}

function fillGamepadEventData(e, t) {
    HEAPF64[e >> 3] = t.timestamp;
    for (var r = 0; r < t.axes.length; ++r) HEAPF64[e + 8 * r + 16 >> 3] = t.axes[r];
    for (r = 0; r < t.buttons.length; ++r) "object" == typeof t.buttons[r] ? HEAPF64[e + 8 * r + 528 >> 3] = t.buttons[r].value : HEAPF64[e + 8 * r + 528 >> 3] = t.buttons[r];
    for (r = 0; r < t.buttons.length; ++r) "object" == typeof t.buttons[r] ? HEAP32[e + 4 * r + 1040 >> 2] = t.buttons[r].pressed : HEAP32[e + 4 * r + 1040 >> 2] = 1 == t.buttons[r];
    HEAP32[e + 1296 >> 2] = t.connected, HEAP32[e + 1300 >> 2] = t.index, HEAP32[e + 8 >> 2] = t.axes.length, HEAP32[e + 12 >> 2] = t.buttons.length, stringToUTF8(t.id, e + 1304, 64), stringToUTF8(t.mapping, e + 1368, 64)
}

function _emscripten_get_gamepad_status(e, t) {
    return e < 0 || e >= JSEvents.lastGamepadState.length ? -5 : JSEvents.lastGamepadState[e] ? (fillGamepadEventData(t, JSEvents.lastGamepadState[e]), 0) : -7
}

function _emscripten_get_num_gamepads() {
    return JSEvents.lastGamepadState.length
}

function _emscripten_glActiveTexture(e) {
    GLctx.activeTexture(e)
}

function _emscripten_glAttachShader(e, t) {
    GLctx.attachShader(GL.programs[e], GL.shaders[t])
}

function _emscripten_glBeginQueryEXT(e, t) {
    GLctx.disjointTimerQueryExt.beginQueryEXT(e, GL.timerQueriesEXT[t])
}

function _emscripten_glBindAttribLocation(e, t, r) {
    GLctx.bindAttribLocation(GL.programs[e], t, UTF8ToString(r))
}

function _emscripten_glBindBuffer(e, t) {
    GLctx.bindBuffer(e, GL.buffers[t])
}

function _emscripten_glBindFramebuffer(e, t) {
    GLctx.bindFramebuffer(e, GL.framebuffers[t])
}

function _emscripten_glBindRenderbuffer(e, t) {
    GLctx.bindRenderbuffer(e, GL.renderbuffers[t])
}

function _emscripten_glBindTexture(e, t) {
    GLctx.bindTexture(e, GL.textures[t])
}

function _emscripten_glBindVertexArrayOES(e) {
    GLctx.bindVertexArray(GL.vaos[e])
}

function _emscripten_glBlendColor(e, t, r, n) {
    GLctx.blendColor(e, t, r, n)
}

function _emscripten_glBlendEquation(e) {
    GLctx.blendEquation(e)
}

function _emscripten_glBlendEquationSeparate(e, t) {
    GLctx.blendEquationSeparate(e, t)
}

function _emscripten_glBlendFunc(e, t) {
    GLctx.blendFunc(e, t)
}

function _emscripten_glBlendFuncSeparate(e, t, r, n) {
    GLctx.blendFuncSeparate(e, t, r, n)
}

function _emscripten_glBufferData(e, t, r, n) {
    GLctx.bufferData(e, r ? HEAPU8.subarray(r, r + t) : t, n)
}

function _emscripten_glBufferSubData(e, t, r, n) {
    GLctx.bufferSubData(e, t, HEAPU8.subarray(n, n + r))
}

function _emscripten_glCheckFramebufferStatus(e) {
    return GLctx.checkFramebufferStatus(e)
}

function _emscripten_glClear(e) {
    GLctx.clear(e)
}

function _emscripten_glClearColor(e, t, r, n) {
    GLctx.clearColor(e, t, r, n)
}

function _emscripten_glClearDepthf(e) {
    GLctx.clearDepth(e)
}

function _emscripten_glClearStencil(e) {
    GLctx.clearStencil(e)
}

function _emscripten_glColorMask(e, t, r, n) {
    GLctx.colorMask(!!e, !!t, !!r, !!n)
}

function _emscripten_glCompileShader(e) {
    GLctx.compileShader(GL.shaders[e])
}

function _emscripten_glCompressedTexImage2D(e, t, r, n, o, a, i, s) {
    GLctx.compressedTexImage2D(e, t, r, n, o, a, s ? HEAPU8.subarray(s, s + i) : null)
}

function _emscripten_glCompressedTexSubImage2D(e, t, r, n, o, a, i, s, c) {
    GLctx.compressedTexSubImage2D(e, t, r, n, o, a, i, c ? HEAPU8.subarray(c, c + s) : null)
}

function _emscripten_glCopyTexImage2D(e, t, r, n, o, a, i, s) {
    GLctx.copyTexImage2D(e, t, r, n, o, a, i, s)
}

function _emscripten_glCopyTexSubImage2D(e, t, r, n, o, a, i, s) {
    GLctx.copyTexSubImage2D(e, t, r, n, o, a, i, s)
}

function _emscripten_glCreateProgram() {
    var e = GL.getNewId(GL.programs), t = GLctx.createProgram();
    return t.name = e, GL.programs[e] = t, e
}

function _emscripten_glCreateShader(e) {
    var t = GL.getNewId(GL.shaders);
    return GL.shaders[t] = GLctx.createShader(e), t
}

function _emscripten_glCullFace(e) {
    GLctx.cullFace(e)
}

function _emscripten_glDeleteBuffers(e, t) {
    for (var r = 0; r < e; r++) {
        var n = HEAP32[t + 4 * r >> 2], o = GL.buffers[n];
        o && (GLctx.deleteBuffer(o), o.name = 0, GL.buffers[n] = null)
    }
}

function _emscripten_glDeleteFramebuffers(e, t) {
    for (var r = 0; r < e; ++r) {
        var n = HEAP32[t + 4 * r >> 2], o = GL.framebuffers[n];
        o && (GLctx.deleteFramebuffer(o), o.name = 0, GL.framebuffers[n] = null)
    }
}

function _emscripten_glDeleteProgram(e) {
    if (e) {
        var t = GL.programs[e];
        t ? (GLctx.deleteProgram(t), t.name = 0, GL.programs[e] = null, GL.programInfos[e] = null) : GL.recordError(1281)
    }
}

function _emscripten_glDeleteQueriesEXT(e, t) {
    for (var r = 0; r < e; r++) {
        var n = HEAP32[t + 4 * r >> 2], o = GL.timerQueriesEXT[n];
        o && (GLctx.disjointTimerQueryExt.deleteQueryEXT(o), GL.timerQueriesEXT[n] = null)
    }
}

function _emscripten_glDeleteRenderbuffers(e, t) {
    for (var r = 0; r < e; r++) {
        var n = HEAP32[t + 4 * r >> 2], o = GL.renderbuffers[n];
        o && (GLctx.deleteRenderbuffer(o), o.name = 0, GL.renderbuffers[n] = null)
    }
}

function _emscripten_glDeleteShader(e) {
    if (e) {
        var t = GL.shaders[e];
        t ? (GLctx.deleteShader(t), GL.shaders[e] = null) : GL.recordError(1281)
    }
}

function _emscripten_glDeleteTextures(e, t) {
    for (var r = 0; r < e; r++) {
        var n = HEAP32[t + 4 * r >> 2], o = GL.textures[n];
        o && (GLctx.deleteTexture(o), o.name = 0, GL.textures[n] = null)
    }
}

function _emscripten_glDeleteVertexArraysOES(e, t) {
    for (var r = 0; r < e; r++) {
        var n = HEAP32[t + 4 * r >> 2];
        GLctx.deleteVertexArray(GL.vaos[n]), GL.vaos[n] = null
    }
}

function _emscripten_glDepthFunc(e) {
    GLctx.depthFunc(e)
}

function _emscripten_glDepthMask(e) {
    GLctx.depthMask(!!e)
}

function _emscripten_glDepthRangef(e, t) {
    GLctx.depthRange(e, t)
}

function _emscripten_glDetachShader(e, t) {
    GLctx.detachShader(GL.programs[e], GL.shaders[t])
}

function _emscripten_glDisable(e) {
    GLctx.disable(e)
}

function _emscripten_glDisableVertexAttribArray(e) {
    GLctx.disableVertexAttribArray(e)
}

function _emscripten_glDrawArrays(e, t, r) {
    GLctx.drawArrays(e, t, r)
}

function _emscripten_glDrawArraysInstancedANGLE(e, t, r, n) {
    GLctx.drawArraysInstanced(e, t, r, n)
}

var tempFixedLengthArray = [];

function _emscripten_glDrawBuffersWEBGL(e, t) {
    for (var r = tempFixedLengthArray[e], n = 0; n < e; n++) r[n] = HEAP32[t + 4 * n >> 2];
    GLctx.drawBuffers(r)
}

function _emscripten_glDrawElements(e, t, r, n) {
    GLctx.drawElements(e, t, r, n)
}

function _emscripten_glDrawElementsInstancedANGLE(e, t, r, n, o) {
    GLctx.drawElementsInstanced(e, t, r, n, o)
}

function _emscripten_glEnable(e) {
    GLctx.enable(e)
}

function _emscripten_glEnableVertexAttribArray(e) {
    GLctx.enableVertexAttribArray(e)
}

function _emscripten_glEndQueryEXT(e) {
    GLctx.disjointTimerQueryExt.endQueryEXT(e)
}

function _emscripten_glFinish() {
    GLctx.finish()
}

function _emscripten_glFlush() {
    GLctx.flush()
}

function _emscripten_glFramebufferRenderbuffer(e, t, r, n) {
    GLctx.framebufferRenderbuffer(e, t, r, GL.renderbuffers[n])
}

function _emscripten_glFramebufferTexture2D(e, t, r, n, o) {
    GLctx.framebufferTexture2D(e, t, r, GL.textures[n], o)
}

function _emscripten_glFrontFace(e) {
    GLctx.frontFace(e)
}

function __glGenObject(e, t, r, n) {
    for (var o = 0; o < e; o++) {
        var a = GLctx[r](), i = a && GL.getNewId(n);
        a ? (a.name = i, n[i] = a) : GL.recordError(1282), HEAP32[t + 4 * o >> 2] = i
    }
}

function _emscripten_glGenBuffers(e, t) {
    __glGenObject(e, t, "createBuffer", GL.buffers)
}

function _emscripten_glGenFramebuffers(e, t) {
    __glGenObject(e, t, "createFramebuffer", GL.framebuffers)
}

function _emscripten_glGenQueriesEXT(e, t) {
    for (var r = 0; r < e; r++) {
        var n = GLctx.disjointTimerQueryExt.createQueryEXT();
        if (!n) {
            for (GL.recordError(1282); r < e;) HEAP32[t + 4 * r++ >> 2] = 0;
            return
        }
        var o = GL.getNewId(GL.timerQueriesEXT);
        n.name = o, GL.timerQueriesEXT[o] = n, HEAP32[t + 4 * r >> 2] = o
    }
}

function _emscripten_glGenRenderbuffers(e, t) {
    __glGenObject(e, t, "createRenderbuffer", GL.renderbuffers)
}

function _emscripten_glGenTextures(e, t) {
    __glGenObject(e, t, "createTexture", GL.textures)
}

function _emscripten_glGenVertexArraysOES(e, t) {
    __glGenObject(e, t, "createVertexArray", GL.vaos)
}

function _emscripten_glGenerateMipmap(e) {
    GLctx.generateMipmap(e)
}

function __glGetActiveAttribOrUniform(e, t, r, n, o, a, i, s) {
    t = GL.programs[t];
    var c = GLctx[e](t, r);
    if (c) {
        var l = s && stringToUTF8(c.name, s, n);
        o && (HEAP32[o >> 2] = l), a && (HEAP32[a >> 2] = c.size), i && (HEAP32[i >> 2] = c.type)
    }
}

function _emscripten_glGetActiveAttrib(e, t, r, n, o, a, i) {
    __glGetActiveAttribOrUniform("getActiveAttrib", e, t, r, n, o, a, i)
}

function _emscripten_glGetActiveUniform(e, t, r, n, o, a, i) {
    __glGetActiveAttribOrUniform("getActiveUniform", e, t, r, n, o, a, i)
}

function _emscripten_glGetAttachedShaders(e, t, r, n) {
    var o = GLctx.getAttachedShaders(GL.programs[e]), a = o.length;
    a > t && (a = t), HEAP32[r >> 2] = a;
    for (var i = 0; i < a; ++i) {
        var s = GL.shaders.indexOf(o[i]);
        HEAP32[n + 4 * i >> 2] = s
    }
}

function _emscripten_glGetAttribLocation(e, t) {
    return GLctx.getAttribLocation(GL.programs[e], UTF8ToString(t))
}

function writeI53ToI64(e, t) {
    HEAPU32[e >> 2] = t, HEAPU32[e + 4 >> 2] = (t - HEAPU32[e >> 2]) / 4294967296
}

function emscriptenWebGLGet(e, t, r) {
    if (t) {
        var n = void 0;
        switch (e) {
            case 36346:
                n = 1;
                break;
            case 36344:
                return void (0 != r && 1 != r && GL.recordError(1280));
            case 36345:
                n = 0;
                break;
            case 34466:
                var o = GLctx.getParameter(34467);
                n = o ? o.length : 0
        }
        if (void 0 === n) {
            var a = GLctx.getParameter(e);
            switch (typeof a) {
                case"number":
                    n = a;
                    break;
                case"boolean":
                    n = a ? 1 : 0;
                    break;
                case"string":
                    return void GL.recordError(1280);
                case"object":
                    if (null === a) switch (e) {
                        case 34964:
                        case 35725:
                        case 34965:
                        case 36006:
                        case 36007:
                        case 32873:
                        case 34229:
                        case 34068:
                            n = 0;
                            break;
                        default:
                            return void GL.recordError(1280)
                    } else {
                        if (a instanceof Float32Array || a instanceof Uint32Array || a instanceof Int32Array || a instanceof Array) {
                            for (var i = 0; i < a.length; ++i) switch (r) {
                                case 0:
                                    HEAP32[t + 4 * i >> 2] = a[i];
                                    break;
                                case 2:
                                    HEAPF32[t + 4 * i >> 2] = a[i];
                                    break;
                                case 4:
                                    HEAP8[t + i >> 0] = a[i] ? 1 : 0
                            }
                            return
                        }
                        try {
                            n = 0 | a.name
                        } catch (t) {
                            return GL.recordError(1280), void err("GL_INVALID_ENUM in glGet" + r + "v: Unknown object returned from WebGL getParameter(" + e + ")! (error: " + t + ")")
                        }
                    }
                    break;
                default:
                    return GL.recordError(1280), void err("GL_INVALID_ENUM in glGet" + r + "v: Native code calling glGet" + r + "v(" + e + ") and it returns " + a + " of type " + typeof a + "!")
            }
        }
        switch (r) {
            case 1:
                writeI53ToI64(t, n);
                break;
            case 0:
                HEAP32[t >> 2] = n;
                break;
            case 2:
                HEAPF32[t >> 2] = n;
                break;
            case 4:
                HEAP8[t >> 0] = n ? 1 : 0
        }
    } else GL.recordError(1281)
}

function _emscripten_glGetBooleanv(e, t) {
    emscriptenWebGLGet(e, t, 4)
}

function _emscripten_glGetBufferParameteriv(e, t, r) {
    r ? HEAP32[r >> 2] = GLctx.getBufferParameter(e, t) : GL.recordError(1281)
}

function _emscripten_glGetError() {
    var e = GLctx.getError() || GL.lastError;
    return GL.lastError = 0, e
}

function _emscripten_glGetFloatv(e, t) {
    emscriptenWebGLGet(e, t, 2)
}

function _emscripten_glGetFramebufferAttachmentParameteriv(e, t, r, n) {
    var o = GLctx.getFramebufferAttachmentParameter(e, t, r);
    (o instanceof WebGLRenderbuffer || o instanceof WebGLTexture) && (o = 0 | o.name), HEAP32[n >> 2] = o
}

function _emscripten_glGetIntegerv(e, t) {
    emscriptenWebGLGet(e, t, 0)
}

function _emscripten_glGetProgramInfoLog(e, t, r, n) {
    var o = GLctx.getProgramInfoLog(GL.programs[e]);
    null === o && (o = "(unknown error)");
    var a = t > 0 && n ? stringToUTF8(o, n, t) : 0;
    r && (HEAP32[r >> 2] = a)
}

function _emscripten_glGetProgramiv(e, t, r) {
    if (r) if (e >= GL.counter) GL.recordError(1281); else {
        var n = GL.programInfos[e];
        if (n) if (35716 == t) {
            var o = GLctx.getProgramInfoLog(GL.programs[e]);
            null === o && (o = "(unknown error)"), HEAP32[r >> 2] = o.length + 1
        } else if (35719 == t) HEAP32[r >> 2] = n.maxUniformLength; else if (35722 == t) {
            if (-1 == n.maxAttributeLength) {
                e = GL.programs[e];
                var a = GLctx.getProgramParameter(e, 35721);
                n.maxAttributeLength = 0;
                for (var i = 0; i < a; ++i) {
                    var s = GLctx.getActiveAttrib(e, i);
                    n.maxAttributeLength = Math.max(n.maxAttributeLength, s.name.length + 1)
                }
            }
            HEAP32[r >> 2] = n.maxAttributeLength
        } else if (35381 == t) {
            if (-1 == n.maxUniformBlockNameLength) {
                e = GL.programs[e];
                var c = GLctx.getProgramParameter(e, 35382);
                n.maxUniformBlockNameLength = 0;
                for (i = 0; i < c; ++i) {
                    var l = GLctx.getActiveUniformBlockName(e, i);
                    n.maxUniformBlockNameLength = Math.max(n.maxUniformBlockNameLength, l.length + 1)
                }
            }
            HEAP32[r >> 2] = n.maxUniformBlockNameLength
        } else HEAP32[r >> 2] = GLctx.getProgramParameter(GL.programs[e], t); else GL.recordError(1282)
    } else GL.recordError(1281)
}

function _emscripten_glGetQueryObjecti64vEXT(e, t, r) {
    if (r) {
        var n = GL.timerQueriesEXT[e], o = GLctx.disjointTimerQueryExt.getQueryObjectEXT(n, t);
        writeI53ToI64(r, "boolean" == typeof o ? o ? 1 : 0 : o)
    } else GL.recordError(1281)
}

function _emscripten_glGetQueryObjectivEXT(e, t, r) {
    if (r) {
        var n, o = GL.timerQueriesEXT[e], a = GLctx.disjointTimerQueryExt.getQueryObjectEXT(o, t);
        n = "boolean" == typeof a ? a ? 1 : 0 : a, HEAP32[r >> 2] = n
    } else GL.recordError(1281)
}

function _emscripten_glGetQueryObjectui64vEXT(e, t, r) {
    if (r) {
        var n = GL.timerQueriesEXT[e], o = GLctx.disjointTimerQueryExt.getQueryObjectEXT(n, t);
        writeI53ToI64(r, "boolean" == typeof o ? o ? 1 : 0 : o)
    } else GL.recordError(1281)
}

function _emscripten_glGetQueryObjectuivEXT(e, t, r) {
    if (r) {
        var n, o = GL.timerQueriesEXT[e], a = GLctx.disjointTimerQueryExt.getQueryObjectEXT(o, t);
        n = "boolean" == typeof a ? a ? 1 : 0 : a, HEAP32[r >> 2] = n
    } else GL.recordError(1281)
}

function _emscripten_glGetQueryivEXT(e, t, r) {
    r ? HEAP32[r >> 2] = GLctx.disjointTimerQueryExt.getQueryEXT(e, t) : GL.recordError(1281)
}

function _emscripten_glGetRenderbufferParameteriv(e, t, r) {
    r ? HEAP32[r >> 2] = GLctx.getRenderbufferParameter(e, t) : GL.recordError(1281)
}

function _emscripten_glGetShaderInfoLog(e, t, r, n) {
    var o = GLctx.getShaderInfoLog(GL.shaders[e]);
    null === o && (o = "(unknown error)");
    var a = t > 0 && n ? stringToUTF8(o, n, t) : 0;
    r && (HEAP32[r >> 2] = a)
}

function _emscripten_glGetShaderPrecisionFormat(e, t, r, n) {
    var o = GLctx.getShaderPrecisionFormat(e, t);
    HEAP32[r >> 2] = o.rangeMin, HEAP32[r + 4 >> 2] = o.rangeMax, HEAP32[n >> 2] = o.precision
}

function _emscripten_glGetShaderSource(e, t, r, n) {
    var o = GLctx.getShaderSource(GL.shaders[e]);
    if (o) {
        var a = t > 0 && n ? stringToUTF8(o, n, t) : 0;
        r && (HEAP32[r >> 2] = a)
    }
}

function _emscripten_glGetShaderiv(e, t, r) {
    if (r) if (35716 == t) {
        var n = GLctx.getShaderInfoLog(GL.shaders[e]);
        null === n && (n = "(unknown error)");
        var o = n ? n.length + 1 : 0;
        HEAP32[r >> 2] = o
    } else if (35720 == t) {
        var a = GLctx.getShaderSource(GL.shaders[e]), i = a ? a.length + 1 : 0;
        HEAP32[r >> 2] = i
    } else HEAP32[r >> 2] = GLctx.getShaderParameter(GL.shaders[e], t); else GL.recordError(1281)
}

function stringToNewUTF8(e) {
    var t = lengthBytesUTF8(e) + 1, r = _malloc(t);
    return stringToUTF8(e, r, t), r
}

function _emscripten_glGetString(e) {
    if (GL.stringCache[e]) return GL.stringCache[e];
    var t;
    switch (e) {
        case 7939:
            var r = GLctx.getSupportedExtensions() || [];
            t = stringToNewUTF8((r = r.concat(r.map(function (e) {
                return "GL_" + e
            }))).join(" "));
            break;
        case 7936:
        case 7937:
        case 37445:
        case 37446:
            var n = GLctx.getParameter(e);
            n || GL.recordError(1280), t = stringToNewUTF8(n);
            break;
        case 7938:
            var o = GLctx.getParameter(7938);
            t = stringToNewUTF8(o = "OpenGL ES 2.0 (" + o + ")");
            break;
        case 35724:
            var a = GLctx.getParameter(35724), i = a.match(/^WebGL GLSL ES ([0-9]\.[0-9][0-9]?)(?:$| .*)/);
            null !== i && (3 == i[1].length && (i[1] = i[1] + "0"), a = "OpenGL ES GLSL ES " + i[1] + " (" + a + ")"), t = stringToNewUTF8(a);
            break;
        default:
            return GL.recordError(1280), 0
    }
    return GL.stringCache[e] = t, t
}

function _emscripten_glGetTexParameterfv(e, t, r) {
    r ? HEAPF32[r >> 2] = GLctx.getTexParameter(e, t) : GL.recordError(1281)
}

function _emscripten_glGetTexParameteriv(e, t, r) {
    r ? HEAP32[r >> 2] = GLctx.getTexParameter(e, t) : GL.recordError(1281)
}

function jstoi_q(e) {
    return parseInt(e)
}

function _emscripten_glGetUniformLocation(e, t) {
    var r = 0;
    if ("]" == (t = UTF8ToString(t))[t.length - 1]) {
        var n = t.lastIndexOf("[");
        r = "]" != t[n + 1] ? jstoi_q(t.slice(n + 1)) : 0, t = t.slice(0, n)
    }
    var o = GL.programInfos[e] && GL.programInfos[e].uniforms[t];
    return o && r >= 0 && r < o[0] ? o[1] + r : -1
}

function emscriptenWebGLGetUniform(e, t, r, n) {
    if (r) {
        var o = GLctx.getUniform(GL.programs[e], GL.uniforms[t]);
        if ("number" == typeof o || "boolean" == typeof o) switch (n) {
            case 0:
                HEAP32[r >> 2] = o;
                break;
            case 2:
                HEAPF32[r >> 2] = o
        } else for (var a = 0; a < o.length; a++) switch (n) {
            case 0:
                HEAP32[r + 4 * a >> 2] = o[a];
                break;
            case 2:
                HEAPF32[r + 4 * a >> 2] = o[a]
        }
    } else GL.recordError(1281)
}

function _emscripten_glGetUniformfv(e, t, r) {
    emscriptenWebGLGetUniform(e, t, r, 2)
}

function _emscripten_glGetUniformiv(e, t, r) {
    emscriptenWebGLGetUniform(e, t, r, 0)
}

function _emscripten_glGetVertexAttribPointerv(e, t, r) {
    r ? HEAP32[r >> 2] = GLctx.getVertexAttribOffset(e, t) : GL.recordError(1281)
}

function emscriptenWebGLGetVertexAttrib(e, t, r, n) {
    if (r) {
        var o = GLctx.getVertexAttrib(e, t);
        if (34975 == t) HEAP32[r >> 2] = o && o.name; else if ("number" == typeof o || "boolean" == typeof o) switch (n) {
            case 0:
                HEAP32[r >> 2] = o;
                break;
            case 2:
                HEAPF32[r >> 2] = o;
                break;
            case 5:
                HEAP32[r >> 2] = Math.fround(o)
        } else for (var a = 0; a < o.length; a++) switch (n) {
            case 0:
                HEAP32[r + 4 * a >> 2] = o[a];
                break;
            case 2:
                HEAPF32[r + 4 * a >> 2] = o[a];
                break;
            case 5:
                HEAP32[r + 4 * a >> 2] = Math.fround(o[a])
        }
    } else GL.recordError(1281)
}

function _emscripten_glGetVertexAttribfv(e, t, r) {
    emscriptenWebGLGetVertexAttrib(e, t, r, 2)
}

function _emscripten_glGetVertexAttribiv(e, t, r) {
    emscriptenWebGLGetVertexAttrib(e, t, r, 5)
}

function _emscripten_glHint(e, t) {
    GLctx.hint(e, t)
}

function _emscripten_glIsBuffer(e) {
    var t = GL.buffers[e];
    return t ? GLctx.isBuffer(t) : 0
}

function _emscripten_glIsEnabled(e) {
    return GLctx.isEnabled(e)
}

function _emscripten_glIsFramebuffer(e) {
    var t = GL.framebuffers[e];
    return t ? GLctx.isFramebuffer(t) : 0
}

function _emscripten_glIsProgram(e) {
    return (e = GL.programs[e]) ? GLctx.isProgram(e) : 0
}

function _emscripten_glIsQueryEXT(e) {
    var t = GL.timerQueriesEXT[e];
    return t ? GLctx.disjointTimerQueryExt.isQueryEXT(t) : 0
}

function _emscripten_glIsRenderbuffer(e) {
    var t = GL.renderbuffers[e];
    return t ? GLctx.isRenderbuffer(t) : 0
}

function _emscripten_glIsShader(e) {
    var t = GL.shaders[e];
    return t ? GLctx.isShader(t) : 0
}

function _emscripten_glIsTexture(e) {
    var t = GL.textures[e];
    return t ? GLctx.isTexture(t) : 0
}

function _emscripten_glIsVertexArrayOES(e) {
    var t = GL.vaos[e];
    return t ? GLctx.isVertexArray(t) : 0
}

function _emscripten_glLineWidth(e) {
    GLctx.lineWidth(e)
}

function _emscripten_glLinkProgram(e) {
    GLctx.linkProgram(GL.programs[e]), GL.populateUniformTable(e)
}

function _emscripten_glPixelStorei(e, t) {
    3317 == e && (GL.unpackAlignment = t), GLctx.pixelStorei(e, t)
}

function _emscripten_glPolygonOffset(e, t) {
    GLctx.polygonOffset(e, t)
}

function _emscripten_glQueryCounterEXT(e, t) {
    GLctx.disjointTimerQueryExt.queryCounterEXT(GL.timerQueriesEXT[e], t)
}

function computeUnpackAlignedImageSize(e, t, r, n) {
    var o;
    return t * (e * r + (o = n) - 1 & -o)
}

function __colorChannelsInGlTextureFormat(e) {
    return {5: 3, 6: 4, 8: 2, 29502: 3, 29504: 4}[e - 6402] || 1
}

function heapObjectForWebGLType(e) {
    return 1 == (e -= 5120) ? HEAPU8 : 4 == e ? HEAP32 : 6 == e ? HEAPF32 : 5 == e || 28922 == e ? HEAPU32 : HEAPU16
}

function heapAccessShiftForWebGLHeap(e) {
    return 31 - Math.clz32(e.BYTES_PER_ELEMENT)
}

function emscriptenWebGLGetTexPixelData(e, t, r, n, o, a) {
    var i = heapObjectForWebGLType(e), s = heapAccessShiftForWebGLHeap(i), c = 1 << s,
        l = computeUnpackAlignedImageSize(r, n, __colorChannelsInGlTextureFormat(t) * c, GL.unpackAlignment);
    return i.subarray(o >> s, o + l >> s)
}

function _emscripten_glReadPixels(e, t, r, n, o, a, i) {
    var s = emscriptenWebGLGetTexPixelData(a, o, r, n, i, o);
    s ? GLctx.readPixels(e, t, r, n, o, a, s) : GL.recordError(1280)
}

function _emscripten_glReleaseShaderCompiler() {
}

function _emscripten_glRenderbufferStorage(e, t, r, n) {
    GLctx.renderbufferStorage(e, t, r, n)
}

function _emscripten_glSampleCoverage(e, t) {
    GLctx.sampleCoverage(e, !!t)
}

function _emscripten_glScissor(e, t, r, n) {
    GLctx.scissor(e, t, r, n)
}

function _emscripten_glShaderBinary() {
    GL.recordError(1280)
}

function _emscripten_glShaderSource(e, t, r, n) {
    var o = GL.getSource(e, t, r, n);
    GLctx.shaderSource(GL.shaders[e], o)
}

function _emscripten_glStencilFunc(e, t, r) {
    GLctx.stencilFunc(e, t, r)
}

function _emscripten_glStencilFuncSeparate(e, t, r, n) {
    GLctx.stencilFuncSeparate(e, t, r, n)
}

function _emscripten_glStencilMask(e) {
    GLctx.stencilMask(e)
}

function _emscripten_glStencilMaskSeparate(e, t) {
    GLctx.stencilMaskSeparate(e, t)
}

function _emscripten_glStencilOp(e, t, r) {
    GLctx.stencilOp(e, t, r)
}

function _emscripten_glStencilOpSeparate(e, t, r, n) {
    GLctx.stencilOpSeparate(e, t, r, n)
}

function _emscripten_glTexImage2D(e, t, r, n, o, a, i, s, c) {
    GLctx.texImage2D(e, t, r, n, o, a, i, s, c ? emscriptenWebGLGetTexPixelData(s, i, n, o, c, r) : null)
}

function _emscripten_glTexParameterf(e, t, r) {
    GLctx.texParameterf(e, t, r)
}

function _emscripten_glTexParameterfv(e, t, r) {
    var n = HEAPF32[r >> 2];
    GLctx.texParameterf(e, t, n)
}

function _emscripten_glTexParameteri(e, t, r) {
    GLctx.texParameteri(e, t, r)
}

function _emscripten_glTexParameteriv(e, t, r) {
    var n = HEAP32[r >> 2];
    GLctx.texParameteri(e, t, n)
}

function _emscripten_glTexSubImage2D(e, t, r, n, o, a, i, s, c) {
    var l = null;
    c && (l = emscriptenWebGLGetTexPixelData(s, i, o, a, c, 0)), GLctx.texSubImage2D(e, t, r, n, o, a, i, s, l)
}

function _emscripten_glUniform1f(e, t) {
    GLctx.uniform1f(GL.uniforms[e], t)
}

var miniTempWebGLFloatBuffers = [];

function _emscripten_glUniform1fv(e, t, r) {
    if (t <= 288) for (var n = miniTempWebGLFloatBuffers[t - 1], o = 0; o < t; ++o) n[o] = HEAPF32[r + 4 * o >> 2]; else n = HEAPF32.subarray(r >> 2, r + 4 * t >> 2);
    GLctx.uniform1fv(GL.uniforms[e], n)
}

function _emscripten_glUniform1i(e, t) {
    GLctx.uniform1i(GL.uniforms[e], t)
}

var __miniTempWebGLIntBuffers = [];

function _emscripten_glUniform1iv(e, t, r) {
    if (t <= 288) for (var n = __miniTempWebGLIntBuffers[t - 1], o = 0; o < t; ++o) n[o] = HEAP32[r + 4 * o >> 2]; else n = HEAP32.subarray(r >> 2, r + 4 * t >> 2);
    GLctx.uniform1iv(GL.uniforms[e], n)
}

function _emscripten_glUniform2f(e, t, r) {
    GLctx.uniform2f(GL.uniforms[e], t, r)
}

function _emscripten_glUniform2fv(e, t, r) {
    if (t <= 144) for (var n = miniTempWebGLFloatBuffers[2 * t - 1], o = 0; o < 2 * t; o += 2) n[o] = HEAPF32[r + 4 * o >> 2], n[o + 1] = HEAPF32[r + (4 * o + 4) >> 2]; else n = HEAPF32.subarray(r >> 2, r + 8 * t >> 2);
    GLctx.uniform2fv(GL.uniforms[e], n)
}

function _emscripten_glUniform2i(e, t, r) {
    GLctx.uniform2i(GL.uniforms[e], t, r)
}

function _emscripten_glUniform2iv(e, t, r) {
    if (t <= 144) for (var n = __miniTempWebGLIntBuffers[2 * t - 1], o = 0; o < 2 * t; o += 2) n[o] = HEAP32[r + 4 * o >> 2], n[o + 1] = HEAP32[r + (4 * o + 4) >> 2]; else n = HEAP32.subarray(r >> 2, r + 8 * t >> 2);
    GLctx.uniform2iv(GL.uniforms[e], n)
}

function _emscripten_glUniform3f(e, t, r, n) {
    GLctx.uniform3f(GL.uniforms[e], t, r, n)
}

function _emscripten_glUniform3fv(e, t, r) {
    if (t <= 96) for (var n = miniTempWebGLFloatBuffers[3 * t - 1], o = 0; o < 3 * t; o += 3) n[o] = HEAPF32[r + 4 * o >> 2], n[o + 1] = HEAPF32[r + (4 * o + 4) >> 2], n[o + 2] = HEAPF32[r + (4 * o + 8) >> 2]; else n = HEAPF32.subarray(r >> 2, r + 12 * t >> 2);
    GLctx.uniform3fv(GL.uniforms[e], n)
}

function _emscripten_glUniform3i(e, t, r, n) {
    GLctx.uniform3i(GL.uniforms[e], t, r, n)
}

function _emscripten_glUniform3iv(e, t, r) {
    if (t <= 96) for (var n = __miniTempWebGLIntBuffers[3 * t - 1], o = 0; o < 3 * t; o += 3) n[o] = HEAP32[r + 4 * o >> 2], n[o + 1] = HEAP32[r + (4 * o + 4) >> 2], n[o + 2] = HEAP32[r + (4 * o + 8) >> 2]; else n = HEAP32.subarray(r >> 2, r + 12 * t >> 2);
    GLctx.uniform3iv(GL.uniforms[e], n)
}

function _emscripten_glUniform4f(e, t, r, n, o) {
    GLctx.uniform4f(GL.uniforms[e], t, r, n, o)
}

function _emscripten_glUniform4fv(e, t, r) {
    if (t <= 72) {
        var n = miniTempWebGLFloatBuffers[4 * t - 1], o = HEAPF32;
        r >>= 2;
        for (var a = 0; a < 4 * t; a += 4) {
            var i = r + a;
            n[a] = o[i], n[a + 1] = o[i + 1], n[a + 2] = o[i + 2], n[a + 3] = o[i + 3]
        }
    } else n = HEAPF32.subarray(r >> 2, r + 16 * t >> 2);
    GLctx.uniform4fv(GL.uniforms[e], n)
}

function _emscripten_glUniform4i(e, t, r, n, o) {
    GLctx.uniform4i(GL.uniforms[e], t, r, n, o)
}

function _emscripten_glUniform4iv(e, t, r) {
    if (t <= 72) for (var n = __miniTempWebGLIntBuffers[4 * t - 1], o = 0; o < 4 * t; o += 4) n[o] = HEAP32[r + 4 * o >> 2], n[o + 1] = HEAP32[r + (4 * o + 4) >> 2], n[o + 2] = HEAP32[r + (4 * o + 8) >> 2], n[o + 3] = HEAP32[r + (4 * o + 12) >> 2]; else n = HEAP32.subarray(r >> 2, r + 16 * t >> 2);
    GLctx.uniform4iv(GL.uniforms[e], n)
}

function _emscripten_glUniformMatrix2fv(e, t, r, n) {
    if (t <= 72) for (var o = miniTempWebGLFloatBuffers[4 * t - 1], a = 0; a < 4 * t; a += 4) o[a] = HEAPF32[n + 4 * a >> 2], o[a + 1] = HEAPF32[n + (4 * a + 4) >> 2], o[a + 2] = HEAPF32[n + (4 * a + 8) >> 2], o[a + 3] = HEAPF32[n + (4 * a + 12) >> 2]; else o = HEAPF32.subarray(n >> 2, n + 16 * t >> 2);
    GLctx.uniformMatrix2fv(GL.uniforms[e], !!r, o)
}

function _emscripten_glUniformMatrix3fv(e, t, r, n) {
    if (t <= 32) for (var o = miniTempWebGLFloatBuffers[9 * t - 1], a = 0; a < 9 * t; a += 9) o[a] = HEAPF32[n + 4 * a >> 2], o[a + 1] = HEAPF32[n + (4 * a + 4) >> 2], o[a + 2] = HEAPF32[n + (4 * a + 8) >> 2], o[a + 3] = HEAPF32[n + (4 * a + 12) >> 2], o[a + 4] = HEAPF32[n + (4 * a + 16) >> 2], o[a + 5] = HEAPF32[n + (4 * a + 20) >> 2], o[a + 6] = HEAPF32[n + (4 * a + 24) >> 2], o[a + 7] = HEAPF32[n + (4 * a + 28) >> 2], o[a + 8] = HEAPF32[n + (4 * a + 32) >> 2]; else o = HEAPF32.subarray(n >> 2, n + 36 * t >> 2);
    GLctx.uniformMatrix3fv(GL.uniforms[e], !!r, o)
}

function _emscripten_glUniformMatrix4fv(e, t, r, n) {
    if (t <= 18) {
        var o = miniTempWebGLFloatBuffers[16 * t - 1], a = HEAPF32;
        n >>= 2;
        for (var i = 0; i < 16 * t; i += 16) {
            var s = n + i;
            o[i] = a[s], o[i + 1] = a[s + 1], o[i + 2] = a[s + 2], o[i + 3] = a[s + 3], o[i + 4] = a[s + 4], o[i + 5] = a[s + 5], o[i + 6] = a[s + 6], o[i + 7] = a[s + 7], o[i + 8] = a[s + 8], o[i + 9] = a[s + 9], o[i + 10] = a[s + 10], o[i + 11] = a[s + 11], o[i + 12] = a[s + 12], o[i + 13] = a[s + 13], o[i + 14] = a[s + 14], o[i + 15] = a[s + 15]
        }
    } else o = HEAPF32.subarray(n >> 2, n + 64 * t >> 2);
    GLctx.uniformMatrix4fv(GL.uniforms[e], !!r, o)
}

function _emscripten_glUseProgram(e) {
    GLctx.useProgram(GL.programs[e])
}

function _emscripten_glValidateProgram(e) {
    GLctx.validateProgram(GL.programs[e])
}

function _emscripten_glVertexAttrib1f(e, t) {
    GLctx.vertexAttrib1f(e, t)
}

function _emscripten_glVertexAttrib1fv(e, t) {
    GLctx.vertexAttrib1f(e, HEAPF32[t >> 2])
}

function _emscripten_glVertexAttrib2f(e, t, r) {
    GLctx.vertexAttrib2f(e, t, r)
}

function _emscripten_glVertexAttrib2fv(e, t) {
    GLctx.vertexAttrib2f(e, HEAPF32[t >> 2], HEAPF32[t + 4 >> 2])
}

function _emscripten_glVertexAttrib3f(e, t, r, n) {
    GLctx.vertexAttrib3f(e, t, r, n)
}

function _emscripten_glVertexAttrib3fv(e, t) {
    GLctx.vertexAttrib3f(e, HEAPF32[t >> 2], HEAPF32[t + 4 >> 2], HEAPF32[t + 8 >> 2])
}

function _emscripten_glVertexAttrib4f(e, t, r, n, o) {
    GLctx.vertexAttrib4f(e, t, r, n, o)
}

function _emscripten_glVertexAttrib4fv(e, t) {
    GLctx.vertexAttrib4f(e, HEAPF32[t >> 2], HEAPF32[t + 4 >> 2], HEAPF32[t + 8 >> 2], HEAPF32[t + 12 >> 2])
}

function _emscripten_glVertexAttribDivisorANGLE(e, t) {
    GLctx.vertexAttribDivisor(e, t)
}

function _emscripten_glVertexAttribPointer(e, t, r, n, o, a) {
    GLctx.vertexAttribPointer(e, t, r, !!n, o, a)
}

function _emscripten_glViewport(e, t, r, n) {
    GLctx.viewport(e, t, r, n)
}

function _emscripten_has_asyncify() {
    return 0
}

function _emscripten_memcpy_big(e, t, r) {
    HEAPU8.copyWithin(e, t, t + r)
}

function doRequestFullscreen(e, t) {
    return JSEvents.fullscreenEnabled() ? (e = findEventTarget(e)) ? e.requestFullscreen || e.webkitRequestFullscreen ? _JSEvents_requestFullscreen(e, t) : -3 : -4 : -1
}

function _emscripten_request_fullscreen_strategy(e, t, r) {
    return doRequestFullscreen(e, {
        scaleMode: HEAP32[r >> 2],
        canvasResolutionScaleMode: HEAP32[r + 4 >> 2],
        filteringMode: HEAP32[r + 8 >> 2],
        deferUntilInEventHandler: t,
        canvasResizedCallback: HEAP32[r + 12 >> 2],
        canvasResizedCallbackUserData: HEAP32[r + 16 >> 2]
    })
}

function _emscripten_request_pointerlock(e, t) {
    return (e = findEventTarget(e)) ? e.requestPointerLock || e.msRequestPointerLock ? JSEvents.canPerformEventHandlerRequests() ? requestPointerLock(e) : t ? (JSEvents.deferCall(requestPointerLock, 2, [e]), 1) : -2 : -1 : -4
}

function abortOnCannotGrowMemory(e) {
    abort("OOM")
}

function _emscripten_resize_heap(e) {
    abortOnCannotGrowMemory(e)
}

function _emscripten_sample_gamepad_data() {
    return (JSEvents.lastGamepadState = navigator.getGamepads ? navigator.getGamepads() : navigator.webkitGetGamepads ? navigator.webkitGetGamepads() : null) ? 0 : -1
}

function registerBeforeUnloadEventCallback(e, t, r, n, o, a) {
    var i = {
        target: findEventTarget(e), eventTypeString: a, callbackfunc: n, handlerFunc: function (e) {
            var r = e || event, a = wasmTable.get(n)(o, 0, t);
            if (a && (a = UTF8ToString(a)), a) return r.preventDefault(), r.returnValue = a, a
        }, useCapture: r
    };
    JSEvents.registerOrRemoveHandler(i)
}

function _emscripten_set_beforeunload_callback_on_thread(e, t, r) {
    return "undefined" == typeof onbeforeunload ? -1 : 1 !== r ? -5 : (registerBeforeUnloadEventCallback(2, e, !0, t, 28, "beforeunload"), 0)
}

function registerFocusEventCallback(e, t, r, n, o, a, i) {
    JSEvents.focusEvent || (JSEvents.focusEvent = _malloc(256));
    var s = {
        target: findEventTarget(e), eventTypeString: a, callbackfunc: n, handlerFunc: function (e) {
            var r = e || event, a = JSEvents.getNodeNameForTarget(r.target), i = r.target.id ? r.target.id : "",
                s = JSEvents.focusEvent;
            stringToUTF8(a, s + 0, 128), stringToUTF8(i, s + 128, 128), wasmTable.get(n)(o, s, t) && r.preventDefault()
        }, useCapture: r
    };
    JSEvents.registerOrRemoveHandler(s)
}

function _emscripten_set_blur_callback_on_thread(e, t, r, n, o) {
    return registerFocusEventCallback(e, t, r, n, 12, "blur", o), 0
}

function _emscripten_set_element_css_size(e, t, r) {
    return (e = findEventTarget(e)) ? (e.style.width = t + "px", e.style.height = r + "px", 0) : -4
}

function _emscripten_set_focus_callback_on_thread(e, t, r, n, o) {
    return registerFocusEventCallback(e, t, r, n, 13, "focus", o), 0
}

function fillFullscreenChangeEventData(e) {
    var t = document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement,
        r = !!t;
    HEAP32[e >> 2] = r, HEAP32[e + 4 >> 2] = JSEvents.fullscreenEnabled();
    var n = r ? t : JSEvents.previousFullscreenElement, o = JSEvents.getNodeNameForTarget(n), a = n && n.id ? n.id : "";
    stringToUTF8(o, e + 8, 128), stringToUTF8(a, e + 136, 128), HEAP32[e + 264 >> 2] = n ? n.clientWidth : 0, HEAP32[e + 268 >> 2] = n ? n.clientHeight : 0, HEAP32[e + 272 >> 2] = screen.width, HEAP32[e + 276 >> 2] = screen.height, r && (JSEvents.previousFullscreenElement = t)
}

function registerFullscreenChangeEventCallback(e, t, r, n, o, a, i) {
    JSEvents.fullscreenChangeEvent || (JSEvents.fullscreenChangeEvent = _malloc(280));
    var s = {
        target: e, eventTypeString: a, callbackfunc: n, handlerFunc: function (e) {
            var r = e || event, a = JSEvents.fullscreenChangeEvent;
            fillFullscreenChangeEventData(a), wasmTable.get(n)(o, a, t) && r.preventDefault()
        }, useCapture: r
    };
    JSEvents.registerOrRemoveHandler(s)
}

function _emscripten_set_fullscreenchange_callback_on_thread(e, t, r, n, o) {
    return JSEvents.fullscreenEnabled() ? (e = findEventTarget(e)) ? (registerFullscreenChangeEventCallback(e, t, r, n, 19, "fullscreenchange", o), registerFullscreenChangeEventCallback(e, t, r, n, 19, "webkitfullscreenchange", o), 0) : -4 : -1
}

function registerGamepadEventCallback(e, t, r, n, o, a, i) {
    JSEvents.gamepadEvent || (JSEvents.gamepadEvent = _malloc(1432));
    var s = {
        target: findEventTarget(e),
        allowsDeferredCalls: !0,
        eventTypeString: a,
        callbackfunc: n,
        handlerFunc: function (e) {
            var r = e || event, a = JSEvents.gamepadEvent;
            fillGamepadEventData(a, r.gamepad), wasmTable.get(n)(o, a, t) && r.preventDefault()
        },
        useCapture: r
    };
    JSEvents.registerOrRemoveHandler(s)
}

function _emscripten_set_gamepadconnected_callback_on_thread(e, t, r, n) {
    return navigator.getGamepads || navigator.webkitGetGamepads ? (registerGamepadEventCallback(2, e, t, r, 26, "gamepadconnected", n), 0) : -1
}

function _emscripten_set_gamepaddisconnected_callback_on_thread(e, t, r, n) {
    return navigator.getGamepads || navigator.webkitGetGamepads ? (registerGamepadEventCallback(2, e, t, r, 27, "gamepaddisconnected", n), 0) : -1
}

function registerKeyEventCallback(e, t, r, n, o, a, i) {
    JSEvents.keyEvent || (JSEvents.keyEvent = _malloc(164));
    var s = {
        target: findEventTarget(e),
        allowsDeferredCalls: !0,
        eventTypeString: a,
        callbackfunc: n,
        handlerFunc: function (e) {
            var r = JSEvents.keyEvent, a = r >> 2;
            HEAP32[a + 0] = e.location, HEAP32[a + 1] = e.ctrlKey, HEAP32[a + 2] = e.shiftKey, HEAP32[a + 3] = e.altKey, HEAP32[a + 4] = e.metaKey, HEAP32[a + 5] = e.repeat, HEAP32[a + 6] = e.charCode, HEAP32[a + 7] = e.keyCode, HEAP32[a + 8] = e.which, stringToUTF8(e.key || "", r + 36, 32), stringToUTF8(e.code || "", r + 68, 32), stringToUTF8(e.char || "", r + 100, 32), stringToUTF8(e.locale || "", r + 132, 32), wasmTable.get(n)(o, r, t) && e.preventDefault()
        },
        useCapture: r
    };
    JSEvents.registerOrRemoveHandler(s)
}

function _emscripten_set_keydown_callback_on_thread(e, t, r, n, o) {
    return registerKeyEventCallback(e, t, r, n, 2, "keydown", o), 0
}

function _emscripten_set_keypress_callback_on_thread(e, t, r, n, o) {
    return registerKeyEventCallback(e, t, r, n, 1, "keypress", o), 0
}

function _emscripten_set_keyup_callback_on_thread(e, t, r, n, o) {
    return registerKeyEventCallback(e, t, r, n, 3, "keyup", o), 0
}

function _emscripten_set_main_loop(e, t, r) {
    setMainLoop(wasmTable.get(e), t, r)
}

function fillMouseEventData(e, t, r) {
    var n = e >> 2;
    HEAP32[n + 0] = t.screenX, HEAP32[n + 1] = t.screenY, HEAP32[n + 2] = t.clientX, HEAP32[n + 3] = t.clientY, HEAP32[n + 4] = t.ctrlKey, HEAP32[n + 5] = t.shiftKey, HEAP32[n + 6] = t.altKey, HEAP32[n + 7] = t.metaKey, HEAP16[2 * n + 16] = t.button, HEAP16[2 * n + 17] = t.buttons, HEAP32[n + 9] = t.movementX, HEAP32[n + 10] = t.movementY;
    var o = getBoundingClientRect(r);
    HEAP32[n + 11] = t.clientX - o.left, HEAP32[n + 12] = t.clientY - o.top
}

function registerMouseEventCallback(e, t, r, n, o, a, i) {
    JSEvents.mouseEvent || (JSEvents.mouseEvent = _malloc(64));
    var s = {
        target: e = findEventTarget(e),
        allowsDeferredCalls: "mousemove" != a && "mouseenter" != a && "mouseleave" != a,
        eventTypeString: a,
        callbackfunc: n,
        handlerFunc: function (r) {
            var a = r || event;
            fillMouseEventData(JSEvents.mouseEvent, a, e), wasmTable.get(n)(o, JSEvents.mouseEvent, t) && a.preventDefault()
        },
        useCapture: r
    };
    JSEvents.registerOrRemoveHandler(s)
}

function _emscripten_set_mousedown_callback_on_thread(e, t, r, n, o) {
    return registerMouseEventCallback(e, t, r, n, 5, "mousedown", o), 0
}

function _emscripten_set_mouseenter_callback_on_thread(e, t, r, n, o) {
    return registerMouseEventCallback(e, t, r, n, 33, "mouseenter", o), 0
}

function _emscripten_set_mouseleave_callback_on_thread(e, t, r, n, o) {
    return registerMouseEventCallback(e, t, r, n, 34, "mouseleave", o), 0
}

function _emscripten_set_mousemove_callback_on_thread(e, t, r, n, o) {
    return registerMouseEventCallback(e, t, r, n, 8, "mousemove", o), 0
}

function _emscripten_set_mouseup_callback_on_thread(e, t, r, n, o) {
    return registerMouseEventCallback(e, t, r, n, 6, "mouseup", o), 0
}

function fillPointerlockChangeEventData(e) {
    var t = document.pointerLockElement || document.mozPointerLockElement || document.webkitPointerLockElement || document.msPointerLockElement,
        r = !!t;
    HEAP32[e >> 2] = r;
    var n = JSEvents.getNodeNameForTarget(t), o = t && t.id ? t.id : "";
    stringToUTF8(n, e + 4, 128), stringToUTF8(o, e + 132, 128)
}

function registerPointerlockChangeEventCallback(e, t, r, n, o, a, i) {
    JSEvents.pointerlockChangeEvent || (JSEvents.pointerlockChangeEvent = _malloc(260));
    var s = {
        target: e, eventTypeString: a, callbackfunc: n, handlerFunc: function (e) {
            var r = e || event, a = JSEvents.pointerlockChangeEvent;
            fillPointerlockChangeEventData(a), wasmTable.get(n)(o, a, t) && r.preventDefault()
        }, useCapture: r
    };
    JSEvents.registerOrRemoveHandler(s)
}

function _emscripten_set_pointerlockchange_callback_on_thread(e, t, r, n, o) {
    return document && document.body && (document.body.requestPointerLock || document.body.mozRequestPointerLock || document.body.webkitRequestPointerLock || document.body.msRequestPointerLock) ? (e = findEventTarget(e)) ? (registerPointerlockChangeEventCallback(e, t, r, n, 20, "pointerlockchange", o), registerPointerlockChangeEventCallback(e, t, r, n, 20, "mozpointerlockchange", o), registerPointerlockChangeEventCallback(e, t, r, n, 20, "webkitpointerlockchange", o), registerPointerlockChangeEventCallback(e, t, r, n, 20, "mspointerlockchange", o), 0) : -4 : -1
}

function registerUiEventCallback(e, t, r, n, o, a, i) {
    JSEvents.uiEvent || (JSEvents.uiEvent = _malloc(36));
    var s = {
        target: e = findEventTarget(e), eventTypeString: a, callbackfunc: n, handlerFunc: function (r) {
            var a = r || event;
            if (a.target == e) {
                var i = document.body;
                if (i) {
                    var s = JSEvents.uiEvent;
                    HEAP32[s >> 2] = a.detail, HEAP32[s + 4 >> 2] = i.clientWidth, HEAP32[s + 8 >> 2] = i.clientHeight, HEAP32[s + 12 >> 2] = innerWidth, HEAP32[s + 16 >> 2] = innerHeight, HEAP32[s + 20 >> 2] = outerWidth, HEAP32[s + 24 >> 2] = outerHeight, HEAP32[s + 28 >> 2] = pageXOffset, HEAP32[s + 32 >> 2] = pageYOffset, wasmTable.get(n)(o, s, t) && a.preventDefault()
                }
            }
        }, useCapture: r
    };
    JSEvents.registerOrRemoveHandler(s)
}

function _emscripten_set_resize_callback_on_thread(e, t, r, n, o) {
    return registerUiEventCallback(e, t, r, n, 10, "resize", o), 0
}

function registerTouchEventCallback(e, t, r, n, o, a, i) {
    JSEvents.touchEvent || (JSEvents.touchEvent = _malloc(1684));
    var s = {
        target: e = findEventTarget(e),
        allowsDeferredCalls: "touchstart" == a || "touchend" == a,
        eventTypeString: a,
        callbackfunc: n,
        handlerFunc: function (r) {
            for (var a = {}, i = r.touches, s = 0; s < i.length; ++s) a[(c = i[s]).identifier] = c;
            i = r.changedTouches;
            for (s = 0; s < i.length; ++s) {
                var c;
                (c = i[s]).isChanged = 1, a[c.identifier] = c
            }
            i = r.targetTouches;
            for (s = 0; s < i.length; ++s) a[i[s].identifier].onTarget = 1;
            var l = JSEvents.touchEvent, u = l >> 2;
            HEAP32[u + 1] = r.ctrlKey, HEAP32[u + 2] = r.shiftKey, HEAP32[u + 3] = r.altKey, HEAP32[u + 4] = r.metaKey, u += 5;
            var d = getBoundingClientRect(e), f = 0;
            for (var s in a) {
                var m = a[s];
                if (HEAP32[u + 0] = m.identifier, HEAP32[u + 1] = m.screenX, HEAP32[u + 2] = m.screenY, HEAP32[u + 3] = m.clientX, HEAP32[u + 4] = m.clientY, HEAP32[u + 5] = m.pageX, HEAP32[u + 6] = m.pageY, HEAP32[u + 7] = m.isChanged, HEAP32[u + 8] = m.onTarget, HEAP32[u + 9] = m.clientX - d.left, HEAP32[u + 10] = m.clientY - d.top, u += 13, ++f > 31) break
            }
            HEAP32[l >> 2] = f, wasmTable.get(n)(o, l, t) && r.preventDefault()
        },
        useCapture: r
    };
    JSEvents.registerOrRemoveHandler(s)
}

function _emscripten_set_touchcancel_callback_on_thread(e, t, r, n, o) {
    return registerTouchEventCallback(e, t, r, n, 25, "touchcancel", o), 0
}

function _emscripten_set_touchend_callback_on_thread(e, t, r, n, o) {
    return registerTouchEventCallback(e, t, r, n, 23, "touchend", o), 0
}

function _emscripten_set_touchmove_callback_on_thread(e, t, r, n, o) {
    return registerTouchEventCallback(e, t, r, n, 24, "touchmove", o), 0
}

function _emscripten_set_touchstart_callback_on_thread(e, t, r, n, o) {
    return registerTouchEventCallback(e, t, r, n, 22, "touchstart", o), 0
}

function fillVisibilityChangeEventData(e) {
    var t = ["hidden", "visible", "prerender", "unloaded"].indexOf(document.visibilityState);
    HEAP32[e >> 2] = document.hidden, HEAP32[e + 4 >> 2] = t
}

function registerVisibilityChangeEventCallback(e, t, r, n, o, a, i) {
    JSEvents.visibilityChangeEvent || (JSEvents.visibilityChangeEvent = _malloc(8));
    var s = {
        target: e, eventTypeString: a, callbackfunc: n, handlerFunc: function (e) {
            var r = e || event, a = JSEvents.visibilityChangeEvent;
            fillVisibilityChangeEventData(a), wasmTable.get(n)(o, a, t) && r.preventDefault()
        }, useCapture: r
    };
    JSEvents.registerOrRemoveHandler(s)
}

function _emscripten_set_visibilitychange_callback_on_thread(e, t, r, n) {
    return specialHTMLTargets[1] ? (registerVisibilityChangeEventCallback(specialHTMLTargets[1], e, t, r, 21, "visibilitychange", n), 0) : -4
}

function registerWheelEventCallback(e, t, r, n, o, a, i) {
    JSEvents.wheelEvent || (JSEvents.wheelEvent = _malloc(96));
    var s = {
        target: e, allowsDeferredCalls: !0, eventTypeString: a, callbackfunc: n, handlerFunc: function (r) {
            var a = r || event, i = JSEvents.wheelEvent;
            fillMouseEventData(i, a, e), HEAPF64[i + 64 >> 3] = a.deltaX, HEAPF64[i + 72 >> 3] = a.deltaY, HEAPF64[i + 80 >> 3] = a.deltaZ, HEAP32[i + 88 >> 2] = a.deltaMode, wasmTable.get(n)(o, i, t) && a.preventDefault()
        }, useCapture: r
    };
    JSEvents.registerOrRemoveHandler(s)
}

function _emscripten_set_wheel_callback_on_thread(e, t, r, n, o) {
    return void 0 !== (e = findEventTarget(e)).onwheel ? (registerWheelEventCallback(e, t, r, n, 9, "wheel", o), 0) : -1
}

function _emscripten_sleep() {
    throw"Please compile your program with async support in order to use asynchronous operations like emscripten_sleep"
}

function _emscripten_thread_sleep(e) {
    for (var t = _emscripten_get_now(); _emscripten_get_now() - t < e;) ;
}

var ENV = {};

function getExecutableName() {
    return thisProgram || "./this.program"
}

function getEnvStrings() {
    if (!getEnvStrings.strings) {
        var e = {
            USER: "web_user",
            LOGNAME: "web_user",
            PATH: "/",
            PWD: "/",
            HOME: "/home/web_user",
            LANG: ("object" == typeof navigator && navigator.languages && navigator.languages[0] || "C").replace("-", "_") + ".UTF-8",
            _: getExecutableName()
        };
        for (var t in ENV) e[t] = ENV[t];
        var r = [];
        for (var t in e) r.push(t + "=" + e[t]);
        getEnvStrings.strings = r
    }
    return getEnvStrings.strings
}

function _environ_get(e, t) {
    try {
        var r = 0;
        return getEnvStrings().forEach(function (n, o) {
            var a = t + r;
            HEAP32[e + 4 * o >> 2] = a, writeAsciiToMemory(n, a), r += n.length + 1
        }), 0
    } catch (e) {
        return void 0 !== FS && e instanceof FS.ErrnoError || abort(e), e.errno
    }
}

function _environ_sizes_get(e, t) {
    try {
        var r = getEnvStrings();
        HEAP32[e >> 2] = r.length;
        var n = 0;
        return r.forEach(function (e) {
            n += e.length + 1
        }), HEAP32[t >> 2] = n, 0
    } catch (e) {
        return void 0 !== FS && e instanceof FS.ErrnoError || abort(e), e.errno
    }
}

function _exit(e) {
    exit(e)
}

function _fd_close(e) {
    try {
        var t = SYSCALLS.getStreamFromFD(e);
        return FS.close(t), 0
    } catch (e) {
        return void 0 !== FS && e instanceof FS.ErrnoError || abort(e), e.errno
    }
}

function _fd_read(e, t, r, n) {
    try {
        var o = SYSCALLS.getStreamFromFD(e), a = SYSCALLS.doReadv(o, t, r);
        return HEAP32[n >> 2] = a, 0
    } catch (e) {
        return void 0 !== FS && e instanceof FS.ErrnoError || abort(e), e.errno
    }
}

function _fd_seek(e, t, r, n, o) {
    try {
        var a = SYSCALLS.getStreamFromFD(e), i = 4294967296 * r + (t >>> 0);
        return i <= -9007199254740992 || i >= 9007199254740992 ? -61 : (FS.llseek(a, i, n), tempI64 = [a.position >>> 0, (tempDouble = a.position, +Math.abs(tempDouble) >= 1 ? tempDouble > 0 ? (0 | Math.min(+Math.floor(tempDouble / 4294967296), 4294967295)) >>> 0 : ~~+Math.ceil((tempDouble - +(~~tempDouble >>> 0)) / 4294967296) >>> 0 : 0)], HEAP32[o >> 2] = tempI64[0], HEAP32[o + 4 >> 2] = tempI64[1], a.getdents && 0 === i && 0 === n && (a.getdents = null), 0)
    } catch (e) {
        return void 0 !== FS && e instanceof FS.ErrnoError || abort(e), e.errno
    }
}

function _fd_write(e, t, r, n) {
    try {
        var o = SYSCALLS.getStreamFromFD(e), a = SYSCALLS.doWritev(o, t, r);
        return HEAP32[n >> 2] = a, 0
    } catch (e) {
        return void 0 !== FS && e instanceof FS.ErrnoError || abort(e), e.errno
    }
}

function _gettimeofday(e) {
    var t = Date.now();
    return HEAP32[e >> 2] = t / 1e3 | 0, HEAP32[e + 4 >> 2] = t % 1e3 * 1e3 | 0, 0
}

function _glActiveTexture(e) {
    GLctx.activeTexture(e)
}

function _glAttachShader(e, t) {
    GLctx.attachShader(GL.programs[e], GL.shaders[t])
}

function _glBindBuffer(e, t) {
    GLctx.bindBuffer(e, GL.buffers[t])
}

function _glBindTexture(e, t) {
    GLctx.bindTexture(e, GL.textures[t])
}

function _glBlendFunc(e, t) {
    GLctx.blendFunc(e, t)
}

function _glBufferData(e, t, r, n) {
    GLctx.bufferData(e, r ? HEAPU8.subarray(r, r + t) : t, n)
}

function _glClear(e) {
    GLctx.clear(e)
}

function _glClearColor(e, t, r, n) {
    GLctx.clearColor(e, t, r, n)
}

function _glCompileShader(e) {
    GLctx.compileShader(GL.shaders[e])
}

function _glCreateProgram() {
    var e = GL.getNewId(GL.programs), t = GLctx.createProgram();
    return t.name = e, GL.programs[e] = t, e
}

function _glCreateShader(e) {
    var t = GL.getNewId(GL.shaders);
    return GL.shaders[t] = GLctx.createShader(e), t
}

function _glDeleteBuffers(e, t) {
    for (var r = 0; r < e; r++) {
        var n = HEAP32[t + 4 * r >> 2], o = GL.buffers[n];
        o && (GLctx.deleteBuffer(o), o.name = 0, GL.buffers[n] = null)
    }
}

function _glDeleteProgram(e) {
    if (e) {
        var t = GL.programs[e];
        t ? (GLctx.deleteProgram(t), t.name = 0, GL.programs[e] = null, GL.programInfos[e] = null) : GL.recordError(1281)
    }
}

function _glDeleteShader(e) {
    if (e) {
        var t = GL.shaders[e];
        t ? (GLctx.deleteShader(t), GL.shaders[e] = null) : GL.recordError(1281)
    }
}

function _glDeleteTextures(e, t) {
    for (var r = 0; r < e; r++) {
        var n = HEAP32[t + 4 * r >> 2], o = GL.textures[n];
        o && (GLctx.deleteTexture(o), o.name = 0, GL.textures[n] = null)
    }
}

function _glDisable(e) {
    GLctx.disable(e)
}

function _glDrawElements(e, t, r, n) {
    GLctx.drawElements(e, t, r, n)
}

function _glEnable(e) {
    GLctx.enable(e)
}

function _glEnableVertexAttribArray(e) {
    GLctx.enableVertexAttribArray(e)
}

function _glGenBuffers(e, t) {
    __glGenObject(e, t, "createBuffer", GL.buffers)
}

function _glGenTextures(e, t) {
    __glGenObject(e, t, "createTexture", GL.textures)
}

function _glGetAttribLocation(e, t) {
    return GLctx.getAttribLocation(GL.programs[e], UTF8ToString(t))
}

function _glGetProgramInfoLog(e, t, r, n) {
    var o = GLctx.getProgramInfoLog(GL.programs[e]);
    null === o && (o = "(unknown error)");
    var a = t > 0 && n ? stringToUTF8(o, n, t) : 0;
    r && (HEAP32[r >> 2] = a)
}

function _glGetProgramiv(e, t, r) {
    if (r) if (e >= GL.counter) GL.recordError(1281); else {
        var n = GL.programInfos[e];
        if (n) if (35716 == t) {
            var o = GLctx.getProgramInfoLog(GL.programs[e]);
            null === o && (o = "(unknown error)"), HEAP32[r >> 2] = o.length + 1
        } else if (35719 == t) HEAP32[r >> 2] = n.maxUniformLength; else if (35722 == t) {
            if (-1 == n.maxAttributeLength) {
                e = GL.programs[e];
                var a = GLctx.getProgramParameter(e, 35721);
                n.maxAttributeLength = 0;
                for (var i = 0; i < a; ++i) {
                    var s = GLctx.getActiveAttrib(e, i);
                    n.maxAttributeLength = Math.max(n.maxAttributeLength, s.name.length + 1)
                }
            }
            HEAP32[r >> 2] = n.maxAttributeLength
        } else if (35381 == t) {
            if (-1 == n.maxUniformBlockNameLength) {
                e = GL.programs[e];
                var c = GLctx.getProgramParameter(e, 35382);
                n.maxUniformBlockNameLength = 0;
                for (i = 0; i < c; ++i) {
                    var l = GLctx.getActiveUniformBlockName(e, i);
                    n.maxUniformBlockNameLength = Math.max(n.maxUniformBlockNameLength, l.length + 1)
                }
            }
            HEAP32[r >> 2] = n.maxUniformBlockNameLength
        } else HEAP32[r >> 2] = GLctx.getProgramParameter(GL.programs[e], t); else GL.recordError(1282)
    } else GL.recordError(1281)
}

function _glGetShaderInfoLog(e, t, r, n) {
    var o = GLctx.getShaderInfoLog(GL.shaders[e]);
    null === o && (o = "(unknown error)");
    var a = t > 0 && n ? stringToUTF8(o, n, t) : 0;
    r && (HEAP32[r >> 2] = a)
}

function _glGetShaderiv(e, t, r) {
    if (r) if (35716 == t) {
        var n = GLctx.getShaderInfoLog(GL.shaders[e]);
        null === n && (n = "(unknown error)");
        var o = n ? n.length + 1 : 0;
        HEAP32[r >> 2] = o
    } else if (35720 == t) {
        var a = GLctx.getShaderSource(GL.shaders[e]), i = a ? a.length + 1 : 0;
        HEAP32[r >> 2] = i
    } else HEAP32[r >> 2] = GLctx.getShaderParameter(GL.shaders[e], t); else GL.recordError(1281)
}

function _glGetUniformLocation(e, t) {
    var r = 0;
    if ("]" == (t = UTF8ToString(t))[t.length - 1]) {
        var n = t.lastIndexOf("[");
        r = "]" != t[n + 1] ? jstoi_q(t.slice(n + 1)) : 0, t = t.slice(0, n)
    }
    var o = GL.programInfos[e] && GL.programInfos[e].uniforms[t];
    return o && r >= 0 && r < o[0] ? o[1] + r : -1
}

function _glLinkProgram(e) {
    GLctx.linkProgram(GL.programs[e]), GL.populateUniformTable(e)
}

function _glShaderSource(e, t, r, n) {
    var o = GL.getSource(e, t, r, n);
    GLctx.shaderSource(GL.shaders[e], o)
}

function _glTexImage2D(e, t, r, n, o, a, i, s, c) {
    GLctx.texImage2D(e, t, r, n, o, a, i, s, c ? emscriptenWebGLGetTexPixelData(s, i, n, o, c, r) : null)
}

function _glTexParameteri(e, t, r) {
    GLctx.texParameteri(e, t, r)
}

function _glTexSubImage2D(e, t, r, n, o, a, i, s, c) {
    var l = null;
    c && (l = emscriptenWebGLGetTexPixelData(s, i, o, a, c, 0)), GLctx.texSubImage2D(e, t, r, n, o, a, i, s, l)
}

function _glUniform1i(e, t) {
    GLctx.uniform1i(GL.uniforms[e], t)
}

function _glUniform4f(e, t, r, n, o) {
    GLctx.uniform4f(GL.uniforms[e], t, r, n, o)
}

function _glUniformMatrix4fv(e, t, r, n) {
    if (t <= 18) {
        var o = miniTempWebGLFloatBuffers[16 * t - 1], a = HEAPF32;
        n >>= 2;
        for (var i = 0; i < 16 * t; i += 16) {
            var s = n + i;
            o[i] = a[s], o[i + 1] = a[s + 1], o[i + 2] = a[s + 2], o[i + 3] = a[s + 3], o[i + 4] = a[s + 4], o[i + 5] = a[s + 5], o[i + 6] = a[s + 6], o[i + 7] = a[s + 7], o[i + 8] = a[s + 8], o[i + 9] = a[s + 9], o[i + 10] = a[s + 10], o[i + 11] = a[s + 11], o[i + 12] = a[s + 12], o[i + 13] = a[s + 13], o[i + 14] = a[s + 14], o[i + 15] = a[s + 15]
        }
    } else o = HEAPF32.subarray(n >> 2, n + 64 * t >> 2);
    GLctx.uniformMatrix4fv(GL.uniforms[e], !!r, o)
}

function _glUseProgram(e) {
    GLctx.useProgram(GL.programs[e])
}

function _glVertexAttribPointer(e, t, r, n, o, a) {
    GLctx.vertexAttribPointer(e, t, r, !!n, o, a)
}

function _glViewport(e, t, r, n) {
    GLctx.viewport(e, t, r, n)
}

function _setTempRet0(e) {
    setTempRet0(0 | e)
}

function _sigaction(e, t, r) {
    return 0
}

var __sigalrm_handler = 0;

function _signal(e, t) {
    return 14 == e && (__sigalrm_handler = t), 0
}

var readAsmConstArgsArray = [];

function readAsmConstArgs(e, t) {
    var r;
    for (readAsmConstArgsArray.length = 0, t >>= 2; r = HEAPU8[e++];) {
        var n = r < 105;
        n && 1 & t && t++, readAsmConstArgsArray.push(n ? HEAPF64[t++ >> 1] : HEAP32[t]), ++t
    }
    return readAsmConstArgsArray
}

var GLctx, FSNode = function (e, t, r, n) {
    e || (e = this), this.parent = e, this.mount = e.mount, this.mounted = null, this.id = FS.nextInode++, this.name = t, this.mode = r, this.node_ops = {}, this.stream_ops = {}, this.rdev = n
}, readMode = 365, writeMode = 146;
Object.defineProperties(FSNode.prototype, {
    read: {
        get: function () {
            return (this.mode & readMode) === readMode
        }, set: function (e) {
            e ? this.mode |= readMode : this.mode &= ~readMode
        }
    }, write: {
        get: function () {
            return (this.mode & writeMode) === writeMode
        }, set: function (e) {
            e ? this.mode |= writeMode : this.mode &= ~writeMode
        }
    }, isFolder: {
        get: function () {
            return FS.isDir(this.mode)
        }
    }, isDevice: {
        get: function () {
            return FS.isChrdev(this.mode)
        }
    }
}), FS.FSNode = FSNode, FS.staticInit(), Module.FS_createPath = FS.createPath, Module.FS_createDataFile = FS.createDataFile, Module.FS_createPreloadedFile = FS.createPreloadedFile, Module.FS_createLazyFile = FS.createLazyFile, Module.FS_createDevice = FS.createDevice, Module.FS_unlink = FS.unlink, Module.requestFullscreen = function (e, t) {
    Browser.requestFullscreen(e, t)
}, Module.requestAnimationFrame = function (e) {
    Browser.requestAnimationFrame(e)
}, Module.setCanvasSize = function (e, t, r) {
    Browser.setCanvasSize(e, t, r)
}, Module.pauseMainLoop = function () {
    Browser.mainLoop.pause()
}, Module.resumeMainLoop = function () {
    Browser.mainLoop.resume()
}, Module.getUserMedia = function () {
    Browser.getUserMedia()
}, Module.createContext = function (e, t, r, n) {
    return Browser.createContext(e, t, r, n)
};
for (var i = 0; i < 32; ++i) tempFixedLengthArray.push(new Array(i));
var miniTempWebGLFloatBuffersStorage = new Float32Array(288);
for (i = 0; i < 288; ++i) miniTempWebGLFloatBuffers[i] = miniTempWebGLFloatBuffersStorage.subarray(0, i + 1);
var __miniTempWebGLIntBuffersStorage = new Int32Array(288);
for (i = 0; i < 288; ++i) __miniTempWebGLIntBuffers[i] = __miniTempWebGLIntBuffersStorage.subarray(0, i + 1);

function intArrayFromString(e, t, r) {
    var n = r > 0 ? r : lengthBytesUTF8(e) + 1, o = new Array(n), a = stringToUTF8Array(e, o, 0, o.length);
    return t && (o.length = a), o
}

var calledRun, asmLibraryArg = {
    da: ___sys_fcntl64,
    cb: ___sys_getdents64,
    $a: ___sys_ioctl,
    bb: ___sys_mkdir,
    ea: ___sys_open,
    ab: ___sys_stat64,
    ba: _abort,
    y: _clock_gettime,
    na: _dlclose,
    Da: _eglBindAPI,
    Ha: _eglChooseConfig,
    ua: _eglCreateContext,
    wa: _eglCreateWindowSurface,
    va: _eglDestroyContext,
    xa: _eglDestroySurface,
    Ia: _eglGetConfigAttrib,
    Y: _eglGetDisplay,
    ta: _eglGetError,
    Ea: _eglInitialize,
    ya: _eglMakeCurrent,
    sa: _eglQueryString,
    za: _eglSwapBuffers,
    Aa: _eglSwapInterval,
    Ga: _eglTerminate,
    Ca: _eglWaitGL,
    Ba: _eglWaitNative,
    a: _emscripten_asm_const_int,
    Ma: _emscripten_async_wget_data,
    La: _emscripten_cancel_main_loop,
    oa: _emscripten_exit_fullscreen,
    ra: _emscripten_exit_pointerlock,
    Ka: _emscripten_force_exit,
    k: _emscripten_get_device_pixel_ratio,
    d: _emscripten_get_element_css_size,
    B: _emscripten_get_gamepad_status,
    ma: _emscripten_get_num_gamepads,
    Pd: _emscripten_glActiveTexture,
    Od: _emscripten_glAttachShader,
    fe: _emscripten_glBeginQueryEXT,
    Nd: _emscripten_glBindAttribLocation,
    Md: _emscripten_glBindBuffer,
    Ld: _emscripten_glBindFramebuffer,
    Kd: _emscripten_glBindRenderbuffer,
    Jd: _emscripten_glBindTexture,
    Yd: _emscripten_glBindVertexArrayOES,
    Id: _emscripten_glBlendColor,
    Hd: _emscripten_glBlendEquation,
    Gd: _emscripten_glBlendEquationSeparate,
    Ed: _emscripten_glBlendFunc,
    Dd: _emscripten_glBlendFuncSeparate,
    Cd: _emscripten_glBufferData,
    Bd: _emscripten_glBufferSubData,
    Ad: _emscripten_glCheckFramebufferStatus,
    zd: _emscripten_glClear,
    yd: _emscripten_glClearColor,
    xd: _emscripten_glClearDepthf,
    wd: _emscripten_glClearStencil,
    vd: _emscripten_glColorMask,
    ud: _emscripten_glCompileShader,
    td: _emscripten_glCompressedTexImage2D,
    sd: _emscripten_glCompressedTexSubImage2D,
    rd: _emscripten_glCopyTexImage2D,
    qd: _emscripten_glCopyTexSubImage2D,
    pd: _emscripten_glCreateProgram,
    od: _emscripten_glCreateShader,
    nd: _emscripten_glCullFace,
    md: _emscripten_glDeleteBuffers,
    ld: _emscripten_glDeleteFramebuffers,
    jd: _emscripten_glDeleteProgram,
    he: _emscripten_glDeleteQueriesEXT,
    id: _emscripten_glDeleteRenderbuffers,
    hd: _emscripten_glDeleteShader,
    gd: _emscripten_glDeleteTextures,
    Xd: _emscripten_glDeleteVertexArraysOES,
    fd: _emscripten_glDepthFunc,
    ed: _emscripten_glDepthMask,
    dd: _emscripten_glDepthRangef,
    cd: _emscripten_glDetachShader,
    bd: _emscripten_glDisable,
    ad: _emscripten_glDisableVertexAttribArray,
    $c: _emscripten_glDrawArrays,
    Td: _emscripten_glDrawArraysInstancedANGLE,
    Ud: _emscripten_glDrawBuffersWEBGL,
    _c: _emscripten_glDrawElements,
    Sd: _emscripten_glDrawElementsInstancedANGLE,
    Zc: _emscripten_glEnable,
    Yc: _emscripten_glEnableVertexAttribArray,
    ee: _emscripten_glEndQueryEXT,
    Xc: _emscripten_glFinish,
    Wc: _emscripten_glFlush,
    Vc: _emscripten_glFramebufferRenderbuffer,
    Uc: _emscripten_glFramebufferTexture2D,
    Tc: _emscripten_glFrontFace,
    Sc: _emscripten_glGenBuffers,
    Pc: _emscripten_glGenFramebuffers,
    ie: _emscripten_glGenQueriesEXT,
    Oc: _emscripten_glGenRenderbuffers,
    Nc: _emscripten_glGenTextures,
    Wd: _emscripten_glGenVertexArraysOES,
    Qc: _emscripten_glGenerateMipmap,
    Mc: _emscripten_glGetActiveAttrib,
    Lc: _emscripten_glGetActiveUniform,
    Kc: _emscripten_glGetAttachedShaders,
    Jc: _emscripten_glGetAttribLocation,
    Ic: _emscripten_glGetBooleanv,
    Hc: _emscripten_glGetBufferParameteriv,
    Gc: _emscripten_glGetError,
    Fc: _emscripten_glGetFloatv,
    Ec: _emscripten_glGetFramebufferAttachmentParameteriv,
    Dc: _emscripten_glGetIntegerv,
    Bc: _emscripten_glGetProgramInfoLog,
    Cc: _emscripten_glGetProgramiv,
    _d: _emscripten_glGetQueryObjecti64vEXT,
    be: _emscripten_glGetQueryObjectivEXT,
    Zd: _emscripten_glGetQueryObjectui64vEXT,
    ae: _emscripten_glGetQueryObjectuivEXT,
    ce: _emscripten_glGetQueryivEXT,
    Ac: _emscripten_glGetRenderbufferParameteriv,
    yc: _emscripten_glGetShaderInfoLog,
    xc: _emscripten_glGetShaderPrecisionFormat,
    vc: _emscripten_glGetShaderSource,
    zc: _emscripten_glGetShaderiv,
    uc: _emscripten_glGetString,
    tc: _emscripten_glGetTexParameterfv,
    sc: _emscripten_glGetTexParameteriv,
    pc: _emscripten_glGetUniformLocation,
    rc: _emscripten_glGetUniformfv,
    qc: _emscripten_glGetUniformiv,
    mc: _emscripten_glGetVertexAttribPointerv,
    oc: _emscripten_glGetVertexAttribfv,
    nc: _emscripten_glGetVertexAttribiv,
    lc: _emscripten_glHint,
    kc: _emscripten_glIsBuffer,
    jc: _emscripten_glIsEnabled,
    ic: _emscripten_glIsFramebuffer,
    hc: _emscripten_glIsProgram,
    ge: _emscripten_glIsQueryEXT,
    gc: _emscripten_glIsRenderbuffer,
    fc: _emscripten_glIsShader,
    ec: _emscripten_glIsTexture,
    Vd: _emscripten_glIsVertexArrayOES,
    dc: _emscripten_glLineWidth,
    cc: _emscripten_glLinkProgram,
    ac: _emscripten_glPixelStorei,
    $b: _emscripten_glPolygonOffset,
    de: _emscripten_glQueryCounterEXT,
    _b: _emscripten_glReadPixels,
    Zb: _emscripten_glReleaseShaderCompiler,
    Yb: _emscripten_glRenderbufferStorage,
    Xb: _emscripten_glSampleCoverage,
    Wb: _emscripten_glScissor,
    Vb: _emscripten_glShaderBinary,
    Ub: _emscripten_glShaderSource,
    Tb: _emscripten_glStencilFunc,
    Sb: _emscripten_glStencilFuncSeparate,
    Rb: _emscripten_glStencilMask,
    Qb: _emscripten_glStencilMaskSeparate,
    Pb: _emscripten_glStencilOp,
    Ob: _emscripten_glStencilOpSeparate,
    Nb: _emscripten_glTexImage2D,
    Mb: _emscripten_glTexParameterf,
    Lb: _emscripten_glTexParameterfv,
    Kb: _emscripten_glTexParameteri,
    Jb: _emscripten_glTexParameteriv,
    Ib: _emscripten_glTexSubImage2D,
    Hb: _emscripten_glUniform1f,
    Gb: _emscripten_glUniform1fv,
    Fb: _emscripten_glUniform1i,
    Eb: _emscripten_glUniform1iv,
    Db: _emscripten_glUniform2f,
    Cb: _emscripten_glUniform2fv,
    Bb: _emscripten_glUniform2i,
    Ab: _emscripten_glUniform2iv,
    zb: _emscripten_glUniform3f,
    yb: _emscripten_glUniform3fv,
    xb: _emscripten_glUniform3i,
    wb: _emscripten_glUniform3iv,
    vb: _emscripten_glUniform4f,
    ub: _emscripten_glUniform4fv,
    tb: _emscripten_glUniform4i,
    sb: _emscripten_glUniform4iv,
    rb: _emscripten_glUniformMatrix2fv,
    qb: _emscripten_glUniformMatrix3fv,
    pb: _emscripten_glUniformMatrix4fv,
    ob: _emscripten_glUseProgram,
    nb: _emscripten_glValidateProgram,
    mb: _emscripten_glVertexAttrib1f,
    lb: _emscripten_glVertexAttrib1fv,
    kb: _emscripten_glVertexAttrib2f,
    jb: _emscripten_glVertexAttrib2fv,
    ib: _emscripten_glVertexAttrib3f,
    hb: _emscripten_glVertexAttrib3fv,
    gb: _emscripten_glVertexAttrib4f,
    fb: _emscripten_glVertexAttrib4fv,
    Rd: _emscripten_glVertexAttribDivisorANGLE,
    eb: _emscripten_glVertexAttribPointer,
    db: _emscripten_glViewport,
    r: _emscripten_has_asyncify,
    Va: _emscripten_memcpy_big,
    pa: _emscripten_request_fullscreen_strategy,
    X: _emscripten_request_pointerlock,
    Wa: _emscripten_resize_heap,
    C: _emscripten_sample_gamepad_data,
    D: _emscripten_set_beforeunload_callback_on_thread,
    P: _emscripten_set_blur_callback_on_thread,
    h: _emscripten_set_canvas_element_size,
    p: _emscripten_set_element_css_size,
    Q: _emscripten_set_focus_callback_on_thread,
    G: _emscripten_set_fullscreenchange_callback_on_thread,
    A: _emscripten_set_gamepadconnected_callback_on_thread,
    z: _emscripten_set_gamepaddisconnected_callback_on_thread,
    J: _emscripten_set_keydown_callback_on_thread,
    H: _emscripten_set_keypress_callback_on_thread,
    I: _emscripten_set_keyup_callback_on_thread,
    Na: _emscripten_set_main_loop,
    V: _emscripten_set_mousedown_callback_on_thread,
    T: _emscripten_set_mouseenter_callback_on_thread,
    S: _emscripten_set_mouseleave_callback_on_thread,
    W: _emscripten_set_mousemove_callback_on_thread,
    U: _emscripten_set_mouseup_callback_on_thread,
    K: _emscripten_set_pointerlockchange_callback_on_thread,
    F: _emscripten_set_resize_callback_on_thread,
    L: _emscripten_set_touchcancel_callback_on_thread,
    N: _emscripten_set_touchend_callback_on_thread,
    M: _emscripten_set_touchmove_callback_on_thread,
    O: _emscripten_set_touchstart_callback_on_thread,
    E: _emscripten_set_visibilitychange_callback_on_thread,
    R: _emscripten_set_wheel_callback_on_thread,
    q: _emscripten_sleep,
    Za: _emscripten_thread_sleep,
    Xa: _environ_get,
    Ya: _environ_sizes_get,
    la: _exit,
    m: _fd_close,
    _a: _fd_read,
    Ua: _fd_seek,
    ca: _fd_write,
    f: _gettimeofday,
    i: _glActiveTexture,
    ia: _glAttachShader,
    b: _glBindBuffer,
    e: _glBindTexture,
    Ra: _glBlendFunc,
    v: _glBufferData,
    qa: _glClear,
    Fa: _glClearColor,
    Fd: _glCompileShader,
    Rc: _glCreateProgram,
    $d: _glCreateShader,
    g: _glDeleteBuffers,
    x: _glDeleteProgram,
    o: _glDeleteShader,
    j: _glDeleteTextures,
    s: _glDisable,
    Oa: _glDrawElements,
    Sa: _glEnable,
    Z: _glEnableVertexAttribArray,
    w: _glGenBuffers,
    ha: _glGenTextures,
    fa: _glGetAttribLocation,
    bc: _glGetProgramInfoLog,
    ga: _glGetProgramiv,
    kd: _glGetShaderInfoLog,
    ja: _glGetShaderiv,
    n: _glGetUniformLocation,
    wc: _glLinkProgram,
    Qd: _glShaderSource,
    t: _glTexImage2D,
    l: _glTexParameteri,
    ka: _glTexSubImage2D,
    $: _glUniform1i,
    Qa: _glUniform4f,
    Pa: _glUniformMatrix4fv,
    aa: _glUseProgram,
    _: _glVertexAttribPointer,
    Ta: _glViewport,
    u: _setTempRet0,
    c: _sigaction,
    Ja: _signal
}, asm = createWasm(), ___wasm_call_ctors = Module.___wasm_call_ctors = function () {
    return (___wasm_call_ctors = Module.___wasm_call_ctors = Module.asm.ke).apply(null, arguments)
}, _free = Module._free = function () {
    return (_free = Module._free = Module.asm.me).apply(null, arguments)
}, _malloc = Module._malloc = function () {
    return (_malloc = Module._malloc = Module.asm.ne).apply(null, arguments)
}, _main = Module._main = function () {
    return (_main = Module._main = Module.asm.oe).apply(null, arguments)
}, _OpenFileData = Module._OpenFileData = function () {
    return (_OpenFileData = Module._OpenFileData = Module.asm.pe).apply(null, arguments)
}, _OpenFile = Module._OpenFile = function () {
    return (_OpenFile = Module._OpenFile = Module.asm.qe).apply(null, arguments)
}, _ExitLoop = Module._ExitLoop = function () {
    return (_ExitLoop = Module._ExitLoop = Module.asm.re).apply(null, arguments)
}, _OnCommand = Module._OnCommand = function () {
    return (_OnCommand = Module._OnCommand = Module.asm.se).apply(null, arguments)
}, ___errno_location = Module.___errno_location = function () {
    return (___errno_location = Module.___errno_location = Module.asm.te).apply(null, arguments)
}, stackSave = Module.stackSave = function () {
    return (stackSave = Module.stackSave = Module.asm.ue).apply(null, arguments)
}, stackRestore = Module.stackRestore = function () {
    return (stackRestore = Module.stackRestore = Module.asm.ve).apply(null, arguments)
}, stackAlloc = Module.stackAlloc = function () {
    return (stackAlloc = Module.stackAlloc = Module.asm.we).apply(null, arguments)
}, dynCall_iiiji = Module.dynCall_iiiji = function () {
    return (dynCall_iiiji = Module.dynCall_iiiji = Module.asm.xe).apply(null, arguments)
}, dynCall_jii = Module.dynCall_jii = function () {
    return (dynCall_jii = Module.dynCall_jii = Module.asm.ye).apply(null, arguments)
}, dynCall_jiji = Module.dynCall_jiji = function () {
    return (dynCall_jiji = Module.dynCall_jiji = Module.asm.ze).apply(null, arguments)
}, dynCall_ji = Module.dynCall_ji = function () {
    return (dynCall_ji = Module.dynCall_ji = Module.asm.Ae).apply(null, arguments)
};

function ExitStatus(e) {
    this.name = "ExitStatus", this.message = "Program terminated with exit(" + e + ")", this.status = e
}

Module.ccall = ccall, Module.addRunDependency = addRunDependency, Module.removeRunDependency = removeRunDependency, Module.FS_createPath = FS.createPath, Module.FS_createDataFile = FS.createDataFile, Module.FS_createPreloadedFile = FS.createPreloadedFile, Module.FS_createLazyFile = FS.createLazyFile, Module.FS_createDevice = FS.createDevice, Module.FS_unlink = FS.unlink;
var calledMain = !1;

function callMain(e) {
    var t = Module._main, r = (e = e || []).length + 1, n = stackAlloc(4 * (r + 1));
    HEAP32[n >> 2] = allocateUTF8OnStack(thisProgram);
    for (var o = 1; o < r; o++) HEAP32[(n >> 2) + o] = allocateUTF8OnStack(e[o - 1]);
    HEAP32[(n >> 2) + r] = 0;
    try {
        exit(t(r, n), !0)
    } catch (e) {
        if (e instanceof ExitStatus) return;
        if ("unwind" == e) return void (noExitRuntime = !0);
        var a = e;
        e && "object" == typeof e && e.stack && (a = [e, e.stack]), err("exception thrown: " + a), quit_(1, e)
    } finally {
        calledMain = !0
    }
}

function run(e) {
    function t() {
        calledRun || (calledRun = !0, Module.calledRun = !0, ABORT || (initRuntime(), preMain(), Module.onRuntimeInitialized && Module.onRuntimeInitialized(), shouldRunNow && callMain(e), postRun()))
    }

    e = e || arguments_, runDependencies > 0 || (preRun(), runDependencies > 0 || (Module.setStatus ? (Module.setStatus("Running..."), setTimeout(function () {
        setTimeout(function () {
            Module.setStatus("")
        }, 1), t()
    }, 1)) : t()))
}

function exit(e, t) {
    t && noExitRuntime && 0 === e || (noExitRuntime || (EXITSTATUS = e, exitRuntime(), Module.onExit && Module.onExit(e), ABORT = !0), quit_(e, new ExitStatus(e)))
}

if (dependenciesFulfilled = function e() {
    calledRun || run(), calledRun || (dependenciesFulfilled = e)
}, Module.run = run, Module.preInit) for ("function" == typeof Module.preInit && (Module.preInit = [Module.preInit]); Module.preInit.length > 0;) Module.preInit.pop()();
var shouldRunNow = !0;
Module.noInitialRun && (shouldRunNow = !1), run();