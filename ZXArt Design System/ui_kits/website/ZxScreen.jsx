/* Procedural ZX Spectrum-style screen mocks. Every screen renders a deterministic
   pseudo-pixel image using the seed id, so they look distinct without bundling
   real captures. */

function ZxScreen({ seed = 0, palette = "default" }) {
  // 32×24 pixel grid (Spectrum 256×192 / 8)
  const W = 32, H = 24;
  const palettes = {
    default: ["#000","#0d3b66","#1a90ff","#80c2ff","#ffbd04","#bb0000","#9c8c5a","#fff"],
    sunset:  ["#1a0000","#3d0000","#bb0000","#ff3333","#ff8080","#ffbd04","#ffe9ac","#fff0f0"],
    cool:    ["#000f1f","#001d38","#005cb3","#1a90ff","#4da9ff","#80c2ff","#b3daff","#fff"],
    forest:  ["#000","#0d3b66","#1a4d2e","#2d8659","#5cb380","#ffbd04","#fff4d6","#fff"],
    night:   ["#000","#131313","#262626","#404040","#005cb3","#0077ee","#a6a6a6","#fff"],
  };
  const colors = palettes[palette] || palettes.default;

  // deterministic LCG
  function lcg(s) { let x = s + 1; return () => { x = (x * 1664525 + 1013904223) >>> 0; return x / 0xffffffff; }; }
  const rnd = lcg(seed * 99991 + 17);

  // build a "scene": top sky band, a horizon line, sprites, ground tiles
  const cells = [];
  const horizon = 8 + Math.floor(rnd() * 6);
  for (let y = 0; y < H; y++) {
    for (let x = 0; x < W; x++) {
      let c;
      if (y < horizon - 1) c = colors[3];                                  // sky
      else if (y === horizon) c = colors[6];                                // horizon line
      else if (y < horizon + 2) c = (x + y) % 3 === 0 ? colors[2] : colors[3]; // distant
      else c = (Math.floor(rnd() * 4) === 0) ? colors[1] : colors[2];        // ground
      cells.push({ x, y, c });
    }
  }
  // stamp a "character" sprite mid-screen
  const cx = 8 + Math.floor(rnd() * (W - 16));
  const cy = horizon - 3;
  const sprite = [
    "..XX..",
    ".XYYX.",
    ".XYYX.",
    "XYYYYX",
    "X.XX.X",
    ".X..X.",
  ];
  sprite.forEach((row, j) => {
    [...row].forEach((ch, i) => {
      const idx = (cy + j) * W + (cx + i);
      if (cells[idx]) {
        if (ch === "X") cells[idx].c = colors[0];
        else if (ch === "Y") cells[idx].c = colors[4];
      }
    });
  });
  // some "stars" / coins
  for (let k = 0; k < 4; k++) {
    const sx = Math.floor(rnd() * W);
    const sy = Math.floor(rnd() * (horizon - 1));
    const idx = sy * W + sx;
    if (cells[idx]) cells[idx].c = colors[7];
  }

  return (
    <svg viewBox={`0 0 ${W} ${H}`} preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      {cells.map((c, i) => (
        <rect key={i} x={c.x} y={c.y} width="1" height="1" fill={c.c} />
      ))}
    </svg>
  );
}

window.ZxScreen = ZxScreen;
