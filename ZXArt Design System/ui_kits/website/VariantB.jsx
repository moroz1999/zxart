/* Variant B — "Collector-first"
   - Wikipedia-style sticky sidebar infobox (cover, key facts, expandable details)
   - Main column: short summary, screen mosaic, releases with table↔cards toggle + rich filter bar,
     accordions for secondary sections.
*/
const { useState } = React;

function VariantB() {
  const [view, setView] = useState("table");
  const [filterLang, setFilterLang] = useState("all");
  const [filterType, setFilterType] = useState("all");

  const filtered = RELEASES.filter(r =>
    (filterLang === "all" || r.lang === filterLang) &&
    (filterType === "all" || r.type === filterType)
  );

  return (
    <div style={{ padding: 24, maxWidth: 1280, margin: "0 auto", fontFamily: "var(--font-body)", color: "var(--text-color)", background: "var(--background-page)", minHeight: 1700 }}>
      <div style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)", marginBottom: 12, fontFamily: "var(--font-mono)" }}>
        Главная / Софт / Игры / Приключения / Квесты-головоломки / <span style={{ color: "var(--text-color)" }}>Crystal Kingdom Dizzy</span>
      </div>

      <h1 style={{ fontSize: "var(--font-xxl)", margin: "0 0 4px" }}>{PROD.title}</h1>
      <div style={{ color: "var(--text-light-color)", marginBottom: 16 }}>
        Приключенческая игра-головоломка · {PROD.year} · также известна как <i>{PROD.alsoKnownAs}</i>
      </div>

      <div className="vb-grid">
        {/* MAIN COLUMN */}
        <div>
          {/* summary strip with primary CTA */}
          <div className="vb-summary pp-card" style={{ padding: 12 }}>
            <div style={{ display: "flex", gap: 10, alignItems: "center" }}>
              <div style={{ display: "flex", alignItems: "baseline", gap: 4 }}>
                <span style={{ fontSize: "var(--font-xl)", fontWeight: 700, color: "var(--warning-700)" }}>{PROD.rating.score}</span>
                <span style={{ fontSize: "var(--font-sm)", color: "var(--text-light-color)" }}>/ 5</span>
              </div>
              <div style={{ color: "var(--warning-500)", fontSize: 16 }}>{"★".repeat(Math.round(PROD.rating.score))}</div>
              <span style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>· {PROD.rating.votes} голосов</span>
              <span style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>· 📷 {SCREENS.length}</span>
              <span style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>· 💿 {RELEASES.length}</span>
              <span style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>· 🎵 {PROD_TUNES.length}</span>
            </div>
            <div className="vb-summary__cta">
              <button className="zx-button zx-button--md zx-button--secondary">▶ Играть онлайн</button>
              <button className="zx-button zx-button--md zx-button--outlined">⬇ Скачать</button>
              <button className="zx-button zx-button--md zx-button--transparent">♥</button>
            </div>
          </div>

          {/* screens mosaic */}
          <div className="pp-card" style={{ marginBottom: 16, padding: 12 }}>
            <div className="pp-section-h" style={{ marginBottom: 10 }}>
              Скрины <span className="count">{SCREENS.length}</span>
              <span style={{ marginLeft: "auto", fontSize: "var(--font-xs)", fontWeight: 400 }}>
                <a href="#" style={{ color: "var(--primary-600)" }}>галерея ↗</a>
              </span>
            </div>
            <div className="vb-mosaic">
              {SCREENS.slice(0, 24).map(s => (
                <div key={s.id} className="vb-mosaic__cell">
                  <ZxScreen seed={s.id} palette={s.palette} />
                </div>
              ))}
            </div>
          </div>

          {/* About */}
          <div className="pp-card" style={{ marginBottom: 16 }}>
            <p style={{ margin: 0, lineHeight: 1.65 }}>{PROD.story}</p>
          </div>

          {/* Releases */}
          <div className="pp-section-h" style={{ marginTop: 24 }}>
            Релизы <span className="count">{RELEASES.length}</span>
            <span style={{ marginLeft: "auto" }}></span>
          </div>

          <div className="vb-filter-bar">
            <span className="vb-filter-bar__label">Язык:</span>
            <div className="vb-filter-bar__group">
              {[["all","все"],["en","🇬🇧 EN"],["ru","🇷🇺 RU"]].map(([k,l]) => (
                <button key={k} onClick={()=>setFilterLang(k)}
                  className={"zx-button zx-button--sm " + (filterLang === k ? "zx-button--secondary" : "zx-button--outlined")}>{l}</button>
              ))}
            </div>
            <div className="vb-filter-bar__sep"></div>
            <span className="vb-filter-bar__label">Тип:</span>
            <div className="vb-filter-bar__group">
              {[["all","все"], ...Object.entries(RELEASE_TYPES).map(([k,v])=>[k,v.label])].map(([k,l]) => (
                <button key={k} onClick={()=>setFilterType(k)}
                  className={"zx-button zx-button--sm " + (filterType === k ? "zx-button--secondary" : "zx-button--outlined")}>{l}</button>
              ))}
            </div>
            <div className="vb-toggle">
              <button className={view === "table" ? "active" : ""} onClick={()=>setView("table")}>☰ таблица</button>
              <button className={view === "cards" ? "active" : ""} onClick={()=>setView("cards")}>▦ карточки</button>
            </div>
          </div>

          {view === "table" && (
            <table className="vb-rel-table">
              <thead>
                <tr>
                  <th></th>
                  <th>Название · автор</th>
                  <th>Год</th>
                  <th>Тип</th>
                  <th>Яз.</th>
                  <th>Платформа / формат</th>
                  <th style={{textAlign:"right"}}>★</th>
                  <th style={{textAlign:"right"}}>⬇</th>
                  <th style={{textAlign:"right"}}>▶</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {filtered.map(r => (
                  <tr key={r.id} className={r.id === 1 ? "recommended-row" : ""}>
                    <td><div className="vb-rel-table__shot">{r.screens.length ? <ZxScreen seed={r.id*13} palette={["sunset","cool","forest","night","default"][r.id%5]} /> : null}</div></td>
                    <td>
                      <div className="vb-rel-table__title">{r.id === 1 && <span style={{color:"var(--warning-500)",marginRight:4}}>★</span>}{r.title}</div>
                      <div className="vb-rel-table__by">{r.releasedBy || "—"}{r.note ? " · " + r.note : ""}</div>
                    </td>
                    <td style={{fontFamily:"var(--font-mono)"}}>{r.year || "—"}</td>
                    <td><span className={"rel-type-pill rel-type-pill--" + r.type}>{RELEASE_TYPES[r.type].label}</span></td>
                    <td>{r.lang === "ru" ? "🇷🇺" : "🇬🇧"}</td>
                    <td style={{fontSize:"var(--font-xs)"}}>
                      <div className="tag-row">
                        {r.format && <span className="tag-glyph" title={r.format}>{r.format.includes("SCL") ? "💾" : "📼"}</span>}
                        {r.hardware.slice(0,3).map(h => <span key={h} className="tag-glyph" title={h}>{h.includes("AY") ? "🔊" : h.includes("джойстик") ? "🕹" : "🖥"}</span>)}
                      </div>
                    </td>
                    <td style={{textAlign:"right",fontFamily:"var(--font-mono)"}}>{r.votes || "—"}</td>
                    <td style={{textAlign:"right",fontFamily:"var(--font-mono)",color:"var(--text-light-color)"}}>{r.downloads}</td>
                    <td style={{textAlign:"right",fontFamily:"var(--font-mono)",color:"var(--text-light-color)"}}>{r.plays}</td>
                    <td>{r.playOnline && <button className="zx-button zx-button--sm zx-button--secondary">▶</button>}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}

          {view === "cards" && (
            <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(260px, 1fr))", gap: 10 }}>
              {filtered.map(r => (
                <div key={r.id} className="va-rel-card" style={{ borderColor: r.id === 1 ? "var(--primary-400)" : "var(--secondary-200)" }}>
                  <div className="va-rel-card__cover">
                    {r.screens.length ? <ZxScreen seed={r.id*13} palette={["sunset","cool","forest","night","default"][r.id%5]} /> : null}
                  </div>
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <div className="va-rel-card__title">{r.title}</div>
                    <div className="va-rel-card__meta">{r.releasedBy || "—"}{r.year ? " · " + r.year : ""}</div>
                    <div className="va-rel-card__chips">
                      <span className={"rel-type-pill rel-type-pill--" + r.type}>{RELEASE_TYPES[r.type].label}</span>
                      <span className="va-rel-card__chip va-rel-card__chip--lang">{r.lang === "ru" ? "🇷🇺" : "🇬🇧"}</span>
                      {r.format && <span className="va-rel-card__chip">{r.format}</span>}
                    </div>
                    <div className="va-rel-card__bottom">
                      {r.playOnline && <a className="play-link" href="#">▶</a>}
                      <span>⬇{r.downloads}</span>
                      <span style={{ marginLeft: "auto" }}>★{r.votes}</span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}

          {/* secondary as accordions */}
          <div style={{ marginTop: 24 }}>
            <details className="vb-acc" open>
              <summary>🎵 Музыка программы <span className="count">{PROD_TUNES.length}</span> <svg className="chev" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg></summary>
              <div className="vb-acc__body">
                {PROD_TUNES.map(t => (
                  <div key={t.id} style={{ display: "flex", alignItems: "center", gap: 10, padding: "6px 0", borderBottom: "1px solid var(--secondary-200)", fontSize: "var(--font-sm)" }}>
                    <button className="zx-button zx-button--sm zx-button--secondary zx-button--round">▶</button>
                    <span style={{ fontWeight: 700 }}>{t.title}</span>
                    <span style={{ color: "var(--text-light-color)" }}>{t.author}</span>
                    <span style={{ marginLeft: "auto", color: "var(--warning-500)" }}>{"★".repeat(t.stars)}</span>
                    <span style={{ fontFamily: "var(--font-mono)", fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>{t.duration}</span>
                  </div>
                ))}
              </div>
            </details>

            <details className="vb-acc">
              <summary>📰 Упоминания и карты <span className="count">{MENTIONS.length + MAPS.length}</span> <svg className="chev" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg></summary>
              <div className="vb-acc__body">
                {MENTIONS.map((m,i)=>(
                  <div key={i} className="va-mini">
                    <span className="va-mini__icon">📰</span>
                    <div className="va-mini__body">
                      <div className="va-mini__title">{m.mag} #{String(m.issue).padStart(2,"0")} ({m.year}) · {m.section}</div>
                      <div className="va-mini__sub">{m.body}</div>
                    </div>
                  </div>
                ))}
                <div className="va-mini">
                  <span className="va-mini__icon">🗺</span>
                  <div className="va-mini__body">
                    <div className="va-mini__title">Карта прохождения</div>
                    <div className="va-mini__sub">by {MAPS[0].author}</div>
                  </div>
                </div>
              </div>
            </details>

            <details className="vb-acc">
              <summary>📦 В сборниках <span className="count">{COMPILATIONS.length}</span> <svg className="chev" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg></summary>
              <div className="vb-acc__body">
                {COMPILATIONS.map((c,i)=>(
                  <div key={i} style={{ padding: "6px 0", borderBottom: "1px dashed var(--secondary-200)", fontSize: "var(--font-sm)" }}>
                    <b>{c.title}</b> <span style={{ color: "var(--text-light-color)" }}>· {c.by || "—"}{c.year ? " · " + c.year : ""}</span>
                  </div>
                ))}
              </div>
            </details>

            <details className="vb-acc">
              <summary>🎮 Серия Dizzy <span className="count">{SAME_SERIES.length}</span> <svg className="chev" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg></summary>
              <div className="vb-acc__body">
                <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(180px, 1fr))", gap: 10 }}>
                  {SAME_SERIES.map((s,i)=>(
                    <div key={i} style={{ fontSize: "var(--font-xs)" }}>
                      <div style={{ aspectRatio: "4/3", background: "var(--background-deep)", borderRadius: 4, overflow: "hidden", marginBottom: 4 }}>
                        <ZxScreen seed={i*1000+7} palette={["sunset","cool","forest","night","default"][i%5]} />
                      </div>
                      <div style={{ fontWeight: 700 }}>{s.title}</div>
                      <div style={{ color: "var(--text-light-color)" }}>{s.year || "—"}</div>
                    </div>
                  ))}
                </div>
              </div>
            </details>

            <details className="vb-acc">
              <summary>💬 Обсуждения и история голосов <span className="count">{VOTES.length}</span> <svg className="chev" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg></summary>
              <div className="vb-acc__body">
                {VOTES.map((v,i)=>(
                  <div key={i} style={{ padding: "4px 0", fontSize: "var(--font-sm)", display: "flex", gap: 10 }}>
                    <span style={{ fontFamily: "var(--font-mono)", color: "var(--text-light-color)", width: 50 }}>{v.year}</span>
                    <span style={{ flex: 1 }}><b>{v.user}</b> → <i>{v.target}</i></span>
                    <span style={{ color: "var(--warning-500)" }}>{v.score ? "★".repeat(v.score) : "—"}</span>
                  </div>
                ))}
              </div>
            </details>
          </div>
        </div>

        {/* SIDEBAR INFOBOX */}
        <aside className="vb-infobox">
          <div className="vb-infobox__cover"><ZxScreen seed={42} palette="forest" /></div>
          <div className="vb-infobox__title-strip">
            <h3 className="vb-infobox__title">{PROD.title}</h3>
            <div className="vb-infobox__alias">aka {PROD.alsoKnownAs}</div>
          </div>
          <div className="vb-infobox__rating-row">
            <span className="big">{PROD.rating.score}</span>
            <span style={{ color: "var(--warning-500)" }}>{"★".repeat(Math.round(PROD.rating.score))}</span>
            <span style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)", marginLeft: "auto" }}>{PROD.rating.votes} votes</span>
          </div>
          <dl>
            <div><dt>Год</dt><dd style={{ fontFamily: "var(--font-mono)" }}>{PROD.year}</dd></div>
            <div><dt>Категория</dt><dd>{PROD.category.join(" / ")}</dd></div>
            <div><dt>Язык</dt><dd>🇬🇧 English</dd></div>
            <div><dt>Авторы</dt><dd>{PROD.authors.join(", ")}</dd></div>
            <div><dt>Музыка</dt><dd>{PROD.music}</dd></div>
            <div><dt>Издатель</dt><dd>{PROD.publisher}</dd></div>
            <div><dt>Разработчик</dt><dd>{PROD.developer}</dd></div>
            <div><dt>Серия</dt><dd>Dizzy ({PROD.series.count})</dd></div>
            <div><dt>Статус</dt><dd style={{ color: "var(--danger-500)", fontSize: "var(--font-xs)" }}>⚠ {PROD.status}</dd></div>
          </dl>
          <details className="vb-infobox__details">
            <summary>Внешние ссылки ({PROD.links.length}) ▾</summary>
            <div style={{ marginTop: 6, display: "grid", gap: 4 }}>
              {PROD.links.map((l,i)=>(
                <a key={i} href="#" style={{ fontSize: "var(--font-xs)", color: "var(--primary-600)", textDecoration: "none" }}>↗ {l.label}</a>
              ))}
            </div>
          </details>
          <details className="vb-infobox__details">
            <summary>Теги ({PROD.tags.length}) ▾</summary>
            <div style={{ marginTop: 6, display: "flex", gap: 4, flexWrap: "wrap" }}>
              {PROD.tags.map(t=>(
                <span key={t} style={{ fontSize: 10, padding: "1px 6px", background: "var(--secondary-100)", borderRadius: 999, color: "var(--text-light-color)" }}>{t}</span>
              ))}
            </div>
          </details>
        </aside>
      </div>
    </div>
  );
}

window.VariantB = VariantB;
