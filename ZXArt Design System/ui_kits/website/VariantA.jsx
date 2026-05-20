/* Variant A — "Player-first"
   - Big hero with cover, alias, primary cat chips, rating, key people inline
   - Recommended release strip with one prominent Play button
   - Big screens mosaic above the fold (mosaic w/ 1 hero cell + 11 thumbs + "+X")
   - Tabs for: Releases / Music / Articles / Compilations / Series / Comments
   - Releases pane: filter bar + groups by type, expandable groups, card grid
*/
const { useState } = React;

function VariantA() {
  const [tab, setTab] = useState("releases");
  const [groupOpen, setGroupOpen] = useState({ original: true, adaptation: false, translation: false, modification: false, crack: false, unknown: false });

  // group releases
  const groups = {};
  RELEASES.forEach(r => { (groups[r.type] = groups[r.type] || []).push(r); });
  const groupOrder = ["original","modification","adaptation","translation","crack","unknown"];

  // recommended = original 1992
  const recommended = RELEASES[0];

  return (
    <div style={{ padding: 24, maxWidth: 1280, margin: "0 auto", fontFamily: "var(--font-sans)", color: "var(--text-color)", background: "var(--background-page)", minHeight: 1700 }}>
      {/* breadcrumb */}
      <div style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)", marginBottom: 12, fontFamily: "var(--font-mono)" }}>
        Главная / Софт / Игры / Приключения / Квесты-головоломки / <span style={{ color: "var(--text-color)" }}>Crystal Kingdom Dizzy</span>
      </div>

      {/* HERO */}
      <div className="va-hero">
        <div className="va-hero__cover">
          <ZxScreen seed={42} palette="forest" />
          <div className="va-hero__shots">📷 46 скринов</div>
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
            <div style={{ display: "flex", gap: 4 }}>
              {[1,2,3,4,5].map(s => (
                <span key={s} style={{ color: s <= Math.round(PROD.rating.score) ? "var(--warning-500)" : "var(--secondary-300)", fontSize: 18 }}>★</span>
              ))}
            </div>
            <button className="zx-button zx-button--sm zx-button--transparent">Голосовать</button>
          </div>

          <div className="va-hero__people">
            <b>Авторы:</b> {PROD.authors.join(", ")} · <b>Музыка:</b> {PROD.music} · <b>Издатель:</b> {PROD.publisher} · <b>Разработчик:</b> {PROD.developer}
          </div>

          {/* recommended release */}
          <div className="va-recommended">
            <span className="va-recommended__badge">★ рекомендованный</span>
            <div>
              <div className="va-recommended__title">{recommended.title}</div>
              <div className="va-recommended__meta">
                Оригинал · {recommended.year} · {recommended.releasedBy} · {recommended.format} · 🇬🇧
              </div>
            </div>
            <div className="va-recommended__cta">
              <button className="zx-button zx-button--md zx-button--secondary">▶ Играть онлайн</button>
              <button className="zx-button zx-button--md zx-button--outlined">⬇ Скачать</button>
            </div>
          </div>

          <div className="va-hero__cta-row" style={{ marginTop: 8 }}>
            <button className="zx-button zx-button--sm zx-button--transparent">♥ В избранное</button>
            <button className="zx-button zx-button--sm zx-button--transparent">+ В подборку</button>
            <button className="zx-button zx-button--sm zx-button--transparent">📤 Поделиться</button>
            <span style={{ marginLeft: "auto", fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>Добавлена {PROD.added}</span>
          </div>
        </div>
      </div>

      {/* SCREENS strip — visible above the fold, no "view all" gate */}
      <div className="pp-card" style={{ marginBottom: 16, padding: 12 }}>
        <div className="pp-section-h" style={{ marginBottom: 10 }}>
          Скрины <span className="count">{SCREENS.length}</span>
          <span style={{ marginLeft: "auto", fontSize: "var(--font-xs)", color: "var(--text-light-color)", fontWeight: 400 }}>
            <a href="#" style={{ color: "var(--primary-600)" }}>галерея ↗</a>
          </span>
        </div>
        <div className="va-screens">
          <div className="va-screens__cell va-screens__cell--big">
            <ZxScreen seed={SCREENS[0].id} palette={SCREENS[0].palette} />
          </div>
          {SCREENS.slice(1, 17).map(s => (
            <div key={s.id} className="va-screens__cell">
              <ZxScreen seed={s.id} palette={s.palette} />
            </div>
          ))}
          <div className="va-screens__cell va-screens__more">+{SCREENS.length - 17}</div>
        </div>
      </div>

      {/* STORY */}
      <div className="pp-card" style={{ marginBottom: 16 }}>
        <div className="pp-section-h">О программе</div>
        <p style={{ margin: 0, lineHeight: 1.65, fontSize: "var(--font-md)" }}>{PROD.story}</p>
        <div style={{ marginTop: 12, display: "flex", gap: 6, flexWrap: "wrap" }}>
          {PROD.tags.map(t => (
            <span key={t} style={{ fontSize: "var(--font-xs)", padding: "2px 8px", background: "var(--secondary-100)", border: "1px solid var(--secondary-200)", borderRadius: 999, color: "var(--text-light-color)" }}>{t}</span>
          ))}
        </div>
      </div>

      {/* TABS */}
      <div className="va-tabs">
        <button className={tab === "releases" ? "active" : ""} onClick={()=>setTab("releases")}>
          Релизы <span className="num">{RELEASES.length}</span>
        </button>
        <button className={tab === "music" ? "active" : ""} onClick={()=>setTab("music")}>
          Музыка <span className="num">{PROD_TUNES.length}</span>
        </button>
        <button className={tab === "articles" ? "active" : ""} onClick={()=>setTab("articles")}>
          Статьи и карты <span className="num">{MENTIONS.length + MAPS.length}</span>
        </button>
        <button className={tab === "compilations" ? "active" : ""} onClick={()=>setTab("compilations")}>
          В сборниках <span className="num">{COMPILATIONS.length}</span>
        </button>
        <button className={tab === "series" ? "active" : ""} onClick={()=>setTab("series")}>
          Серия Dizzy <span className="num">{SAME_SERIES.length}</span>
        </button>
        <button className={tab === "comments" ? "active" : ""} onClick={()=>setTab("comments")}>Обсуждения</button>
      </div>

      {/* TAB: Releases — grouped, with filter bar */}
      {tab === "releases" && (
        <div>
          <div className="vb-filter-bar" style={{ marginBottom: 12 }}>
            <span className="vb-filter-bar__label">Фильтр:</span>
            <div className="vb-filter-bar__group">
              <button className="zx-button zx-button--sm zx-button--outlined">все языки ▾</button>
              <button className="zx-button zx-button--sm zx-button--outlined">все платформы ▾</button>
              <button className="zx-button zx-button--sm zx-button--outlined">все форматы ▾</button>
            </div>
            <div className="vb-filter-bar__sep"></div>
            <span className="vb-filter-bar__label">Сорт:</span>
            <button className="zx-button zx-button--sm zx-button--outlined">по году ↓</button>
          </div>

          {groupOrder.map(t => {
            const list = groups[t]; if (!list) return null;
            const open = groupOpen[t];
            return (
              <div key={t} className={"va-rel-group" + (open ? " va-rel-group--open" : "")}>
                <div className="va-rel-group__head" onClick={()=>setGroupOpen({...groupOpen, [t]: !open})}>
                  <span className={"rel-type-pill rel-type-pill--" + t}>{RELEASE_TYPES[t].label}</span>
                  <span className="va-rel-group__title">{list.length === 1 ? "1 релиз" : list.length + " релизов"}</span>
                  <span className="va-rel-group__count">{list.map(r => r.year).filter(Boolean).filter((v,i,a)=>a.indexOf(v)===i).join(" · ")}</span>
                  <svg className="va-rel-group__chev" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg>
                </div>
                {open && (
                  <div className="va-rel-group__body">
                    {list.map(r => (
                      <div key={r.id} className="va-rel-card">
                        <div className="va-rel-card__cover">
                          {r.screens.length ? <ZxScreen seed={r.id * 13} palette={["sunset","cool","forest","night","default"][r.id % 5]} /> : <div style={{height:"100%",display:"flex",alignItems:"center",justifyContent:"center",fontSize:11,color:"var(--secondary-400)"}}>—</div>}
                        </div>
                        <div style={{ flex: 1, minWidth: 0 }}>
                          <div className="va-rel-card__title">{r.title}</div>
                          <div className="va-rel-card__meta">
                            {r.releasedBy || "автор неизвестен"}{r.year ? " · " + r.year : ""}
                          </div>
                          <div className="va-rel-card__chips">
                            <span className="va-rel-card__chip va-rel-card__chip--lang">{r.lang === "ru" ? "🇷🇺" : "🇬🇧"} {r.lang.toUpperCase()}</span>
                            {r.format && <span className="va-rel-card__chip">{r.format.includes("SCL") ? "💾" : "📼"} {r.format}</span>}
                            {r.hardware.map(h => <span key={h} className="va-rel-card__chip">{h.includes("AY") ? "🔊 " : ""}{h}</span>)}
                            {r.note && <span className="va-rel-card__chip va-rel-card__chip--cheats">★ {r.note}</span>}
                          </div>
                          <div className="va-rel-card__bottom">
                            {r.playOnline && <a className="play-link" href="#">▶ Играть</a>}
                            <span>⬇ {r.downloads}</span>
                            <span>· ▶ {r.plays}</span>
                            <span style={{ marginLeft: "auto" }}>★ {r.votes}</span>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}

      {tab === "music" && (
        <div className="pp-card">
          <div style={{ display: "grid", gap: 6 }}>
            {PROD_TUNES.map(t => (
              <div key={t.id} style={{ display: "flex", alignItems: "center", gap: 12, padding: "8px 4px", borderBottom: "1px solid var(--secondary-200)" }}>
                <span style={{ fontFamily: "var(--font-mono)", color: "var(--text-light-color)", width: 24, fontSize: "var(--font-sm)" }}>{t.idx}</span>
                <button className="zx-button zx-button--sm zx-button--secondary zx-button--round" aria-label="Play">▶</button>
                <div style={{ flex: 1 }}>
                  <div style={{ fontWeight: 700, fontSize: "var(--font-sm)" }}>{t.title}</div>
                  <div style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>{t.author} · {t.chip} · {t.year}</div>
                </div>
                <span style={{ fontFamily: "var(--font-mono)", fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>{t.duration}</span>
                <span style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)", width: 60, textAlign: "right" }}>▶ {t.plays}</span>
                <span style={{ color: "var(--warning-500)", fontSize: 14 }}>{"★".repeat(t.stars)}</span>
              </div>
            ))}
          </div>
        </div>
      )}

      {tab === "articles" && (
        <div className="pp-card">
          <div style={{ marginBottom: 16 }}>
            <div className="pp-section-h" style={{ fontSize: "var(--font-md)" }}>Карты <span className="count">{MAPS.length}</span></div>
            <div className="va-mini">
              <span className="va-mini__icon">🗺</span>
              <div className="va-mini__body">
                <div className="va-mini__title">Карта прохождения</div>
                <div className="va-mini__sub">by {MAPS[0].author}</div>
              </div>
              <button className="zx-button zx-button--sm zx-button--outlined">Открыть</button>
            </div>
          </div>
          <div>
            <div className="pp-section-h" style={{ fontSize: "var(--font-md)" }}>Упоминания в статьях <span className="count">{MENTIONS.length}</span></div>
            {MENTIONS.map((m, i) => (
              <div key={i} className="va-mini">
                <span className="va-mini__icon">📰</span>
                <div className="va-mini__body">
                  <div className="va-mini__title">{m.mag} #{String(m.issue).padStart(2,"0")} ({m.year}) · {m.section}</div>
                  <div className="va-mini__sub">{m.body}</div>
                </div>
              </div>
            ))}
            <div className="va-mini">
              <span className="va-mini__icon">🎬</span>
              <div className="va-mini__body">
                <div className="va-mini__title">Прохождение в формате RZX</div>
                <div className="va-mini__sub">CrystalKingdomDizzy.rzx.zip · by Jamie Angus (with rollback)</div>
              </div>
              <button className="zx-button zx-button--sm zx-button--outlined" disabled>denied</button>
            </div>
          </div>
        </div>
      )}

      {tab === "compilations" && (
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

      {tab === "series" && (
        <div className="pp-card">
          <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(220px, 1fr))", gap: 10 }}>
            {SAME_SERIES.map((s, i) => (
              <div key={i} style={{ padding: 10, border: s.title === PROD.title ? "2px solid var(--primary-500)" : "1px solid var(--secondary-200)", borderRadius: "var(--radius-md)", background: s.title === PROD.title ? "var(--primary-50)" : "var(--surface)" }}>
                <div style={{ aspectRatio: "4/3", background: "var(--background-deep)", borderRadius: "var(--radius-sm)", overflow: "hidden", marginBottom: 8 }}>
                  <ZxScreen seed={i * 1000 + 7} palette={["sunset","cool","forest","night","default"][i % 5]} />
                </div>
                <div style={{ fontWeight: 700, fontSize: "var(--font-sm)" }}>{s.title}</div>
                <div style={{ fontSize: "var(--font-xs)", color: "var(--text-light-color)" }}>{s.by} · {s.year}</div>
                <div style={{ marginTop: 6, fontSize: 10, color: "var(--text-light-color)" }}>{s.hardware.join(" · ")}</div>
              </div>
            ))}
          </div>
        </div>
      )}

      {tab === "comments" && (
        <div className="pp-card">
          <div style={{ marginBottom: 12 }}>
            <textarea className="zx-input" style={{ height: 80, width: "100%", padding: 10 }} placeholder="Оставить комментарий…" />
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

window.VariantA = VariantA;
