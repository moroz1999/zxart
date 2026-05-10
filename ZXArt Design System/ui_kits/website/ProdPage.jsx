/* Merged prod page — based on user feedback:
   - Hero/info block from Variant A (rich, scannable)
   - Releases table from Variant B (full-width, sortable, votable)
   - NO right sidebar (site already has one)
   - NO "recommended" badge (subjective — solved by sortable votes column instead)
*/
const { useState } = React;

function ProdPage() {
  const [tab, setTab] = useState("releases");
  const [mediaSub, setMediaSub] = useState("articles"); // articles | covers | music | graphics
  const [linksSub, setLinksSub] = useState("series"); // series | compilations
  const [view, setView] = useState("table");
  const [filterLang, setFilterLang] = useState("all");
  const [filterType, setFilterType] = useState("all");
  const [sortBy, setSortBy] = useState("votes"); // votes | year | downloads | plays
  const [sortDir, setSortDir] = useState("desc");

  const filtered = RELEASES.filter(r =>
    (filterLang === "all" || r.lang === filterLang) &&
    (filterType === "all" || r.type === filterType)
  );
  const sorted = [...filtered].sort((a,b) => {
    const va = a[sortBy] ?? 0, vb = b[sortBy] ?? 0;
    return sortDir === "desc" ? (vb - va) : (va - vb);
  });
  function clickHeader(key) {
    if (sortBy === key) setSortDir(sortDir === "desc" ? "asc" : "desc");
    else { setSortBy(key); setSortDir("desc"); }
  }
  const arrow = (k) => sortBy === k ? (sortDir === "desc" ? " ↓" : " ↑") : "";

  return (
    <div style={{ padding: 24, maxWidth: 1280, margin: "0 auto", fontFamily: "var(--font-body)", color: "var(--text-color)", background: "var(--background-page)", minHeight: 1700 }}>
      <div style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)", marginBottom: 12, fontFamily: "var(--font-mono)" }}>
        Главная / Софт / Игры / Приключения / Квесты-головоломки / <span style={{ color: "var(--text-color)" }}>Crystal Kingdom Dizzy</span>
      </div>

      {/* HERO — from Variant A, no sidebar */}
      <div className="va-hero">
        <div className="va-hero__cover">
          <ZxScreen seed={42} palette="forest" />
          <div className="va-hero__shots">📷 {SCREENS.length} скринов</div>
        </div>
        <div>
          <div className="va-hero__title-row">
            <h1 className="va-hero__title">{PROD.title}</h1>
            <span className="va-hero__year">· {PROD.year}</span>
          </div>
          <div className="va-hero__alias">также известна как <i>{PROD.alsoKnownAs}</i></div>

          <div className="va-hero__chips">
            {PROD.category.map(c => <span key={c} className="chip chip--cat">{c}</span>)}
            <span className="chip">🇬🇧 English</span>
            <span className="chip" title={PROD.status}>⚠ Распространение запрещено</span>
          </div>

          <div className="va-hero__rating-row">
            <div className="va-hero__rating">
              <span className="num">{PROD.rating.score}</span>
              <span className="of">/ {PROD.rating.ofFive}</span>
              <span className="votes">· {PROD.rating.votes} голосов</span>
            </div>
            <VoteWidget myVote={4} fav={false} />
            <span style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>{PROD.rating.votes} голосов · в избранном у 18</span>
            <span style={{ marginLeft: "auto", fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>Добавлена {PROD.added}</span>
          </div>

          <div className="va-hero__people">
            <b>Авторы:</b> {PROD.authors.join(", ")} · <b>Музыка:</b> {PROD.music} · <b>Издатель:</b> {PROD.publisher} · <b>Разработчик:</b> {PROD.developer}
          </div>

          <div style={{ marginTop: 10, fontSize: "var(--font-xs)", display: "flex", gap: 12, flexWrap: "wrap" }}>
            {PROD.links.map((l,i) => (
              <a key={i} href="#" style={{ color: "var(--primary-600)", textDecoration: "none" }}>↗ {l.label}</a>
            ))}
          </div>
        </div>
      </div>

      {/* SCREENS strip — 6 + "посмотреть ещё" */}
      <div className="pp-card" style={{ marginBottom: 16, padding: 12 }}>
        <div className="pp-section-h" style={{ marginBottom: 10 }}>
          Скрины <span className="count">{SCREENS.length}</span>
        </div>
        <div className="va-screens">
          <div className="va-screens__cell va-screens__cell--big">
            <ZxScreen seed={SCREENS[0].id} palette={SCREENS[0].palette} />
          </div>
          {SCREENS.slice(1, 6).map(s => (
            <div key={s.id} className="va-screens__cell"><ZxScreen seed={s.id} palette={s.palette} /></div>
          ))}
          <a href="#" className="va-screens__cell va-screens__more">
            Посмотреть ещё<br/><span style={{fontSize:"var(--font-xs)",fontWeight:400,opacity:0.7}}>+{SCREENS.length - 6} скринов</span>
          </a>
        </div>
      </div>

      {/* STORY + tags */}
      <div className="pp-card" style={{ marginBottom: 16 }}>
        <div className="pp-section-h">О программе</div>
        <p style={{ margin: 0, lineHeight: 1.65, fontSize: "var(--font-md)" }}>{PROD.story}</p>
        <div style={{ marginTop: 14, paddingTop: 12, borderTop: "1px dashed var(--secondary-200)", display: "flex", gap: 6, flexWrap: "wrap", alignItems: "center" }}>
          <span style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)", textTransform: "uppercase", letterSpacing: "0.04em", marginRight: 4 }}>Теги:</span>
          {PROD.tags.map(t => (
            <a key={t} href="#" style={{ fontSize: "var(--font-xs)", padding: "2px 8px", background: "var(--secondary-100)", border: "1px solid var(--secondary-200)", borderRadius: 999, color: "var(--text-light-color)", textDecoration: "none" }}>{t}</a>
          ))}
        </div>
      </div>

      {/* TABS — 4 groups */}
      <div className="va-tabs">
        <button className={tab==="releases"?"active":""} onClick={()=>setTab("releases")}>Релизы <span className="num">{RELEASES.length}</span></button>
        <button className={tab==="media"?"active":""} onClick={()=>setTab("media")}>Медиа <span className="num">{MENTIONS.length + MAPS.length + PROD_TUNES.length}</span></button>
        <button className={tab==="links"?"active":""} onClick={()=>setTab("links")}>Связи <span className="num">{COMPILATIONS.length + SAME_SERIES.length}</span></button>
        <button className={tab==="discussion"?"active":""} onClick={()=>setTab("discussion")}>Обсуждение <span className="num">{VOTES.length}</span></button>
      </div>

      {tab==="releases" && (
        <div>
          <div className="vb-filter-bar">
            <span className="vb-filter-bar__label">Язык:</span>
            <div className="vb-filter-bar__group">
              {[["all","все"],["en","🇬🇧 EN"],["ru","🇷🇺 RU"]].map(([k,l]) => (
                <button key={k} onClick={()=>setFilterLang(k)} className={"zx-button zx-button--sm " + (filterLang===k?"zx-button--secondary":"zx-button--outlined")}>{l}</button>
              ))}
            </div>
            <div className="vb-filter-bar__sep"></div>
            <span className="vb-filter-bar__label">Тип:</span>
            <div className="vb-filter-bar__group">
              {[["all","все"], ...Object.entries(RELEASE_TYPES).map(([k,v])=>[k,v.label])].map(([k,l]) => (
                <button key={k} onClick={()=>setFilterType(k)} className={"zx-button zx-button--sm " + (filterType===k?"zx-button--secondary":"zx-button--outlined")}>{l}</button>
              ))}
            </div>
            <div className="vb-toggle">
              <button className={view==="table"?"active":""} onClick={()=>setView("table")}>☰ таблица</button>
              <button className={view==="cards"?"active":""} onClick={()=>setView("cards")}>▦ карточки</button>
            </div>
          </div>

          <div style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)", margin: "0 0 6px", fontStyle: "italic" }}>
            Лучший релиз — тот, у которого выше рейтинг сообщества. Сортируйте по «Рейтинг», ⬇ или ▶, чтобы найти подходящий.
          </div>

          {view==="table" && (
            <table className="vb-rel-table">
              <thead>
                <tr>
                  <th></th>
                  <th>Название · автор</th>
                  <th onClick={()=>clickHeader("year")} style={{cursor:"pointer"}}>Год{arrow("year")}</th>
                  <th>Тип</th>
                  <th>Яз.</th>
                  <th>Платформа · формат</th>
                  <th onClick={()=>clickHeader("votes")} style={{cursor:"pointer", textAlign:"right"}} title="Рейтинг релиза по голосам сообщества">Рейтинг{arrow("votes")}</th>
                  <th onClick={()=>clickHeader("plays")} style={{cursor:"pointer", textAlign:"right"}}>▶{arrow("plays")}</th>
                  <th onClick={()=>clickHeader("downloads")} style={{cursor:"pointer", textAlign:"right"}}>⬇{arrow("downloads")}</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {sorted.map(r => (
                  <tr key={r.id}>
                    <td><div className="vb-rel-table__shot">{r.screens.length ? <ZxScreen seed={r.id*13} palette={["sunset","cool","forest","night","default"][r.id%5]} /> : null}</div></td>
                    <td>
                      <div className="vb-rel-table__title">{r.title}</div>
                      <div className="vb-rel-table__by">{r.releasedBy || "—"}{r.note ? " · " + r.note : ""}</div>
                    </td>
                    <td style={{fontFamily:"var(--font-mono)"}}>{r.year || "—"}</td>
                    <td><span className={"rel-type-pill rel-type-pill--" + r.type}>{RELEASE_TYPES[r.type].label}</span></td>
                    <td>{r.lang==="ru"?"🇷🇺":"🇬🇧"}</td>
                    <td style={{fontSize:"var(--font-xs)"}}>
                      <div className="tag-row">
                        {r.format && <span className="tag-glyph" title={r.format}>{r.format.includes("SCL")?"💾":"📼"}</span>}
                        {r.hardware.slice(0,3).map(h => <span key={h} className="tag-glyph" title={h}>{h.includes("AY")?"🔊":h.includes("джойстик")?"🕹":"🖥"}</span>)}
                      </div>
                    </td>
                    <td style={{textAlign:"right",fontFamily:"var(--font-mono)"}}>
                      <span style={{ display:"inline-flex", alignItems:"center", gap:3, color: "var(--warning-700)", fontWeight: 700 }} title={`${r.votes || 0} голосов`}>
                        <span style={{color:"var(--warning-500)"}}>★</span>{r.votes || "—"}
                      </span>
                    </td>
                    <td style={{textAlign:"right",fontFamily:"var(--font-mono)",color:"var(--text-light-color)"}}>{r.plays}</td>
                    <td style={{textAlign:"right",fontFamily:"var(--font-mono)"}}>
                      <a href="#" title={`Скачать (${r.downloads})`} style={{color:"var(--primary-600)",textDecoration:"none",fontWeight:600}}>⬇ {r.downloads}</a>
                    </td>
                    <td>{r.playOnline && <button className="zx-button zx-button--sm zx-button--secondary" title="Играть онлайн">▶</button>}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}

          {view==="cards" && (
            <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(260px, 1fr))", gap: 10 }}>
              {sorted.map(r => (
                <div key={r.id} className="va-rel-card">
                  <div className="va-rel-card__cover">
                    {r.screens.length ? <ZxScreen seed={r.id*13} palette={["sunset","cool","forest","night","default"][r.id%5]} /> : null}
                  </div>
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <div className="va-rel-card__title">{r.title}</div>
                    <div className="va-rel-card__meta">{r.releasedBy || "—"}{r.year ? " · " + r.year : ""}</div>
                    <div className="va-rel-card__chips">
                      <span className={"rel-type-pill rel-type-pill--" + r.type}>{RELEASE_TYPES[r.type].label}</span>
                      <span className="va-rel-card__chip va-rel-card__chip--lang">{r.lang==="ru"?"🇷🇺":"🇬🇧"}</span>
                      {r.format && <span className="va-rel-card__chip">{r.format}</span>}
                      {r.note && <span className="va-rel-card__chip va-rel-card__chip--cheats">★ {r.note}</span>}
                    </div>
                    <div className="va-rel-card__bottom">
                      {r.playOnline && <a className="play-link" href="#">▶ Играть</a>}
                      <span>⬇{r.downloads}</span>
                      <span>· ▶{r.plays}</span>
                      <span style={{ marginLeft: "auto", color: "var(--warning-500)" }}>★{r.votes}</span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {tab==="media" && (
        <div>
          <div className="vb-toggle" style={{ marginBottom: 12 }}>
            <button className={mediaSub==="articles"?"active":""} onClick={()=>setMediaSub("articles")}>📰 Статьи и карты <span style={{opacity:0.6}}>{MENTIONS.length + MAPS.length}</span></button>
            <button className={mediaSub==="covers"?"active":""} onClick={()=>setMediaSub("covers")}>📼 Обложки кассет</button>
            <button className={mediaSub==="music"?"active":""} onClick={()=>setMediaSub("music")}>🎵 Музыка <span style={{opacity:0.6}}>{PROD_TUNES.length}</span></button>
            <button className={mediaSub==="graphics"?"active":""} onClick={()=>setMediaSub("graphics")}>🎨 Графика</button>
          </div>

          {mediaSub==="articles" && (
            <div className="pp-card">
              <div className="pp-section-h" style={{ fontSize: "var(--font-md)" }}>Карты <span className="count">{MAPS.length}</span></div>
              <div className="va-mini">
                <span className="va-mini__icon">🗺</span>
                <div className="va-mini__body">
                  <div className="va-mini__title">Карта прохождения</div>
                  <div className="va-mini__sub">by {MAPS[0].author}</div>
                </div>
                <button className="zx-button zx-button--sm zx-button--outlined">Открыть</button>
              </div>
              <div className="pp-section-h" style={{ fontSize: "var(--font-md)", marginTop: 16 }}>Упоминания <span className="count">{MENTIONS.length}</span></div>
              {MENTIONS.map((m,i) => (
                <div key={i} className="va-mini">
                  <span className="va-mini__icon">📰</span>
                  <div className="va-mini__body">
                    <div className="va-mini__title">{m.mag} #{String(m.issue).padStart(2,"0")} ({m.year}) · {m.section}</div>
                    <div className="va-mini__sub">{m.body}</div>
                  </div>
                </div>
              ))}
            </div>
          )}

          {mediaSub==="covers" && (
            <div className="pp-card">
              <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(180px, 1fr))", gap: 12 }}>
                {[0,1,2,3,4,5].map(i => (
                  <div key={i} style={{ aspectRatio: "3/4", border: "1px solid var(--secondary-200)", borderRadius: "var(--radius-sm)", background: "var(--background-deep)", display:"flex", alignItems:"center", justifyContent:"center", color: "var(--text-light-color)", fontSize: "var(--font-xs)" }}>
                    📼 обложка {i+1}
                  </div>
                ))}
              </div>
            </div>
          )}

          {mediaSub==="music" && (
            <div className="pp-card">
              {PROD_TUNES.map(t => (
                <div key={t.id} style={{ display: "flex", alignItems: "center", gap: 12, padding: "8px 4px", borderBottom: "1px solid var(--secondary-200)" }}>
                  <span style={{ fontFamily: "var(--font-mono)", color: "var(--text-light-color)", width: 24, fontSize: "var(--font-sm)" }}>{t.idx}</span>
                  <button className="zx-button zx-button--sm zx-button--secondary zx-button--round">▶</button>
                  <div style={{ flex: 1 }}>
                    <div style={{ fontWeight: 700, fontSize: "var(--font-sm)" }}>{t.title}</div>
                    <div style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>{t.author} · {t.chip} · {t.year}</div>
                  </div>
                  <span style={{ fontFamily: "var(--font-mono)", fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>{t.duration}</span>
                  <span style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)", width: 60, textAlign: "right" }}>▶ {t.plays}</span>
                  <span style={{ color: "var(--warning-500)" }}>{"★".repeat(t.stars)}</span>
                </div>
              ))}
            </div>
          )}

          {mediaSub==="graphics" && (
            <div className="pp-card">
              <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(160px, 1fr))", gap: 8 }}>
                {SCREENS.slice(0,12).map(s => (
                  <div key={s.id} style={{ aspectRatio: "4/3", borderRadius: "var(--radius-sm)", overflow: "hidden", background: "var(--background-deep)" }}>
                    <ZxScreen seed={s.id} palette={s.palette} />
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      )}

      {tab==="links" && (
        <div>
          <div className="vb-toggle" style={{ marginBottom: 12 }}>
            <button className={linksSub==="series"?"active":""} onClick={()=>setLinksSub("series")}>🔗 Серия Dizzy <span style={{opacity:0.6}}>{SAME_SERIES.length}</span></button>
            <button className={linksSub==="compilations"?"active":""} onClick={()=>setLinksSub("compilations")}>📦 В сборниках <span style={{opacity:0.6}}>{COMPILATIONS.length}</span></button>
          </div>

          {linksSub==="series" && (
            <div className="pp-card">
              <div style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)", marginBottom: 10, fontStyle: "italic" }}>
                Все программы серии «Dizzy»
              </div>
              <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(220px, 1fr))", gap: 10 }}>
                {SAME_SERIES.map((s, i) => (
                  <div key={i} style={{ padding: 10, border: s.title===PROD.title?"2px solid var(--primary-500)":"1px solid var(--secondary-200)", borderRadius: "var(--radius-md)", background: s.title===PROD.title?"var(--primary-50)":"var(--surface)" }}>
                    <div style={{ aspectRatio: "4/3", background: "var(--background-deep)", borderRadius: "var(--radius-sm)", overflow: "hidden", marginBottom: 8 }}>
                      <ZxScreen seed={i*1000+7} palette={["sunset","cool","forest","night","default"][i%5]} />
                    </div>
                    <div style={{ fontWeight: 700, fontSize: "var(--font-sm)" }}>{s.title}</div>
                    <div style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>{s.by} · {s.year || "—"}</div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {linksSub==="compilations" && (
            <div className="pp-card">
              <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(280px, 1fr))", gap: 10 }}>
                {COMPILATIONS.map((c, i) => (
                  <div key={i} style={{ padding: 12, border: "1px solid var(--secondary-200)", borderRadius: "var(--radius-md)" }}>
                    <div style={{ fontWeight: 700 }}>{c.title}</div>
                    <div style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)", marginTop: 2 }}>
                      {c.by || "—"}{c.year ? " · " + c.year : ""}{c.count ? " · " + c.count + " программ" : ""}
                    </div>
                    {c.format && <div style={{ marginTop: 8 }}><span className="va-rel-card__chip">{c.format}</span></div>}
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      )}

      {tab==="discussion" && (
        <div className="pp-card">
          <div style={{ marginBottom: 12 }}>
            <textarea className="zx-input" style={{ height: 80, width: "100%", padding: 10 }} placeholder="Оставить отзыв или комментарий…" />
            <div style={{ display: "flex", justifyContent: "flex-end", marginTop: 8 }}>
              <button className="zx-button zx-button--sm zx-button--secondary">Отправить</button>
            </div>
          </div>
          <div className="pp-section-h" style={{ fontSize: "var(--font-md)" }}>История голосов <span className="count">{VOTES.length}</span></div>
          {VOTES.map((v, i) => (
            <div key={i} style={{ padding: "8px 0", borderBottom: "1px dashed var(--secondary-200)", display: "flex", gap: 12, fontSize: "var(--font-sm)" }}>
              <span style={{ fontFamily: "var(--font-mono)", color: "var(--text-light-color)", fontSize: "var(--font-xs)", width: 60 }}>{v.year}</span>
              <span style={{ flex: 1 }}><b>{v.user}</b> голосовал за <i>{v.target}</i></span>
              <span style={{ color: "var(--warning-500)" }}>{v.score ? "★".repeat(v.score) : "—"}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

window.ProdPage = ProdPage;
