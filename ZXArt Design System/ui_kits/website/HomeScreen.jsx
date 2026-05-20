/** Home page — section grid, featured picture, top tunes, latest prods. */
const HomeScreen = ({ onPlayTune, currentTuneId, isPlaying, onOpenPicture, onNavigate }) => {
  const featured = SAMPLE_PICTURES.slice(0, 4);
  const topTunes = SAMPLE_TUNES.slice(0, 5);
  const latestProds = SAMPLE_PRODS.slice(0, 3);

  return (
    <>
      <div className="section-title">
        <h2>Latest pictures</h2>
        <a className="more" href="#" onClick={(e)=>{e.preventDefault(); onNavigate("pictures");}}>view all →</a>
      </div>
      <div className="grid-pictures">
        {featured.map(p => <PictureCard key={p.id} picture={p} onOpen={onOpenPicture} />)}
      </div>

      <div className="layout-2col" style={{ marginTop: 32 }}>
        <div>
          <div className="section-title">
            <h2>Top tunes this week</h2>
            <a className="more" href="#" onClick={(e)=>{e.preventDefault(); onNavigate("music");}}>view all →</a>
          </div>
          <div style={{ background: "var(--surface)", border: "1px solid var(--secondary-200)", boxShadow: "var(--shadow-md)", borderRadius: "var(--radius-lg)", overflow: "hidden" }}>
            {topTunes.map(t => (
              <TuneRow key={t.id} tune={t}
                isCurrent={t.id === currentTuneId}
                isPlaying={t.id === currentTuneId && isPlaying}
                onPlay={onPlayTune} />
            ))}
          </div>
        </div>
        <aside>
          <div className="section-title"><h2>Latest prods</h2></div>
          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            {latestProds.map(p => <ProdCard key={p.id} prod={p} />)}
          </div>
        </aside>
      </div>

      <div className="section-title" style={{ marginTop: 32 }}>
        <h2>Top groups</h2>
      </div>
      <div className="aside-panel">
        <div className="aside-list">
          {["4th Dimension", "DiHalt", "Outsiders", "BYTEREALMS", "RAZOR 1911", "g0blinish & friends"].map((g, i) => (
            <a href="#" key={g} onClick={e=>e.preventDefault()}>
              <span className="num">{i + 1}.</span>
              <Icon name="game" size={16} />
              <span>{g}</span>
              <span style={{ marginLeft: "auto", color: "var(--text-light-color)", fontFamily: "var(--font-mono)", fontSize: 11 }}>{120 - i * 14} prods</span>
            </a>
          ))}
        </div>
      </div>
    </>
  );
};

window.HomeScreen = HomeScreen;
