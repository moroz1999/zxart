/* AuthorPage.jsx — Author page for ZXArt.
   Composes:
   - Header (identity + counters + groups + aliases + links + tech-specs)
   - Mini-dashboard (top works per category)
   - Works navigator (Music / Graphics / Software tabs with smart filters)
   - Collaborators (people + groups)
   - Comments + votes feed (two parallel columns)

   Modes: preset = "moroz" | "newbie", heroStyle = "rich" | "calm"
*/

const { useState, useMemo } = React;

/* ── inline svg icon (matches design-system 24x24 paths) ── */
function AP_I({ name, size = 16 }) {
  const p = {
    play:    "M8 5v14l11-7z",
    pause:   "M6 19h4V5H6v14zm8-14v14h4V5h-4z",
    star:    "M12 2l3.09 6.26 6.91 1-5 4.87L18.18 22 12 18.27 5.82 22l1.18-7.87-5-4.87 6.91-1z",
    image:   "M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm0 13l4-4 3 3 5-5 3 3V5H5v12z",
    music:   "M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6z",
    game:    "M21 6H3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2zM11 13H8v3H6v-3H3v-2h3V8h2v3h3v2zm4.5 2a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm4-3a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z",
    code:    "M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0L19.2 12l-4.6-4.6L16 6l6 6-6 6-1.4-1.4z",
    chevron: "M16.6 8.6L12 13.2 7.4 8.6 6 10l6 6 6-6z",
    chevronUp: "M7.4 15.4L12 10.8l4.6 4.6L18 14l-6-6-6 6z",
    link:    "M3.9 12a4.1 4.1 0 0 1 4.1-4.1h4V6H8a6 6 0 1 0 0 12h4v-1.9H8A4.1 4.1 0 0 1 3.9 12zm5.1 1h6v-2H9v2zm7-7h-4v1.9h4a4.1 4.1 0 0 1 0 8.2h-4V18h4a6 6 0 0 0 0-12z",
    location:"M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z",
    person:  "M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0 2c-3 0-9 1.5-9 4.5V21h18v-2.5c0-3-6-4.5-9-4.5z",
    crown:   "M2 5l4 4 6-7 6 7 4-4-2 14H4L2 5z",
    award:   "M12 2l2.39 4.84L19.78 8l-3.89 3.79.92 5.4L12 14.77 7.19 17.19l.92-5.4L4.22 8l5.39-1.16L12 2z",
    chat:    "M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h11c.55 0 1-.45 1-1z",
    download:"M5 20h14v-2H5v2zm7-18l-5.5 5.5h3.5V14h4V7.5h3.5L12 2z",
    visible: "M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5C21.3 7.6 17 4.5 12 4.5zm0 12.5a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z",
    filter:  "M3 5h18l-7 8v6l-4 2v-8L3 5z",
    sort:    "M3 18h6v-2H3v2zm0-5h12v-2H3v2zm0-7v2h18V6H3z",
  };
  return (
    <svg viewBox="0 0 24 24" width={size} height={size} fill="currentColor" aria-hidden="true" style={{ flexShrink: 0 }}>
      <path d={p[name] || ""}></path>
    </svg>
  );
}

/* Russian plural helper: pluralRu(5, ["мелодию","мелодии","мелодий"]) */
function pluralRu(n, [one, few, many]) {
  const mod10 = n % 10, mod100 = n % 100;
  if (mod10 === 1 && mod100 !== 11) return one;
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return few;
  return many;
}

