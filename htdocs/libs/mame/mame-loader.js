// ============================================================
// mame-loader.js — Reusable native-Filesystem MAME loader.
//
// Fetches files via XHR, injects them into Emscripten's MEMFS,
// then starts MAME. No BrowserFS / emularity dependency.
//
// Usage:
//   <canvas id="canvas" class="emscripten"></canvas>
//   <div id="status">Loading...</div>
//   <script src="mame-loader.js"></script>
//   <script>
//     MAMELoader.start({
//       canvas:    document.getElementById("canvas"),
//       status:    document.getElementById("status"),
//       driver:    "tbblue",
//       files:     [{ url: "roms/tbblue.zip", path: "roms/tbblue.zip" }],
//       args:      ["-verbose", "-window",
//                   "-resolution", "720x540", "-nounevenstretch"],
//       diffDir:   true,           // create "diff" dir for CHD differencing
//     });
//   </script>
// ============================================================

var MAMELoader = (function () {
  // ---- helpers ----------------------------------------------------------
  // Web Audio unlock net: capture contexts created by the emulator build and
  // resume them on the first user gesture. Window capture phase runs before
  // SDL/bubble handlers, which swallow game keys (arrows/space) to stop page
  // scroll, so document listeners would never fire for those keys.
  var capturedAudioContexts = [];
  function trackAudioContext(ctx) {
    if (ctx && capturedAudioContexts.indexOf(ctx) < 0) capturedAudioContexts.push(ctx);
    return ctx;
  }
  function wrapAudioContext(name) {
    if (typeof window === "undefined" || typeof window[name] !== "function" || window[name]._mameCaptured) return;
    var Original = window[name];
    var Wrapped = function () {
      var ctx = new (Function.prototype.bind.apply(Original, [null].concat(Array.prototype.slice.call(arguments))))();
      return trackAudioContext(ctx);
    };
    Wrapped.prototype = Original.prototype;
    Wrapped._mameCaptured = true;
    try { window[name] = Wrapped; } catch (ignore) {}
  }
  function resumeCapturedAudio() {
    wrapAudioContext("AudioContext");
    wrapAudioContext("webkitAudioContext");
    capturedAudioContexts.forEach(function (ctx) {
      try { if (ctx && ctx.state === "suspended" && typeof ctx.resume === "function") ctx.resume(); } catch (ignore) {}
    });
  }
  function installGestureResume() {
    var target = (typeof window !== "undefined") ? window : ((typeof document !== "undefined") ? document : null);
    if (!target || target._mameAudioResumeInstalled) return;
    target._mameAudioResumeInstalled = true;
    ["pointerdown", "keydown", "keyup", "touchend"].forEach(function (type) {
      target.addEventListener(type, resumeCapturedAudio, { passive: true, capture: true });
    });
  }

  function setStatus(statusEl, t) {
    if (statusEl) statusEl.textContent = t;
  }

  function fetchFile(url, cb, onprogress) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.responseType = "arraybuffer";
    if (typeof onprogress === "function" && xhr.addEventListener) {
      xhr.addEventListener("progress", function (event) {
        onprogress(event.loaded || 0, event.lengthComputable ? event.total : 0);
      });
    }
    xhr.onload = function () {
      if (xhr.status === 200) {
        cb(new Int8Array(xhr.response));
      } else {
        cb(null, "HTTP " + xhr.status);
      }
    };
    xhr.onerror = function () { cb(null, "network error"); };
    xhr.send();
  }

  // Recursively create a directory path in MEMFS (ignore EEXIST).
  function mkdirP(dir) {
    var parts = dir.split("/").filter(Boolean);
    var cur = "";
    for (var i = 0; i < parts.length; i++) {
      cur += "/" + parts[i];
      try { FS.mkdir(cur); } catch (e) { /* exists */ }
    }
  }

  // ---- core -------------------------------------------------------------

  function start(opts) {
    wrapAudioContext("AudioContext");
    wrapAudioContext("webkitAudioContext");
    installGestureResume();
    var canvas   = opts.canvas;
    var statusEl = opts.status;
    var driver   = opts.driver;
    var files    = opts.files || [];
    var cfgDir   = opts.cfgDir   || null;
    var nvramDir = opts.nvramDir || null;
    var bgfxCurrentChain = opts.bgfxInitialChain || null;
    var fullscreenElement = opts.fullscreenElement || null;
    var waitForGesture = !!opts.waitForGesture;

    // Build argument list
    var args = [driver];
    if (opts.width && opts.height) {
      args.push("-resolution", opts.width + "x" + opts.height, "-nounevenstretch");
    }
    if (opts.args) {
      args = args.concat(opts.args);
    }
    if (cfgDir)   args.push("-cfg_directory", cfgDir);
    if (nvramDir) args.push("-nvram_directory", nvramDir);

    var pending = files.length;
    var fetched = {};
    // Fail-fast: any missing asset aborts the boot instead of launching
    // with a half-populated filesystem.
    var loadFailed = false;
    function failFile(f, err) {
      if (loadFailed) return;
      loadFailed = true;
      console.error("[mame-loader] failed to load " + f.url + ": " + err);
      setStatus(statusEl, "Failed to load " + f.url + ": " + err + " — emulator not started.");
    }

    if (pending === 0) {
      maybeLaunch();
      return;
    }

    var progress = files.map(function () { return { loaded: 0, total: 0 }; });
    function reportProgress(name, cached) {
      if (loadFailed) return;
      // Status line stays a plain per-file message; the fraction goes to
      // the theme loadbar via onProgress (per-event percent text churns
      // and per-file percent is meaningless overall).
      var loaded = 0, total = 0;
      progress.forEach(function (p) {
        loaded += p.loaded;
        total += p.total;
      });
      var frac = total > 0 ? loaded / total : 0;
      if (typeof opts.onProgress === "function") {
        try { opts.onProgress(frac, name); } catch (ignore) {}
      }
    }
    // Optional persistent asset cache (opts.cache = versioned name, e.g.
    // "tbblue-v1"): files are served from IndexedDB on repeat visits and
    // only hit the network — with progress — on a miss. Bump the name to
    // invalidate (older versioned databases are deleted on open). Absent
    // without opts.cache; other pages that never set it keep plain XHR
    // behavior. IndexedDB, unlike CacheStorage, also works on remote
    // plain-http origins, which are not secure contexts.
    var ASSET_DB_PREFIX = "mame-assets-";
    function openAssetDB(version) {
      return new Promise(function (resolve) {
        if (typeof indexedDB === "undefined") { resolve(null); return; }
        if (indexedDB.databases) {
          indexedDB.databases().then(function (infos) {
            infos.forEach(function (info) {
              if (info.name && info.name.indexOf(ASSET_DB_PREFIX) === 0 &&
                  info.name !== ASSET_DB_PREFIX + version) {
                try { indexedDB.deleteDatabase(info.name); } catch (ignore) { }
              }
            });
          }).catch(function () { });
        }
        var req;
        try {
          req = indexedDB.open(ASSET_DB_PREFIX + version, 1);
        } catch (e) { resolve(null); return; }
        req.onupgradeneeded = function () { req.result.createObjectStore("files"); };
        req.onsuccess = function () { resolve(req.result); };
        req.onerror = function () { resolve(null); };
      });
    }
    var assetCacheReady = opts.cache ? openAssetDB(opts.cache) : Promise.resolve(null);
    function idbGet(db, url) {
      return new Promise(function (resolve) {
        try {
          var req = db.transaction("files", "readonly").objectStore("files").get(url);
          req.onsuccess = function () { resolve(req.result || null); };
          req.onerror = function () { resolve(null); };
        } catch (e) { resolve(null); }
      });
    }
    function idbPut(db, url, data) {
      try {
        // Structured clone copies the bytes; the original moves into MEMFS.
        db.transaction("files", "readwrite").objectStore("files").put(data.slice().buffer, url);
      } catch (ignore) { }
    }
    function finishFile(f, index, data, cached) {
      // Mark complete even when the server omitted Content-Length.
      if (!progress[index].total) progress[index].total = progress[index].loaded || 1;
      progress[index].loaded = progress[index].total;
      reportProgress(f.url, cached);
      fetched[f.path] = data;
      pending--;
      if (pending === 0 && !loadFailed) {
        maybeLaunch();
      }
    }
    files.forEach(function (f, index) {
      setStatus(statusEl, "Loading " + (index + 1) + "/" + files.length + ": " + f.url + " ...");
      // Two kinds of file never go in the cache. A blob: URL is a one-shot
      // name for bytes the page already holds — a freshly built SD card, an
      // unpacked release — so caching it would fill the store with entries
      // nothing can ever ask for again. A file marked nocache is one whose
      // URL stays the same while its contents change, like the software a
      // reference page is pointed at: cached, it would serve yesterday's
      // program forever and look like the machine is broken.
      var cacheable = f.url.indexOf("blob:") !== 0 && !f.nocache;
      assetCacheReady.then(function (db) {
        if (db && cacheable) return idbGet(db, f.url);
        return null;
      }).then(function (buffer) {
        if (buffer) {
          progress[index].loaded = progress[index].total = buffer.byteLength;
          console.log("[mame-loader] cache hit: " + f.url + " (" + buffer.byteLength + " bytes, no download)");
          finishFile(f, index, new Int8Array(buffer), true);
          return;
        }
        fetchT0 = Date.now();
        fetchFile(f.url, function (data, err) {
          if (err || !data) {
            failFile(f, err || "empty response");
            return;
          }
          console.log("[mame-loader] network fetch: " + f.url + " (" + data.length + " bytes in " + (Date.now() - fetchT0) + " ms)");
          if (cacheable) {
            assetCacheReady.then(function (db) { if (db) idbPut(db, f.url, data); });
          }
          finishFile(f, index, data);
        }, function (loaded, total) {
          progress[index].loaded = loaded;
          progress[index].total = total;
          reportProgress(f.url);
        });
      });
    });

    // Emularity-style start gate: boot only after a user gesture so the
    // AudioContext is created under sticky activation and starts running.
    // Runs before mame.js loads, so no SDL handler swallows the key yet.
    function maybeLaunch() {
      if (!waitForGesture) {
        setStatus(statusEl, "Launching emulator...");
        launchEmulator();
        return;
      }
      setStatus(statusEl, "Press any key or click to start...");
      var go = function (event) {
        if (event && event.preventDefault) {
          try { event.preventDefault(); } catch (ignore) {}
        }
        window.removeEventListener("keydown", go);
        if (canvas) canvas.removeEventListener("click", go);
        if (statusEl) statusEl.removeEventListener("click", go);
        setStatus(statusEl, "Launching emulator...");
        launchEmulator();
      };
      window.addEventListener("keydown", go);
      if (canvas) canvas.addEventListener("click", go);
      if (statusEl) statusEl.addEventListener("click", go);
    }

    function launchEmulator() {
      // The Module object MUST be set before mame.js is appended.
      Module = {
        canvas: canvas,
        arguments: args,
        noInitialRun: false,
        screenIsReadOnly: true,
        print:    function (t) { console.log(t); },
        printErr: function (t) { console.error(t); },
        preRun: function () {
          // Inject fetched files into MEMFS before main() runs.
          for (var path in fetched) {
            if (!Object.prototype.hasOwnProperty.call(fetched, path)) continue;
            var data = fetched[path];
            var parts = path.split("/");
            var name = parts.pop();
            var dir = parts.join("/");
            if (dir) mkdirP(dir);
            try {
              FS.createDataFile(dir || "/", name, data, true, true);
            } catch (e) {
              console.error("createDataFile failed for " + path + ": " + e);
            }
          }
          // MAME's harddisk CHD open tries writable first; on
          // FILE_NOT_WRITEABLE it falls back to read-only + a differencing
          // image in -diff_directory ("diff").
          if (opts.diffDir) {
            try { mkdirP("diff"); } catch (e) { console.warn("mkdir diff: " + e); }
          }
        },
        onRuntimeInitialized: function () {
          // JSMAME wrappers come from emscripten_post.js (--post-js) and
          // resolve at call time, so nothing to re-create here.
          window.addEventListener("unhandledrejection", function (ev) {
            console.error("[uncaught exception]", ev && ev.reason);
          });
          if (opts._onReady) opts._onReady();
        }
      };

      // Load the emulator script — it picks up the global Module.
      // opts.emulatorJS names it for a page that does not sit beside it: the
      // bare name resolves against the page URL, which on a site with routes
      // is not where the runtime is. Emscripten then finds mame.wasm beside
      // whatever this script's own URL turns out to be.
      var s = document.createElement("script");
      s.src = opts.emulatorJS || "mame.js";
      document.getElementsByTagName("head")[0].appendChild(s);
    }

    // Expose for UI hooks
    // ---- UI hooks ----
    // JSMAME wrappers come from emscripten_post.js but only work once wasm is
    // instantiated, so calls stay gated behind a ready flag, with the first
    // action queued if needed.
    var ready = false;
    var pendingAction = null;
      var muted = false;
      var showFps = false;
    var api = {
      softReset: function () {
        if (ready && JSMAME && typeof JSMAME.soft_reset === "function") {
          JSMAME.soft_reset();
        } else {
          pendingAction = "softReset";
        }
      },
      setMute: function (state) {
        if (!ready || typeof JSMAME === "undefined") return false;
        muted = !!state;
        JSMAME._muted = muted;
        if (typeof JSMAME.sound_manager_mute === "function" && typeof JSMAME.get_sound === "function") {
          JSMAME.sound_manager_mute(JSMAME.get_sound(), muted ? 1 : 0, 0x02);
        }
        return muted;
      },
      mute: function () {
        return this.setMute(true);
      },
      unmute: function () {
        return this.setMute(false);
      },
      toggleMute: function () {
        return muted ? this.unmute() : this.mute();
      },
      setShowFps: function (state) {
        if (!ready || typeof JSMAME === "undefined") return false;
        showFps = (typeof state === "boolean") ? state : !showFps;
        if (typeof JSMAME.ui_set_show_fps === "function" && typeof JSMAME.get_ui === "function") {
          JSMAME.ui_set_show_fps(JSMAME.get_ui(), showFps ? 1 : 0);
        }
        return showFps;
      },
      toggleShowFps: function () {
        return this.setShowFps(!showFps);
      },
      audioState: function () {
        return {
          muted: muted,
          contexts: capturedAudioContexts.map(function (ctx) {
            try {
              return { state: ctx.state, currentTime: +ctx.currentTime.toFixed(2), sampleRate: ctx.sampleRate };
            } catch (ignore) { return { state: "inaccessible" }; }
          })
        };
      },
      resumeAudio: function () {
        var results = [];
        capturedAudioContexts.forEach(function (ctx) {
          try {
            if (ctx && ctx.state === "suspended" && typeof ctx.resume === "function") {
              results.push(ctx.resume().then(function () { return ctx.state; }, function () { return "rejected"; }));
            } else {
              results.push(Promise.resolve(ctx ? ctx.state : "missing"));
            }
          } catch (ignore) { results.push(Promise.resolve("threw")); }
        });
        return Promise.all(results);
      },
      setBgfxChain: function (name) {
        if (!ready || typeof name !== "string" || typeof JSMAME === "undefined" ||
            typeof JSMAME.set_bgfx_chain !== "function") {
          return Promise.resolve(false);
        }
        if (bgfxCurrentChain === name) {
          return Promise.resolve(name);
        }

        try {
          if (!JSMAME.set_bgfx_chain(name)) {
            return Promise.resolve(false);
          }
        } catch (error) {
          console.error("BGFX chain setter failed", error);
          return Promise.resolve(false);
        }

        bgfxCurrentChain = name;
        return Promise.resolve(name);
      },
      visibleArea: function () {
        if (!ready || typeof JSMAME === "undefined" ||
            typeof JSMAME.get_visible_area !== "function") {
          return null;
        }
        var packed = JSMAME.get_visible_area() >>> 0;
        if (!packed) return null;
        return { width: (packed >>> 16) & 0xffff, height: packed & 0xffff };
      },
      // One-shot: shrink the SDL backing to display size once the window
      // exists (canvas.width > 0). Fire-and-forget with a 30s budget;
      // no-op if the binding is missing (older wasm).
      resizeBacking: function (w, h) {
        function fire() {
          if (ready && typeof JSMAME !== "undefined" &&
              typeof JSMAME.resize_window === "function" &&
              canvas && canvas.width > 0) {
            JSMAME.resize_window(w, h);
            return true;
          }
          return false;
        }
        if (!fire()) {
          var tries = 0;
          var poll = setInterval(function () {
            if (fire() || ++tries >= 60) clearInterval(poll);
          }, 500);
        }
      },
    };

    // Store the onRuntimeInitialized callback so launchEmulator can chain it
    opts._onReady = function () {
      ready = true;
      if (statusEl) statusEl.style.display = "none";
      if (pendingAction === "softReset" && JSMAME.soft_reset) {
        JSMAME.soft_reset();
        pendingAction = null;
      }
    };

    return api;
  }

  return { start: start };
})();
