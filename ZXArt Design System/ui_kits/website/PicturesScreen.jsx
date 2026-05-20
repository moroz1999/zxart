/** Pictures browser — letter selector, sort, filter chips, grid. */
const PicturesScreen = ({ onOpenPicture }) => {
  const [letter, setLetter] = React.useState("E");
  const [sort, setSort] = React.useState("newest");

  const filtered = SAMPLE_PICTURES; // illustrative — not actually filtered
  return (
    <>
      <div className="crumbs">
        <a href="#">Home</a><span className="sep">›</span> <span>Pictures</span>
      </div>

      <div className="zx-letters" style={{ marginBottom: 12 }}>
        <a href="#" className={letter === "all" ? "active" : ""} onClick={(e)=>{e.preventDefault(); setLetter("all");}}>all</a>
        <a href="#" onClick={(e)=>{e.preventDefault(); setLetter("#");}}>#</a>
        {"ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("").map(L => (
          <a href="#" key={L} className={letter === L ? "active" : ""}
             onClick={(e)=>{e.preventDefault(); setLetter(L);}}>{L}</a>
        ))}
      </div>

      <div className="toolbar" style={{ marginBottom: 16 }}>
        <div style={{ display: "flex", gap: 6, alignItems: "center", fontSize: 13, color: "var(--text-light-color)" }}>
          Sort:
          <ZxButton size="xs" variant={sort === "newest" ? "primary" : "outlined"} onClick={()=>setSort("newest")}>Newest</ZxButton>
          <ZxButton size="xs" variant={sort === "rated"  ? "primary" : "outlined"} onClick={()=>setSort("rated")}>Top rated</ZxButton>
          <ZxButton size="xs" variant={sort === "az"     ? "primary" : "outlined"} onClick={()=>setSort("az")}>A → Z</ZxButton>
        </div>
        <div style={{ display: "flex", gap: 6, alignItems: "center" }}>
          <ZxBadge>.SCR</ZxBadge>
          <ZxBadge>.MC</ZxBadge>
          <ZxBadge>realtime</ZxBadge>
          <ZxButton size="xs" variant="outlined">+ filter</ZxButton>
        </div>
      </div>

      <div className="grid-pictures">
        {filtered.map(p => <PictureCard key={p.id} picture={p} onOpen={onOpenPicture} />)}
      </div>
    </>
  );
};

window.PicturesScreen = PicturesScreen;
