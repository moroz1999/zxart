// MAMEThemeSimple: stock page theme for MAME emscripten drivers.
//
// Owns everything presentational: DOM, CSS, UI hooks (reset/nmi/fullscreen/
// mute/fps/bgfx chains/joystick), the canvas fit policy (2/3 budget, Next
// x2 height, portrait rules, fullscreen settle), and the asset cache name.
// The driver page keeps only its config (driver/files/args/display) and one
// mount() call, e.g.:
//
//   MAMEThemeSimple.mount(document.getElementById("app"), {
//     driver: "tbblue",
//     files: [ { url: "roms/tbblue.zip", path: "roms/tbblue.zip" },
//              { url: "software/next.chd", path: "next.chd" } ],
//     args: ["-window", "-nounevenstretch",
//            "-video", "bgfx", "-bgfx_screen_chains", "unfiltered",
//            "-hard1", "next.chd"],
 //   });
 //
 // diffDir defaults to true (harmless empty dir when no writable image is
 // mounted); set false to skip. bgfxInitialChain defaults to "unfiltered";
 // set another chain, or null to leave MAME's default chain alone.
 //
 // config.cache overrides the default asset cache name when given.
var MAMEThemeSimple = (function () {
  var CACHE_PREFIX = "mame-simple-v1-";

  var CSS = [
    "body {",
    "  background-color: #2a4e96;",
    "  font-family: sans-serif;",
    "  color: #fff;",
    "}",
    "canvas.emscripten {",
    "    border: 0 none;",
    "    background-color: #000;",
    "    display: block;",
    "    margin: 0 auto;",
    "    width: calc(100% * 2 / 3);",
    "    aspect-ratio: 4 / 3;",
    "    height: auto;",
    "}",
    "#emulator-screen {",
    "  position: relative;",
    "  display: block;",
    "  background: #000;",
    "}",
    "#emulator-screen:fullscreen, #emulator-screen.fs-fallback {",
    "  width: 100vw;",
    "  height: 100vh;",
    "  display: flex;",
    "  align-items: center;",
    "  justify-content: center;",
    "}",
    "#emulator-screen.fs-fallback {",
    "  position: fixed;",
    "  top: 0;",
    "  left: 0;",
    "  z-index: 9999;",
    "}",
    "#emulator-screen:fullscreen canvas, #emulator-screen.fs-fallback canvas {",
    "  width: 100%;",
    "  height: 100%;",
    "  max-width: none;",
    "  aspect-ratio: auto;",
    "  object-fit: contain;",
    "}",
    "body.fs-lock {",
    "  overflow: hidden;",
    "}",
    "#joystick-controls {",
    "  display: none;",
    "}",
    "#emulator-screen:fullscreen #joystick-controls, #emulator-screen.fs-fallback #joystick-controls {",
    "  position: absolute;",
    "  right: 0;",
    "  bottom: max(12px, env(safe-area-inset-bottom));",
    "  left: 0;",
    "  display: none;",
    "  justify-content: space-between;",
    "  align-items: flex-end;",
    "  padding: 0 16px;",
    "  touch-action: none;",
    "}",
    "#emulator-screen:fullscreen.show-touch #joystick-controls, #emulator-screen.fs-fallback.show-touch #joystick-controls {",
    "  display: flex;",
    "}",
    "#touch-toggle {",
    "  display: none;",
    "}",
    "#fs-reset {",
    "  display: none;",
    "}",
    "#emulator-screen:fullscreen #touch-toggle, #emulator-screen.fs-fallback #touch-toggle {",
    "  position: absolute;",
    "  top: max(10px, env(safe-area-inset-top));",
    "  right: 12px;",
    "  display: block;",
    "  z-index: 2;",
    "  padding: 6px 10px;",
    "  border: 1px solid rgba(170, 170, 170, 0.6);",
    "  border-radius: 8px;",
    "  background: rgba(51, 51, 51, 0.45);",
    "  color: rgba(255, 255, 255, 0.9);",
    "  font-size: 14px;",
    "  cursor: pointer;",
    "}",
    "#emulator-screen:fullscreen #fs-reset, #emulator-screen.fs-fallback #fs-reset {",
    "  position: absolute;",
    "  top: max(10px, env(safe-area-inset-top));",
    "  left: 12px;",
    "  display: block;",
    "  z-index: 2;",
    "  padding: 6px 10px;",
    "  border: 1px solid rgba(170, 170, 170, 0.6);",
    "  border-radius: 8px;",
    "  background: rgba(51, 51, 51, 0.45);",
    "  color: rgba(255, 255, 255, 0.9);",
    "  font-size: 14px;",
    "  cursor: pointer;",
    "}",
    "#joystick-controls .dpad {",
    "  display: grid;",
    "  grid-template-columns: repeat(3, 64px);",
    "  grid-auto-rows: 52px;",
    "  gap: 6px;",
    "}",
    '#joystick-controls .dpad [data-joystick-key="up"] { grid-column: 2; grid-row: 1; }',
    '#joystick-controls .dpad [data-joystick-key="left"] { grid-column: 1; grid-row: 2; }',
    '#joystick-controls .dpad [data-joystick-key="right"] { grid-column: 3; grid-row: 2; }',
    '#joystick-controls .dpad [data-joystick-key="down"] { grid-column: 2; grid-row: 3; }',
    "#joystick-controls .fire-buttons {",
    "  display: flex;",
    "  gap: 88px;",
    "}",
    "/* Narrow portrait phones: side-by-side A/B overflows, stack A above B */",
    "@media (max-width: 500px) {",
    "  #joystick-controls .fire-buttons {",
    "    flex-direction: column;",
    "    gap: 12px;",
    "  }",
    "}",
    "#joystick-controls .fire-buttons button {",
    "  min-width: 88px;",
    "  min-height: 88px;",
    "  border-radius: 50%;",
    "  font-size: 18px;",
    "}",
    "#joystick-controls button {",
    "  min-width: 58px;",
    "  min-height: 48px;",
    "  border: 1px solid rgba(170, 170, 170, 0.6);",
    "  border-radius: 8px;",
    "  background: rgba(51, 51, 51, 0.45);",
    "  color: rgba(255, 255, 255, 0.9);",
    "  font-size: 22px;",
    "  touch-action: none;",
    "}",
    "#joystick-controls button.pressed {",
    "  background: rgba(51, 51, 51, 1);",
    "  border-color: rgba(255, 255, 255, 0.9);",
    "}",
    "#status {",
    "  text-align: center;",
    "  margin-top: 10px;",
    "  min-height: 1.5em;",
    "}",
    "#loadbar {",
    "  display: none;",
    "  width: 320px;",
    "  max-width: 80vw;",
    "  height: 10px;",
    "  margin: 8px auto 0;",
    "  border: 1px solid #aaa;",
    "  border-radius: 5px;",
    "}",
    "#loadbar > div {",
    "  height: 100%;",
    "  width: 0;",
    "  background: #7fb2ff;",
    "  border-radius: 4px;",
    "}",
    "#controls {",
    "  text-align: center;",
    "  margin-top: 10px;",
    "}",
    "#controls span {",
    "  margin: 0 8px;",
    "  cursor: pointer;",
    "  text-decoration: underline;",
    "}",
  ].join("\n");

  // On-screen joystick: inject keys the way the browser delivers them. The
  // KeyboardEvent constructor ignores keyCode/which (they read 0), and a
  var defaultKeys = { up: "ArrowUp", down: "ArrowDown", left: "ArrowLeft", right: "ArrowRight", fireA: "Space", fireB: "Enter", nmi: "F12" };
  var keyCodes = defaultKeys;
  // Fullscreen touch schemes, cycled by the Controls button. Order is
  // Up Down Left Right A B.
  var keySchemes = {
    kemp: { up: "ArrowUp", down: "ArrowDown", left: "ArrowLeft", right: "ArrowRight", fireA: "Enter", fireB: "Space" },
    qaop: { up: "KeyQ", down: "KeyA", left: "KeyO", right: "KeyP", fireA: "KeyM", fireB: "Space" },
    sinclair1: { up: "Digit9", down: "Digit8", left: "Digit6", right: "Digit7", fireA: "Enter", fireB: "Digit0" },
  };
  var schemeOrder = ["kemp", "qaop", "sinclair1"];
  var domKeyCodes = { ArrowUp: 38, ArrowDown: 40, ArrowLeft: 37, ArrowRight: 39, Enter: 13, Space: 32, F12: 123,
    KeyQ: 81, KeyA: 65, KeyO: 79, KeyP: 80, KeyM: 77, Digit9: 57, Digit8: 56, Digit6: 54, Digit7: 55, Digit0: 48 };

  function ensureCss() {
    if (document.getElementById("mame-simple-css")) return;
    var style = document.createElement("style");
    style.id = "mame-simple-css";
    style.textContent = CSS;
    document.getElementsByTagName("head")[0].appendChild(style);
  }

  function buildDom(root, config) {
    var chains = config.bgfxChains || [{ chain: "unfiltered", label: "Unfiltered" }, { chain: "crt-geom-deluxe", label: "CRT Filter" }];
    var chainHtml = chains.map(function (c) {
      return '<span data-bgfx-chain="' + c.chain + '">' + c.label + "</span>";
    }).join("");
    root.innerHTML =
      '<div id="controls">' +
        '<span id="reset">Reset</span>' +
        (config.nmi === true ? '<span id="nmi">NMI</span>' : '') +
        '<span id="fullscr">Full Screen</span>' +
        '<span id="mute">Mute</span>' +
        '<span id="fps">FPS</span>' +
        chainHtml +
      "</div>" +
      '<div id="emulator-screen">' +
        '<canvas id="canvas" class="emscripten" width="720" height="540" style="visibility:hidden"></canvas>' +
        '<span id="touch-toggle">Controls</span>' +
        '<span id="fs-reset">Reset</span>' +
        '<div id="joystick-controls" aria-label="Joystick controls">' +
          '<div class="dpad">' +
            '<button type="button" data-joystick-key="up" aria-label="Up">▲</button>' +
            '<button type="button" data-joystick-key="left" aria-label="Left">◀</button>' +
            '<button type="button" data-joystick-key="right" aria-label="Right">▶</button>' +
            '<button type="button" data-joystick-key="down" aria-label="Down">▼</button>' +
          "</div>" +
          '<div class="fire-buttons">' +
            '<button type="button" data-joystick-key="fireA" aria-label="A">A</button>' +
            '<button type="button" data-joystick-key="fireB" aria-label="B">B</button>' +
          "</div>" +
        "</div>" +
      "</div>" +
      '<div id="status">Loading...</div>' +
      '<div id="loadbar"><div></div></div>';
  }
  function mount(root, config) {
    ensureCss();
    buildDom(root, config);

    var canvas = root.querySelector("#canvas");
    var screen = root.querySelector("#emulator-screen");
    var status = root.querySelector("#status");
    var loadbar = root.querySelector("#loadbar");
    // Hardcoded frame size from the page's -resolution (parsed below).
    // Windowed shows it 1:1; fullscreen fills (see onFsChange).
    var frameW = 0, frameH = 0;
    var emulator = MAMELoader.start({
      canvas: canvas,
      status: status,
      fullscreenElement: screen,
      driver: config.driver,
      cache: config.cache || (CACHE_PREFIX + config.driver),
      files: config.files || [],
      // -maximize takes the get_max_bounds path (viewport aspect-constrained)
      // instead of the tiny get_min_bounds default for the initial window.
      args: (config.args || []).concat(["-maximize"]),
      diffDir: ("diffDir" in config) ? config.diffDir : true,
      cfgDir: config.cfgDir || null,
      nvramDir: config.nvramDir || null,
      bgfxInitialChain: ("bgfxInitialChain" in config)
        ? config.bgfxInitialChain
        : "unfiltered",
      onProgress: function (frac) {
        if (!loadbar) return;
        loadbar.style.display = "block";
        loadbar.firstElementChild.style.width = Math.floor(frac * 100) + "%";
        if (frac >= 1) loadbar.style.display = "none";
      },
    });
    // Pre-size the frame before MAME starts: the page's -resolution is the
    // creation size (clamped by -maximize), so mirror it into the canvas
    // now and contain immediately. Boot then lands with no snap - MAME
    // creates the same-size backing it was told to.
    (function () {
      var args = config.args || [];
      for (var i = 0; i + 1 < args.length; i++) {
        if (args[i] === "-resolution") {
          var m = /^(\d+)x(\d+)$/.exec(args[i + 1] || "");
          if (m) {
            frameW = +m[1]; frameH = +m[2];
            canvas.width = frameW; canvas.height = frameH;
            containCanvas();
          }
          break;
        }
      }
    })();

    // ---- UI hooks ----
    root.querySelector("#reset").onclick = function () { emulator.softReset(); };
    root.querySelector("#fs-reset").onclick = function () { emulator.softReset(); };
    root.querySelector("#fullscr").onclick = function () { toggleFullscreen(); };
    root.querySelector("#mute").onclick = function () { var m = emulator.toggleMute(); this.textContent = m ? "Unmute" : "Mute"; };
    root.querySelector("#fps").onclick = function () { var f = emulator.toggleShowFps(); this.textContent = f ? "No FPS" : "FPS"; };
    var touchToggle = root.querySelector("#touch-toggle");
    var schemePos = 0; // 0 = none (hidden); 1..schemeOrder.length
    touchToggle.onclick = function () {
      schemePos = (schemePos + 1) % (schemeOrder.length + 1);
      var name = schemeOrder[schemePos - 1] || null;
      if (name) {
        keyCodes = keySchemes[name];
        screen.classList.add("show-touch");
        this.textContent = name.toUpperCase();
      } else {
        keyCodes = defaultKeys;
        screen.classList.remove("show-touch");
        this.textContent = "Controls";
      }
    };
    Array.prototype.forEach.call(root.querySelectorAll("[data-bgfx-chain]"), function (control) {
      control.onclick = function () {
        var current = this;
        current.style.pointerEvents = "none";
        emulator.setBgfxChain(current.getAttribute("data-bgfx-chain")).then(function (chain) {
          if (chain) {
            Array.prototype.forEach.call(root.querySelectorAll("[data-bgfx-chain]"), function (other) {
              other.style.textDecoration = (other === current) ? "none" : "underline";
            });
          }
          current.style.pointerEvents = "";
        });
      };
    });

    function eventKey(code) {
      if (code === "Space") return " ";
      if (code.indexOf("Key") === 0) return code.charAt(3).toLowerCase();
      if (code.indexOf("Digit") === 0) return code.charAt(5);
      return code;
    }
    function sendKeyEvent(type, key) {
      var code = keyCodes[key];
      if (!code) return;
      var event;
      try {
        event = new KeyboardEvent(type, { key: eventKey(code), code: code, bubbles: true, cancelable: true });
        Object.defineProperty(event, "keyCode", { value: domKeyCodes[code] || 0 });
        Object.defineProperty(event, "which", { value: domKeyCodes[code] || 0 });
      } catch (ignore) { return; }
      (canvas || document).dispatchEvent(event);
    }
    Array.prototype.forEach.call(root.querySelectorAll("#joystick-controls button"), function (button) {
      var key = button.getAttribute("data-joystick-key");
      var press = function (event) {
        event.preventDefault();
        button.setPointerCapture && event.pointerId !== undefined && (function () { try { button.setPointerCapture(event.pointerId); } catch (ignore) {} })();
        button.classList.add("pressed");
        sendKeyEvent("keydown", key);
      };
      var release = function (event) {
        event.preventDefault();
        button.classList.remove("pressed");
        sendKeyEvent("keyup", key);
      };
      button.addEventListener("pointerdown", press);
      button.addEventListener("pointerup", release);
      button.addEventListener("pointercancel", release);
      button.addEventListener("lostpointercapture", release);
      button.addEventListener("contextmenu", function (event) { event.preventDefault(); });
    });
    // NMI is edge-triggered on the F12 port bit; hold it past a frame so a
    // quick tap can't land inside one input poll and vanish.
    var nmiBtn = root.querySelector("#nmi");
    if (nmiBtn) nmiBtn.onclick = function () {
      sendKeyEvent("keydown", "nmi");
      setTimeout(function () { sendKeyEvent("keyup", "nmi"); }, 150);
    };

    // ---- Canvas display: page-declared size, CSS only. Windowed shows the
    // -resolution frame 1:1 (shrunk only if the viewport is smaller, e.g.
    // phones); fullscreen aspect-fills; re-contained on browser
    // resize/fullscreen changes.
    function containCanvas(fracW, fracH) {
      var bw = canvas.width, bh = canvas.height;
      if (!(bw > 0 && bh > 0)) return null;
      if (typeof fracW !== "number" || typeof fracH !== "number") {
        if (frameW > 0 && frameH > 0) {
          var s = Math.min(1, window.innerWidth / frameW, window.innerHeight / frameH);
          canvas.style.width = Math.floor(frameW * s) + "px";
          canvas.style.height = Math.floor(frameH * s) + "px";
          canvas.style.visibility = "visible";
          return true;
        }
        fracW = 2 / 3; fracH = 2 / 3;
      }
      var s = Math.min(window.innerWidth * fracW / bw, window.innerHeight * fracH / bh);
      canvas.style.width = Math.floor(bw * s) + "px";
      canvas.style.height = Math.floor(bh * s) + "px";
      canvas.style.visibility = "visible";
      return true;
    }
    function fsActive() {
      return !!(document.fullscreenElement || document.webkitFullscreenElement ||
        (screen && screen.classList.contains("fs-fallback")));
    }
    // Fullscreen transitions animate: innerWidth/Height pass through
    // transient values. Re-containing on each one makes the picture jump.
    // resize events during a transition are noise; the settle poll below
    // contains once, when dimensions stop changing.
    var transitioning = false;
    var wasFs = false;
    function onFsChange() {
      var fs = fsActive();
      if (fs && !wasFs) {
        // Touch controls default off in fullscreen; Controls re-enables.
        schemePos = 0;
        keyCodes = defaultKeys;
        screen.classList.remove("show-touch");
        touchToggle.textContent = "Controls";
      }
      wasFs = fs;
      if (fs) containCanvas(1, 1);
      else containCanvas();
    }
    function onResize() {
      if (!transitioning) onFsChange();
    }
    var bootPoll = setInterval(function () {
      if (containCanvas()) clearInterval(bootPoll);
    }, 1000);
    setInterval(onFsChange, 5000);
    window.addEventListener("resize", onResize);
    function onFsEvent() {
      // Esc-key exits never set transitioning, so the event is the only
      // signal there. During a toggle transition the settle poll owns it.
      if (!transitioning) onFsChange();
    }
    document.addEventListener("fullscreenchange", onFsEvent);
    document.addEventListener("webkitfullscreenchange", onFsEvent);

    // Browsers without element fullscreen (iOS Safari) get an in-page
    // fallback: same CSS and sizing, driven by class instead of the
    // fullscreen element. Never cede fullscreen to the canvas itself —
    // SDL would fill the screen its own way and hide these controls.
    function toggleFullscreen() {
      var req = screen.requestFullscreen || screen.webkitRequestFullscreen;
      if (req) {
        if (fsActive() && document.exitFullscreen) document.exitFullscreen();
        else req.call(screen);
        // Exit transitions can take seconds and the fullscreen event is
        // unreliable on mobile: poll until the state settles instead of
        // firing once at a fixed delay (which lands mid-exit and no-ops).
        transitioning = true;
        var tries = 0, lastKey = "";
        var settle = setInterval(function () {
          var key = window.innerWidth + "x" + window.innerHeight;
          if (key === lastKey || ++tries >= 10) {
            transitioning = false;
            onFsChange();
            clearInterval(settle);
          }
          lastKey = key;
        }, 500);
        return;
      }
      var on = screen.classList.toggle("fs-fallback");
      document.body.classList.toggle("fs-lock", on);
      onFsChange();
    }

    return emulator;
  }

  return { mount: mount };
})();