/* ── pixelized avatar (procedural placeholder for an old scanned photo) ── */
function PixelAvatar({ seed = 42, size = 84 }) {
  const cols = 14, rows = 14;
  let s = seed * 9301 + 49297;
  const rand = () => { s = (s * 9301 + 49297) % 233280; return s / 233280; };
  const cells = [];
  /* face mask roughly oval */
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      const cy = rows / 2, cx = cols / 2;
      const dy = (y - cy) / cy, dx = (x - cx) / cx;
      const inOval = dy*dy + dx*dx*0.9 < 0.9;
      const v = rand();
      let c;
      if (!inOval) c = "#000";
      else if (y < 4) c = v > 0.4 ? "#000" : "#222"; /* hair */
      else if (y > rows - 4) c = v > 0.6 ? "#1a1a1a" : "#0a0a0a"; /* beard/shadow */
      else c = v > 0.55 ? "#dadada" : v > 0.3 ? "#888" : "#333"; /* skin */
      cells.push(<rect key={`${x}-${y}`} x={x} y={y} width={1} height={1} fill={c}/>);
    }
  }
  return (
    <svg viewBox={`0 0 ${cols} ${rows}`} width={size} height={size}
         style={{ imageRendering: "pixelated", display: "block", borderRadius: 2, background: "#000" }}
         shapeRendering="crispEdges">
      {cells}
    </svg>
  );
}

/* ── ZxScreen-style mini picture used for thumbnails (kept independent of ZxScreen import) ── */
function MiniPicture({ seed, palette = "default", style }) {
  return (
    <div style={{ width: "100%", height: "100%", ...style }}>
      <PixelArtSVG seed={seed} palette={palette}/>
    </div>
  );
}

/* ── role chip ── */
function RoleChip({ role, size = "md" }) {
  const r = ROLE_TYPES[role];
  if (!r) return null;
  const iconMap = { music: "music", gfx: "image", code: "code", intro: "game", sfx: "music", design: "code" };
  return (
    <span className={"ap-role-chip ap-role-chip--" + r.color + " ap-role-chip--" + size}>
      <AP_I name={iconMap[role]} size={size === "sm" ? 10 : 12}/>
      {r.label}
    </span>
  );
}

/* ──────────────────────────────────────────────────────────────────────────
   HEADER block — biography (left, one block) + technical specs (collapsible)
   ────────────────────────────────────────────────────────────────────────── */
