/** Picture card — derived from ng-zxart/src/app/entities/picture/ui/picture-card */
const PictureCard = ({ picture, onOpen }) => {
  return (
    <article className="zx-picture-card">
      <div className="zx-picture-card__panel">
        <a className="zx-picture-card__image" href="#" onClick={(e)=>{e.preventDefault(); onOpen?.(picture);}}>
          <PixelArtSVG seed={picture.id} palette={picture.palette} />
          <div className="zx-picture-card__badges">
            {picture.format && <ZxBadge>{picture.format}</ZxBadge>}
            {picture.realtime && <ZxBadge>realtime</ZxBadge>}
            {picture.flickering && <ZxBadge>flickering</ZxBadge>}
          </div>
        </a>
        <div className="zx-picture-card__info">
          <a className="zx-picture-card__title" href="#" onClick={(e)=>{e.preventDefault(); onOpen?.(picture);}}>
            {picture.title}
          </a>
          <div className="zx-picture-card__authors">
            {picture.authors.map((a, i) => (
              <span key={i}>{i > 0 && ", "}<a href="#" onClick={(e)=>e.preventDefault()}>{a}</a></span>
            ))}
          </div>
          {picture.party && (
            <div className="zx-picture-card__party">
              {picture.place && <ZxMedal place={picture.place} />}
              <a href="#" onClick={(e)=>e.preventDefault()}>{picture.party}</a>
            </div>
          )}
          <div className="zx-picture-card__bottom">
            <ZxStars value={picture.stars} count={picture.votes} />
            <span className="zx-picture-card__year">{picture.year}</span>
          </div>
        </div>
      </div>
    </article>
  );
};

/** Procedurally generated pixel-art placeholder. Stable per `seed`. */
const PixelArtSVG = ({ seed = 0, palette = "default" }) => {
  // small deterministic PRNG
  let s = seed * 9301 + 49297;
  const rand = () => { s = (s * 9301 + 49297) % 233280; return s / 233280; };

  const palettes = {
    default: ["#000033", "#aa0000", "#ffaa00", "#ffffff", "#0066cc"],
    sunset:  ["#1a0033", "#cc3300", "#ff9933", "#ffcc66", "#330011"],
    cool:    ["#001133", "#003366", "#3399cc", "#66ccff", "#ffffff"],
    forest:  ["#001100", "#114411", "#226622", "#88aa44", "#ddee99"],
    night:   ["#000022", "#221144", "#553388", "#aa66cc", "#ffeebb"],
  };
  const colors = palettes[palette] || palettes.default;

  const cols = 32, rows = 24;
  const cells = [];
  // sky gradient
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      const v = rand();
      let c;
      if (y > rows - 6) c = colors[1]; // ground
      else if (y > rows - 9 && v > 0.7) c = colors[2]; // mid foliage
      else if (y < 4 && v > 0.94) c = colors[3]; // stars
      else if (y > 4 && y < rows - 9 && v > 0.97) c = colors[4]; // distant
      else c = colors[0];
      cells.push(<rect key={`${x}-${y}`} x={x} y={y} width={1} height={1} fill={c} />);
    }
  }
  // a few "buildings" silhouettes
  for (let i = 0; i < 5; i++) {
    const bx = Math.floor(rand() * cols);
    const bw = 1 + Math.floor(rand() * 3);
    const bh = 2 + Math.floor(rand() * 6);
    cells.push(<rect key={`b${i}`} x={bx} y={rows - 6 - bh} width={bw} height={bh} fill={colors[0]} />);
  }
  return (
    <svg viewBox="0 0 32 24" preserveAspectRatio="xMidYMid meet"
         xmlns="http://www.w3.org/2000/svg"
         style={{ width: "100%", height: "100%", display: "block", imageRendering: "pixelated" }}
         shapeRendering="crispEdges">
      {cells}
    </svg>
  );
};

window.PictureCard = PictureCard;
window.PixelArtSVG = PixelArtSVG;
