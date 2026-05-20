/* ProdPageMobile.jsx — mobile (≤480px) variant of the prod details page.
   STRUCTURE MIRRORS DESKTOP 1:1. Same blocks, same order, same 4 tabs,
   same filter bar, same sub-tabs. Mobile only changes:
     • Hero stacks (cover above text) instead of 360-col grid.
     • Screens strip stays 1-big + small (smaller grid).
     • Releases renders as cards (the same .va-rel-card the desktop already
       has under "cards view") — no table on mobile.
     • Chips / tabs / filter / tags scroll horizontally instead of wrapping.
     • Type-scale shrinks one notch.
   This keeps the same data flow, same component tree, same CSS class names —
   so porting tweaks between desktop and mobile is mechanical, not structural.
*/
const { useState: useStateM } = React;

function ProdPageMobile() {
  const [tab, setTab] = useStateM("releases");
  const [mediaSub, setMediaSub] = useStateM("articles");
  const [linksSub, setLinksSub] = useStateM("series");
  const [filterLang, setFilterLang] = useStateM("all");
  const [filterType, setFilterType] = useStateM("all");
  const [sortBy, setSortBy] = useStateM("votes");
  const [sortDir, setSortDir] = useStateM("desc");

  const filtered = RELEASES.filter(r =>
    (filterLang === "all" || r.lang === filterLang) &&
    (filterType === "all" || r.type === filterType)
  );
  const sorted = [...filtered].sort((a,b) => {
    const va = a[sortBy] ?? 0, vb = b[sortBy] ?? 0;
    return sortDir === "desc" ? (vb - va) : (va - vb);
  });

  return (
    <div className="mob" style={{ padding: "12px 12px 24px", maxWidth: 480, margin: "0 auto" }}>
      {/* === Breadcrumb (same as desktop) === */}
      <div style={{ fontSize: 10, color: "var(--text-light-color)", marginBottom: 10, fontFamily: "var(--font-mono)", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
        Главная / Софт / Игры / … / <span style={{ color: "var(--text-color)" }}>Crystal Kingdom Dizzy</span>
      </div>

      {/* === HERO — same order as desktop, stacked === */}
      <div className="mob-hero-block">
        <div className="va-hero__cover mob-hero__cover">
          <ZxScreen seed={42} palette="forest" />
          <div className="va-hero__shots">📷 {SCREENS.length} скринов</div>
        </div>
        <div className="mob-hero__info">
          <div className="va-hero__title-row">
            <h1 className="va-hero__title">{PROD.title}</h1>
            <span className="va-hero__year">· {PROD.year}</span>
          </div>
          <div className="va-hero__alias">также известна как <i>{PROD.alsoKnownAs}</i></div>

          <div className="va-hero__chips mob-chips-scroll">
            {PROD.category.map(c => <span key={c} className="chip chip--cat">{c}</span>)}
            <span className="chip">🇬🇧 English</span>
            <span className="chip" title={PROD.status}>⚠ Распространение запрещено</span>
          </div>

          <div className="va-hero__rating-row">
            <div className="va-hero__rating">
              <span className="num">{PROD.rating.score}</span>
              <span className="of">/ {PROD.rating.ofFive}</span>
            </div>
            <VoteWidget myVote={4} fav={false} />
            <span style={{ fontSize: 11, color: "var(--text-light-color)" }}>
              {PROD.rating.votes} голосов · в избранном у 18
            </span>
          </div>

          <div className="va-hero__people">
            <b>Авторы:</b> {PROD.authors.join(", ")} · <b>Музыка:</b> {PROD.music} · <b>Издатель:</b> {PROD.publisher} · <b>Разработчик:</b> {PROD.developer} · <span style={{color:"var(--text-light-color)"}}>Добавлена {PROD.added}</span>
          </div>

          <div style={{ marginTop: 10, fontSize: 11, display: "flex", gap: 10, flexWrap: "wrap" }}>
            {PROD.links.map((l,i) => (
              <a key={i} href="#" style={{ color: "var(--primary-600)", textDecoration: "none" }}>↗ {l.label}</a>
            ))}
          </div>

          <div className="mob-hero__cta">
            <button className="zx-button zx-button--secondary" style={{ flex: 1 }}>▶ Играть онлайн</button>
            <button className="zx-button zx-button--outlined" style={{ flex: 1 }}>⬇ Скачать</button>
          </div>
        </div>
      </div>

      {/* === SCREENS strip (same big + small layout as desktop) === */}
      <div className="pp-card" style={{ marginBottom: 12, padding: 10 }}>
        <div className="pp-section-h" style={{ marginBottom: 8, fontSize: "var(--font-md)" }}>
          Скрины <span className="count">{SCREENS.length}</span>
        </div>
        <div className="va-screens mob-screens-grid">
          <div className="va-screens__cell va-screens__cell--big">
            <ZxScreen seed={SCREENS[0].id} palette={SCREENS[0].palette} />
          </div>
          {SCREENS.slice(1, 5).map(s => (
            <div key={s.id} className="va-screens__cell"><ZxScreen seed={s.id} palette={s.palette} /></div>
          ))}
          <a href="#" className="va-screens__cell va-screens__more" style={{ fontSize: 11, textAlign: "center", padding: 4, lineHeight: 1.2 }}>
            ещё<br/><span style={{fontSize:10,fontWeight:400,opacity:0.7}}>+{SCREENS.length - 5}</span>
          </a>
        </div>
      </div>

      {/* === STORY + tags === */}
      <div className="pp-card" style={{ marginBottom: 12 }}>
        <div className="pp-section-h" style={{ fontSize: "var(--font-md)" }}>О программе</div>
        <p style={{ margin: 0, lineHeight: 1.55, fontSize: "var(--font-sm)" }}>{PROD.story}</p>
        <div style={{ marginTop: 12, paddingTop: 10, borderTop: "1px dashed var(--secondary-200)", display: "flex", gap: 4, flexWrap: "wrap", alignItems: "center" }}>
          <span style={{ fontSize: 10, color: "var(--text-light-color)", textTransform: "uppercase", letterSpacing: "0.04em", marginRight: 4 }}>Теги:</span>
          {PROD.tags.map(t => (
            <a key={t} href="#" style={{ fontSize: 11, padding: "2px 7px", background: "var(--secondary-100)", border: "1px solid var(--secondary-200)", borderRadius: 999, color: "var(--text-light-color)", textDecoration: "none" }}>{t}</a>
          ))}
        </div>
      </div>

      {/* === TABS — same 4 as desktop === */}
      <div className="va-tabs mob-tabs-scroll">
        <button className={tab==="releases"?"active":""} onClick={()=>setTab("releases")}>Релизы <span className="num">{RELEASES.length}</span></button>
        <button className={tab==="media"?"active":""} onClick={()=>setTab("media")}>Медиа <span className="num">{MENTIONS.length + MAPS.length + PROD_TUNES.length}</span></button>
        <button className={tab==="links"?"active":""} onClick={()=>setTab("links")}>Связи <span className="num">{COMPILATIONS.length + SAME_SERIES.length}</span></button>
        <button className={tab==="discussion"?"active":""} onClick={()=>setTab("discussion")}>Обсуждение <span className="num">{VOTES.length}</span></button>
      </div>

      {tab==="releases" && (
        <div>
          {/* same filter bar as desktop, h-scrolling */}
          <div className="vb-filter-bar mob-filter-bar">
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
          </div>

          <div style={{ fontSize: 11, color: "var(--text-light-color)", margin: "4px 0 8px", fontStyle: "italic" }}>
            Лучший релиз — тот, у которого выше рейтинг сообщества.
          </div>

          {/* Cards view — desktop already supports this exact layout; on mobile it's the only view */}
          <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
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
        </div>
      )}

      {tab==="media" && (
        <div>
          <div className="vb-toggle mob-tabs-scroll" style={{ marginBottom: 10, marginLeft: 0 }}>
            <button className={mediaSub==="articles"?"active":""} onClick={()=>setMediaSub("articles")}>📰 Статьи и карты <span style={{opacity:0.6}}>{MENTIONS.length + MAPS.length}</span></button>
            <button className={mediaSub==="covers"?"active":""} onClick={()=>setMediaSub("covers")}>📼 Обложки</button>
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
              <div className="pp-section-h" style={{ fontSize: "var(--font-md)", marginTop: 14 }}>Упоминания <span className="count">{MENTIONS.length}</span></div>
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
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 8 }}>
                {[0,1,2,3,4,5].map(i => (
                  <div key={i} style={{ aspectRatio: "3/4", border: "1px solid var(--secondary-200)", borderRadius: "var(--radius-sm)", background: "var(--background-deep)", display:"flex", alignItems:"center", justifyContent:"center", color: "var(--text-light-color)", fontSize: 11 }}>
                    📼 обложка {i+1}
                  </div>
                ))}
              </div>
            </div>
          )}

          {mediaSub==="music" && (
            <div className="pp-card" style={{ padding: 8 }}>
              {PROD_TUNES.map(t => (
                <div key={t.id} style={{ display: "flex", alignItems: "center", gap: 8, padding: "8px 4px", borderBottom: "1px solid var(--secondary-200)" }}>
                  <span style={{ fontFamily: "var(--font-mono)", color: "var(--text-light-color)", width: 18, fontSize: 11 }}>{t.idx}</span>
                  <button className="zx-button zx-button--sm zx-button--secondary zx-button--round" style={{ flexShrink: 0 }}>▶</button>
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{ fontWeight: 700, fontSize: "var(--font-sm)", whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }}>{t.title}</div>
                    <div style={{ fontSize: 10, color: "var(--text-light-color)" }}>{t.author} · {t.chip} · {t.duration}</div>
                  </div>
                  <span style={{ color: "var(--warning-500)", fontSize: 11 }}>{"★".repeat(t.stars)}</span>
                </div>
              ))}
            </div>
          )}

          {mediaSub==="graphics" && (
            <div className="pp-card">
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 6 }}>
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
          <div className="vb-toggle mob-tabs-scroll" style={{ marginBottom: 10, marginLeft: 0 }}>
            <button className={linksSub==="series"?"active":""} onClick={()=>setLinksSub("series")}>🔗 Серия Dizzy <span style={{opacity:0.6}}>{SAME_SERIES.length}</span></button>
            <button className={linksSub==="compilations"?"active":""} onClick={()=>setLinksSub("compilations")}>📦 В сборниках <span style={{opacity:0.6}}>{COMPILATIONS.length}</span></button>
          </div>

          {linksSub==="series" && (
            <div className="pp-card">
              <div style={{ fontSize: 11, color: "var(--text-light-color)", marginBottom: 10, fontStyle: "italic" }}>
                Все программы серии «Dizzy»
              </div>
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 8 }}>
                {SAME_SERIES.map((s, i) => (
                  <div key={i} style={{ padding: 8, border: s.title===PROD.title?"2px solid var(--primary-500)":"1px solid var(--secondary-200)", borderRadius: "var(--radius-md)", background: s.title===PROD.title?"var(--primary-50)":"var(--surface)" }}>
                    <div style={{ aspectRatio: "4/3", background: "var(--background-deep)", borderRadius: "var(--radius-sm)", overflow: "hidden", marginBottom: 6 }}>
                      <ZxScreen seed={i*1000+7} palette={["sunset","cool","forest","night","default"][i%5]} />
                    </div>
                    <div style={{ fontWeight: 700, fontSize: 12, lineHeight: 1.25 }}>{s.title}</div>
                    <div style={{ fontSize: 10, color: "var(--text-light-color)", fontFamily: "var(--font-mono)", marginTop: 2 }}>{s.year || "—"}</div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {linksSub==="compilations" && (
            <div className="pp-card">
              <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
                {COMPILATIONS.map((c, i) => (
                  <div key={i} style={{ padding: 10, border: "1px solid var(--secondary-200)", borderRadius: "var(--radius-md)" }}>
                    <div style={{ fontWeight: 700, fontSize: "var(--font-sm)" }}>{c.title}</div>
                    <div style={{ fontSize: 11, color: "var(--text-light-color)", marginTop: 2 }}>
                      {c.by || "—"}{c.year ? " · " + c.year : ""}{c.count ? " · " + c.count + " программ" : ""}
                    </div>
                    {c.format && <div style={{ marginTop: 6 }}><span className="va-rel-card__chip">{c.format}</span></div>}
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
            <textarea className="zx-input" style={{ height: 72, width: "100%", padding: 10, boxSizing: "border-box", fontSize: "var(--font-sm)" }} placeholder="Оставить отзыв или комментарий…" />
            <div style={{ display: "flex", justifyContent: "flex-end", marginTop: 8 }}>
              <button className="zx-button zx-button--sm zx-button--secondary">Отправить</button>
            </div>
          </div>
          <div className="pp-section-h" style={{ fontSize: "var(--font-md)" }}>История голосов <span className="count">{VOTES.length}</span></div>
          {VOTES.map((v, i) => (
            <div key={i} style={{ padding: "8px 0", borderBottom: "1px dashed var(--secondary-200)", fontSize: "var(--font-sm)" }}>
              <div style={{ display: "flex", gap: 8, alignItems: "baseline" }}>
                <b>{v.user}</b>
                <span style={{ marginLeft: "auto", color: "var(--warning-500)" }}>{v.score ? "★".repeat(v.score) : "—"}</span>
              </div>
              <div style={{ fontSize: 10, color: "var(--text-light-color)", fontFamily: "var(--font-mono)" }}>
                {v.year} · {v.target}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

window.ProdPageMobile = ProdPageMobile;