function AuthorHeader({ profile, counters, totalRatings }) {
  const [showAliases, setShowAliases] = useState(false);
  const [showPlayback, setShowPlayback] = useState(false);

  const VISIBLE_ALIASES = 7;
  const visibleAliases = showAliases ? profile.aliases : profile.aliases.slice(0, VISIBLE_ALIASES);
  const hiddenCount = profile.aliases.length - VISIBLE_ALIASES;

  /* One award at most (VIP > Волонтёр priority). */
  const award = profile.badges.includes("VIP-спонсор")
    ? { kind: "vip", label: "VIP-спонсор", hint: "поддерживает архив пожертвованиями" }
    : profile.badges.includes("Волонтёр")
    ? { kind: "vol", label: "Волонтёр",    hint: "редактирует архив и добавляет материалы" }
    : null;

  return (
    <section className="ap-header">
      <div className="ap-header__left">
        <div className="ap-header__avatar">
          {profile.avatar === "pixel" ? (
            <PixelAvatar seed={profile.handle.charCodeAt(0) * 17}/>
          ) : (
            <div className="ap-avatar--empty"><AP_I name="person" size={40}/></div>
          )}
        </div>
      </div>

      <div className="ap-header__body">
        <div className="ap-header__title-row">
          <h1 className="ap-header__name">{profile.handle}</h1>
          {award && (
            <span className={"ap-award ap-award--" + award.kind} title={award.hint}>
              <AP_I name={award.kind === "vip" ? "crown" : "award"} size={12}/>
              {award.label}
            </span>
          )}
          <div className="ap-header__role-chips">
            {profile.roles.includes("artist")   && <span className="ap-tag ap-tag--gfx">  <AP_I name="image" size={12}/>Художник</span>}
            {profile.roles.includes("musician") && <span className="ap-tag ap-tag--music"><AP_I name="music" size={12}/>Музыкант</span>}
            {profile.roles.includes("coder")    && <span className="ap-tag ap-tag--code"> <AP_I name="code"  size={12}/>Кодер</span>}
          </div>
        </div>

        {/* one biography block — name, location, joined */}
        <div className="ap-header__bio">
          {profile.realName && profile.realName !== "—" && <span className="ap-bio__name">{profile.realName}</span>}
          {profile.location && (
            <>
              {profile.realName !== "—" && <span className="ap-bio__sep">·</span>}
              <span className="ap-bio__loc"><AP_I name="location" size={12}/>{profile.location.map((p,i)=>(
                <React.Fragment key={p}>{i>0 && ", "}<a href="#" onClick={e=>e.preventDefault()}>{p}</a></React.Fragment>
              ))}</span>
            </>
          )}
          <span className="ap-bio__sep">·</span>
          <span className="ap-bio__joined">На ZX-Art с {profile.joined.slice(0,4)} (логин <code>{profile.siteUser}</code>)</span>
        </div>

        {/* Stats — sentence-style summary line (the reading the brief asked for:
            "написал N мелодий, нарисовал M картин, участвовал в разработке K программ"),
            plus a compact rating strip when ratings exist. */}
        <p className="ap-stats-sentence">
          {profile.realName && profile.realName !== "—" ? "Он " : "Этот автор "}
          {[
            counters.pictures > 0 && <React.Fragment key="g">нарисовал <b>{counters.pictures.toLocaleString("ru-RU")}</b> {pluralRu(counters.pictures, ["картину","картины","картин"])}</React.Fragment>,
            counters.tunes > 0    && <React.Fragment key="m">написал <b>{counters.tunes.toLocaleString("ru-RU")}</b> {pluralRu(counters.tunes, ["мелодию","мелодии","мелодий"])}</React.Fragment>,
            counters.prods > 0    && <React.Fragment key="p">участвовал в разработке <b>{counters.prods.toLocaleString("ru-RU")}</b> {pluralRu(counters.prods, ["программы","программ","программ"])}</React.Fragment>,
          ].filter(Boolean).reduce((acc, el, i, arr) => {
            const sep = i === 0 ? "" : (i === arr.length - 1 ? " и " : ", ");
            acc.push(sep, el);
            return acc;
          }, []).concat([" — и получил ", <b key="c">{counters.comments}</b>, " ", pluralRu(counters.comments, ["комментарий","комментария","комментариев"]), "."])}
        </p>
        {(totalRatings.artist > 0 || totalRatings.musician > 0) && (
          <div className="ap-rating-strip">
            <span className="ap-rating-strip__label">Рейтинг по голосам сообщества:</span>
            {totalRatings.artist > 0 && (
              <span className="ap-rating-strip__item" title="Сумма звёзд за хорошо проголосованные картины">
                <AP_I name="image" size={12}/>
                <b>{totalRatings.artist.toFixed(2)}</b>
                <span className="ap-rating-strip__sub">художник</span>
              </span>
            )}
            {totalRatings.musician > 0 && (
              <span className="ap-rating-strip__item" title="Сумма звёзд за хорошо проголосованные мелодии">
                <AP_I name="music" size={12}/>
                <b>{totalRatings.musician.toFixed(2)}</b>
                <span className="ap-rating-strip__sub">музыкант</span>
              </span>
            )}
          </div>
        )}

        {/* groups — list of chips */}
        {profile.groups.length > 0 && (
          <div className="ap-meta-row">
            <span className="ap-meta-row__label">Группы:</span>
            <div className="ap-chips">
              {profile.groups.map(g => (
                <a key={g.name} href="#" className={"ap-group-chip" + (g.parent ? " ap-group-chip--sub" : "")} onClick={e=>e.preventDefault()}>
                  {g.parent && <span className="ap-group-chip__sub">↳ {g.parent} /</span>}
                  <span className="ap-group-chip__name">{g.name}</span>
                  {g.years && <span className="ap-group-chip__years">{g.years}</span>}
                </a>
              ))}
            </div>
          </div>
        )}

        {/* aliases — collapsed after 7 */}
        {profile.aliases.length > 0 && (
          <div className="ap-meta-row">
            <span className="ap-meta-row__label">Ники:</span>
            <div className="ap-aliases">
              {visibleAliases.map((a, i) => (
                <a key={a + i} href="#" onClick={e=>e.preventDefault()}>{a}{i < visibleAliases.length - 1 ? "," : ""}</a>
              ))}
              {hiddenCount > 0 && !showAliases && (
                <button type="button" className="ap-aliases__more" onClick={()=>setShowAliases(true)}>
                  +{hiddenCount}
                </button>
              )}
              {showAliases && hiddenCount > 0 && (
                <button type="button" className="ap-aliases__more" onClick={()=>setShowAliases(false)}>свернуть</button>
              )}
            </div>
          </div>
        )}

        {/* external links */}
        {profile.links.length > 0 && (
          <div className="ap-meta-row">
            <span className="ap-meta-row__label">Ссылки:</span>
            <div className="ap-ext-links">
              {profile.links.map((l, i) => (
                <a key={i} href="#" onClick={e=>e.preventDefault()} title={l.label}>
                  <span className="ap-ext-icon">{l.icon}</span>{l.label}
                </a>
              ))}
            </div>
          </div>
        )}

        {/* Default playback profile — these are the author's preferred conversion
            settings (the site converts native ZX formats to ogg/png; every
            author tweaks these to their taste, and individual works can
            override them). */}
        <div className="ap-meta-row ap-meta-row--tech">
          <button type="button" className="ap-tech-toggle" onClick={()=>setShowPlayback(!showPlayback)}>
            <AP_I name={showPlayback ? "chevronUp" : "chevron"} size={14}/>
            Настройки конвертации по умолчанию
            <span className="ap-tech-toggle__hint">авторские дефолты для ogg/png — конкретная работа может их переопределить</span>
          </button>
          {showPlayback && (
            <div className="ap-tech">
              <div className="ap-tech__row">
                <span className="ap-tech__k">Палитра картин</span>
                <span className="ap-tech__v">{profile.tech.palette}</span>
              </div>
              <div className="ap-tech__row">
                <span className="ap-tech__k">Чип AY</span>
                <span className="ap-tech__v">{profile.tech.ayChip}</span>
              </div>
              <div className="ap-tech__row">
                <span className="ap-tech__k">Раскладка каналов AY</span>
                <span className="ap-tech__v">{profile.tech.ayChannels}</span>
              </div>
              <div className="ap-tech__row">
                <span className="ap-tech__k">Тактовая частота AY</span>
                <span className="ap-tech__v">{profile.tech.ayClock}</span>
              </div>
              <div className="ap-tech__row">
                <span className="ap-tech__k">Частота прерываний INT</span>
                <span className="ap-tech__v">{profile.tech.intFreq}</span>
              </div>
            </div>
          )}
        </div>
      </div>
    </section>
  );
}

