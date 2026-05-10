/** Prod card — software production tile. */
const ProdCard = ({ prod, onOpen }) => {
  return (
    <div className="zx-prod">
      <a className="zx-prod__cover" href="#" onClick={(e)=>{e.preventDefault(); onOpen?.(prod);}}>
        <PixelArtSVG seed={prod.id + 999} palette={prod.palette || "night"} />
      </a>
      <div className="zx-prod__info">
        <div className="zx-prod__title"><a href="#" onClick={(e)=>{e.preventDefault(); onOpen?.(prod);}}>{prod.title}</a></div>
        <div className="zx-prod__authors">
          {prod.authors.map((a, i) => <span key={i}>{i > 0 && ", "}<a href="#" onClick={e=>e.preventDefault()}>{a}</a></span>)}
        </div>
        <div className="zx-prod__meta">
          <ZxBadge>{prod.kind}</ZxBadge>
          {prod.party && <span>{prod.party}</span>}
          {prod.place && <ZxMedal place={prod.place} />}
          <span style={{ color: "var(--pseudo-link-color)", fontWeight: 700, marginLeft: "auto" }}>{prod.year}</span>
        </div>
        <div style={{ marginTop: 6 }}><ZxStars value={prod.stars} count={prod.votes} /></div>
      </div>
    </div>
  );
};

window.ProdCard = ProdCard;
