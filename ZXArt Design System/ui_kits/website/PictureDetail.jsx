/** Picture detail — hero, metadata, comments, "more by author" aside */
const PictureDetail = ({ picture, onBack }) => {
  if (!picture) return null;
  return (
    <>
      <div className="crumbs">
        <a href="#" onClick={(e)=>{e.preventDefault(); onBack();}}>Home</a><span className="sep">›</span>
        <a href="#" onClick={(e)=>{e.preventDefault(); onBack();}}>Pictures</a><span className="sep">›</span>
        <span>{picture.title}</span>
      </div>

      <div className="detail-hero">
        <div className="detail-hero__image">
          <PixelArtSVG seed={picture.id} palette={picture.palette} />
        </div>
        <div>
          <h1 className="detail-hero__title">{picture.title}</h1>
          <div className="detail-hero__authors">{picture.authors.join(", ")}</div>
          <ZxStars value={picture.stars} count={picture.votes} />
          <dl className="detail-hero__meta" style={{ marginTop: 16 }}>
            <dt>Year</dt><dd>{picture.year}</dd>
            <dt>Format</dt><dd>{picture.format || "—"}</dd>
            <dt>Party</dt><dd>{picture.party || "—"}</dd>
            <dt>Place</dt><dd>{picture.place ? <><ZxMedal place={picture.place} /> {ordinal(picture.place)}</> : "—"}</dd>
            <dt>Border</dt><dd>black</dd>
            <dt>Realtime</dt><dd>{picture.realtime ? "yes" : "no"}</dd>
          </dl>
          <div className="detail-hero__actions">
            <ZxButton variant="primary"><Icon name="download" size={16} /> Download .scr</ZxButton>
            <ZxButton variant="outlined"><Icon name="heartO" size={16} /> Favourite</ZxButton>
            <ZxButton variant="outlined"><Icon name="chat" size={16} /> Comment</ZxButton>
            <ZxButton variant="transparent" shape="square" ariaLabel="Share">
              <Icon name="globe" size={16} />
            </ZxButton>
          </div>
        </div>
      </div>

      <div className="layout-2col">
        <div>
          <div className="section-title"><h2>Comments</h2></div>
          <div className="comments">
            {SAMPLE_COMMENTS.map((c, i) => (
              <div className="comment" key={i}>
                <div className="comment__head">
                  <Icon name="person" size={14} />
                  <span className="comment__author">{c.author}</span>
                  <span className="comment__date">{c.date}</span>
                </div>
                <div className="comment__body">{c.body}</div>
              </div>
            ))}
            <div style={{ marginTop: 8, display: "flex", gap: 8 }}>
              <input className="zx-input" style={{ flex: 1 }} placeholder="Add a comment..." />
              <ZxButton variant="primary">Post</ZxButton>
            </div>
          </div>
        </div>
        <aside>
          <div className="aside-panel" style={{ marginBottom: 16 }}>
            <h3>More by {picture.authors[0]}</h3>
            <div className="aside-list">
              {SAMPLE_PICTURES.filter(p => p.id !== picture.id).slice(0, 5).map(p => (
                <a href="#" key={p.id} onClick={e=>e.preventDefault()}>
                  <Icon name="image" size={14} />
                  <span style={{ overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{p.title}</span>
                  <span style={{ marginLeft: "auto", color: "var(--text-light-color)", fontFamily: "var(--font-mono)", fontSize: 11 }}>{p.year}</span>
                </a>
              ))}
            </div>
          </div>
          <div className="aside-panel">
            <h3>From the same party</h3>
            <div className="aside-list">
              {SAMPLE_PICTURES.slice(0, 4).map(p => (
                <a href="#" key={p.id} onClick={e=>e.preventDefault()}>
                  <span className="num">{p.place || "—"}</span>
                  <span style={{ overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{p.title}</span>
                </a>
              ))}
            </div>
          </div>
        </aside>
      </div>
    </>
  );
};

const SAMPLE_COMMENTS = [
  { author: "Diver/4D",    date: "12 Apr 2003", body: "Reuploaded with a fixed colour-clash on the third row. Thanks for the catches." },
  { author: "Andy/CFM",    date: "13 Apr 2003", body: "That horizon! How did you do the dithering on the sky — Beta line by line?" },
  { author: "g0blinish",   date: "01 Feb 2014", body: "Still one of my favourite SCRs of the era. Aged like wine." },
];

function ordinal(n) {
  const s = ["th","st","nd","rd"], v = n % 100;
  return n + (s[(v - 20) % 10] || s[v] || s[0]) + " place";
}

window.PictureDetail = PictureDetail;
