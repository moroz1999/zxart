/** Music page — tabs (Tunes / Authors / Top), letter, list */
const MusicScreen = ({ onPlayTune, currentTuneId, isPlaying }) => {
  const [tab, setTab] = React.useState("tunes");
  return (
    <>
      <div className="crumbs">
        <a href="#">Home</a><span className="sep">›</span> <span>Music</span>
      </div>
      <div className="tab-bar">
        <button className={tab === "tunes" ? "active" : ""} onClick={()=>setTab("tunes")}>All tunes</button>
        <button className={tab === "authors" ? "active" : ""} onClick={()=>setTab("authors")}>Authors</button>
        <button className={tab === "top" ? "active" : ""} onClick={()=>setTab("top")}>Top rated</button>
        <button className={tab === "radio" ? "active" : ""} onClick={()=>setTab("radio")}>Radio</button>
      </div>

      {tab === "radio" ? (
        <div className="empty-screen">📻 Radio is broadcasting in the player — keep listening.</div>
      ) : (
        <>
          <div className="zx-letters" style={{ marginBottom: 12 }}>
            <a href="#" className="active">all</a>
            {"ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("").map(L => (
              <a href="#" key={L} onClick={e=>e.preventDefault()}>{L}</a>
            ))}
          </div>

          <div className="toolbar" style={{ marginBottom: 16 }}>
            <div style={{ display: "flex", gap: 6, alignItems: "center", fontSize: 13, color: "var(--text-light-color)" }}>
              Chip:
              <ZxButton size="xs" variant="primary">AY</ZxButton>
              <ZxButton size="xs" variant="outlined">Beeper</ZxButton>
              <ZxButton size="xs" variant="outlined">Turbosound</ZxButton>
            </div>
            <ZxButton size="sm" variant="outlined">Sort: newest</ZxButton>
          </div>

          <div style={{ background: "var(--surface)", border: "1px solid var(--secondary-200)", boxShadow: "var(--shadow-md)", borderRadius: "var(--radius-lg)", overflow: "hidden" }}>
            {SAMPLE_TUNES.map(t => (
              <TuneRow key={t.id} tune={t}
                isCurrent={t.id === currentTuneId}
                isPlaying={t.id === currentTuneId && isPlaying}
                onPlay={onPlayTune} />
            ))}
          </div>
        </>
      )}
    </>
  );
};

window.MusicScreen = MusicScreen;