/* ──────────────────────────────────────────────────────────────────────────
   MINI-DASHBOARD — три колонки, 3–4 работы в каждой.
   Сюда вынесено самое сильное; подробные списки с фильтрами/пагинацией
   живут ниже в вкладках «Все работы».
   ────────────────────────────────────────────────────────────────────────── */
function MiniDashboard({ pictures, tunes, prods, authorHandle, onJumpToTab }) {
  const [sort, setSort] = useState("votes");
  const sorter = (a, b) => {
    if (sort === "votes")     return b.votes - a.votes;
    if (sort === "year")      return b.year - a.year;
    if (sort === "plays")     return (b.plays || 0) - (a.plays || 0);
    if (sort === "downloads") return (b.downloads || 0) - (a.downloads || 0);
    return 0;
  };
  const topPics  = useMemo(() => [...pictures].sort(sorter).slice(0, 4), [pictures, sort]);
  const topTunes = useMemo(() => [...tunes].sort(sorter).slice(0, 4),    [tunes, sort]);
  const topProds = useMemo(() => [...prods].sort(sorter).slice(0, 4),    [prods, sort]);

  const cols = [
    pictures.length > 0 && { key: "gfx",   label: "Графика", total: pictures.length, items: topPics  },
    tunes.length > 0    && { key: "music", label: "Музыка",  total: tunes.length,    items: topTunes },
    prods.length > 0    && { key: "soft",  label: "Софт",    total: prods.length,    items: topProds },
  ].filter(Boolean);

  if (cols.length === 0) return null;

  return (
    <section className="ap-dashboard">
      <div className="ap-section__h">
        <h2>Лучшие работы</h2>
        <span className="ap-section__hint">самое сильное из каждой категории — подробные списки с фильтрами в вкладках ниже</span>
        <div className="ap-dashboard__sort">
          <label>топ по</label>
          <select value={sort} onChange={e => setSort(e.target.value)}>
            <option value="votes">голосам ★</option>
            <option value="year">году ↓</option>
            <option value="plays">запускам на сайте ▶</option>
            <option value="downloads">скачиваниям ⬇</option>
          </select>
        </div>
      </div>

      <div className="ap-dash-grid" style={{ gridTemplateColumns: `repeat(${cols.length}, 1fr)` }}>
        {cols.map(col => (
          <div key={col.key} className={"ap-dash-col ap-dash-col--" + col.key}>
            <div className="ap-dash-col__head">
              <AP_I name={col.key === "gfx" ? "image" : col.key === "music" ? "music" : "game"} size={14}/>
              <span className="ap-dash-col__label">{col.label}</span>
              <span className="ap-dash-col__count">{col.total}</span>
              <a className="ap-dash-col__all" href="#" onClick={e=>{e.preventDefault(); onJumpToTab && onJumpToTab(col.key);}}>
                все →
              </a>
            </div>

            {col.key === "gfx" && (
              <div className="ap-dash-pics">
                {col.items.map(p => (
                  <a key={p.id} href="#" className="ap-dash-pic" onClick={e=>e.preventDefault()}>
                    <div className="ap-dash-pic__art">
                      <MiniPicture seed={p.id} palette={p.palette}/>
                      {p.place && <span className="ap-dash-pic__place">{p.place}</span>}
                    </div>
                    <div className="ap-dash-pic__title">{p.title}</div>
                    <div className="ap-dash-pic__meta">
                      <span className="ap-dash-pic__year">{p.year}</span>
                      <span className="ap-dash-pic__star">★ {p.stars}.{p.votes % 9}</span>
                      <span className="ap-dash-pic__votes">· {p.votes}</span>
                    </div>
                  </a>
                ))}
              </div>
            )}

            {col.key === "music" && (
              <div className="ap-dash-tunes">
                {col.items.map((t, i) => (
                  <div key={t.id} className="ap-dash-tune">
                    <span className="ap-dash-tune__rank">{i + 1}</span>
                    <button className="ap-dash-tune__play" aria-label="Play"><AP_I name="play" size={12}/></button>
                    <div className="ap-dash-tune__body">
                      <div className="ap-dash-tune__title">{t.title}</div>
                      <div className="ap-dash-tune__meta">
                        {t.chip} · {t.duration} · {t.year}
                      </div>
                    </div>
                    <div className="ap-dash-tune__stats">
                      <span className="ap-feat__star">★ {t.stars}.{t.votes % 9}</span>
                      <span className="ap-dash-tune__plays">▶ {t.plays.toLocaleString("ru-RU")}</span>
                    </div>
                  </div>
                ))}
              </div>
            )}

            {col.key === "soft" && (
              <div className="ap-dash-prods">
                {col.items.map(p => {
                  const adapted = {
                    id: p.id,
                    title: p.title,
                    palette: p.palette,
                    kind: p.kind,
                    year: p.year,
                    stars: p.stars,
                    votes: p.votes,
                    authors: [authorHandle, ...p.coAuthors],
                    party: null,
                    place: null,
                  };
                  return (
                    <div key={p.id} className="ap-prodwrap ap-prodwrap--compact">
                      {(p.roles.length > 0 || p.introRelease) && (
                        <div className="ap-prodwrap__roles">
                          {p.roles.map(r => <RoleChip key={r} role={r}/>)}
                          {p.introRelease && <RoleChip role="intro"/>}
                        </div>
                      )}
                      <ProdCard prod={adapted}/>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        ))}
      </div>
    </section>
  );
}

window.AuthorHeader = AuthorHeader;
window.MiniDashboard = MiniDashboard;
window.AP_I = AP_I;
window.PixelAvatar = PixelAvatar;
window.RoleChip = RoleChip;
