/* AuthorPageWorks.jsx — Works navigator (tabs + filters + pagination),
   Collaborators, Comments & Votes feed, and the top-level AuthorPage shell. */

const { useState: useState2, useMemo: useMemo2 } = React;

/* ──────────────────────────────────────────────────────────────────────────
   WORKS NAVIGATOR
   tabs: Графика / Музыка / Софт
   For pictures & tunes:
     - toolbar: sort, year filter, party filter, search
     - view: grouped by year (timeline rail)
     - pagination
   For software:
     - toolbar: role-filter chips
     - if no role filter: group by year + per-card role chips
     - if role filter set: flat list under one role badge, no per-card chips (already implied)
   ────────────────────────────────────────────────────────────────────────── */

const PAGE_SIZE_PIC = 24;
const PAGE_SIZE_TUNE = 20;
const PAGE_SIZE_PROD = 12;

function Pagination({ page, total, perPage, onChange }) {
  const pages = Math.max(1, Math.ceil(total / perPage));
  if (pages <= 1) return null;
  const nums = [];
  const push = n => nums.push(n);
  push(1);
  for (let p = Math.max(2, page - 2); p <= Math.min(pages - 1, page + 2); p++) push(p);
  if (pages > 1) push(pages);
  const dedup = [...new Set(nums)];
  const out = [];
  for (let i = 0; i < dedup.length; i++) {
    if (i > 0 && dedup[i] - dedup[i-1] > 1) out.push("…");
    out.push(dedup[i]);
  }
  return (
    <nav className="ap-pagination" aria-label="pagination">
      <button className="ap-page-btn" disabled={page === 1} onClick={()=>onChange(page-1)}>← пред.</button>
      {out.map((n, i) => n === "…" ? (
        <span key={i} className="ap-page-gap">…</span>
      ) : (
        <button key={i} className={"ap-page-btn" + (n === page ? " ap-page-btn--active" : "")} onClick={()=>onChange(n)}>{n}</button>
      ))}
      <button className="ap-page-btn" disabled={page === pages} onClick={()=>onChange(page+1)}>след. →</button>
      <span className="ap-pagination__total">из {pages} страниц · всего {total}</span>
    </nav>
  );
}

/* Compact picture card used in the year-grouped grid — uses the existing PictureCard. */
function PictureCardMini({ pic }) {
  return (
    <div className="ap-pic-wrap">
      <PictureCard picture={{
        id: pic.id, title: pic.title, palette: pic.palette,
        authors: pic.authors, year: pic.year, stars: pic.stars, votes: pic.votes,
        party: pic.party || "", place: pic.place || null,
        format: pic.format, realtime: pic.realtime, flickering: pic.flickering,
      }}/>
    </div>
  );
}

function YearRail({ year, count }) {
  return (
    <div className="ap-year-rail">
      <div className="ap-year-rail__dot"></div>
      <div className="ap-year-rail__year">{year}</div>
      <div className="ap-year-rail__count">{count}</div>
    </div>
  );
}

function GraphicsTab({ pictures }) {
  const [page, setPage] = useState2(1);
  const [sort, setSort] = useState2("year-desc"); /* year-desc | year-asc | votes | plays */
  const [formatFilter, setFormatFilter] = useState2("all");
  const [partyFilter, setPartyFilter] = useState2("all");
  const [search, setSearch] = useState2("");

  const allFormats = useMemo2(() => {
    const fs = [...new Set(pictures.map(p => p.format).filter(Boolean))];
    return fs;
  }, [pictures]);
  const allParties = useMemo2(() => {
    const ps = [...new Set(pictures.map(p => p.party).filter(Boolean))].sort();
    return ps;
  }, [pictures]);

  const filtered = useMemo2(() => {
    return pictures.filter(p =>
      (formatFilter === "all" || p.format === formatFilter) &&
      (partyFilter === "all" || p.party === partyFilter) &&
      (search === "" || p.title.toLowerCase().includes(search.toLowerCase()))
    );
  }, [pictures, formatFilter, partyFilter, search]);

  const sorted = useMemo2(() => {
    const arr = [...filtered];
    if (sort === "year-desc") arr.sort((a,b)=>b.year-a.year || b.votes-a.votes);
    if (sort === "year-asc")  arr.sort((a,b)=>a.year-b.year || b.votes-a.votes);
    if (sort === "votes")     arr.sort((a,b)=>b.votes-a.votes);
    if (sort === "plays")     arr.sort((a,b)=>(b.plays||0)-(a.plays||0));
    if (sort === "downloads") arr.sort((a,b)=>(b.downloads||0)-(a.downloads||0));
    return arr;
  }, [filtered, sort]);

  const pageStart = (page - 1) * PAGE_SIZE_PIC;
  const pageItems = sorted.slice(pageStart, pageStart + PAGE_SIZE_PIC);

  /* group page items by year when sort is by year, otherwise show flat */
  const groupedByYear = (sort === "year-desc" || sort === "year-asc");
  const groups = useMemo2(() => {
    if (!groupedByYear) return [{ year: null, items: pageItems }];
    const m = new Map();
    pageItems.forEach(p => {
      if (!m.has(p.year)) m.set(p.year, []);
      m.get(p.year).push(p);
    });
    const arr = [...m.entries()].map(([year, items]) => ({ year, items }));
    if (sort === "year-asc") arr.sort((a,b)=>a.year-b.year); else arr.sort((a,b)=>b.year-a.year);
    return arr;
  }, [pageItems, sort, groupedByYear]);

  return (
    <div>
      <div className="ap-toolbar">
        <div className="ap-toolbar__group">
          <AP_I name="sort" size={14}/>
          <span>Сортировка:</span>
          {[
            ["year-desc", "новее"],
            ["year-asc",  "старее"],
            ["votes",     "по голосам"],
            ["plays",     "по запускам на сайте"],
            ["downloads", "по скачиваниям"],
          ].map(([k,l]) => (
            <button key={k} className={"ap-pill" + (sort===k ? " ap-pill--on" : "")} onClick={()=>{ setSort(k); setPage(1); }}>{l}</button>
          ))}
        </div>
        <div className="ap-toolbar__sep"/>
        <div className="ap-toolbar__group">
          <label>Формат:</label>
          <select value={formatFilter} onChange={e=>{ setFormatFilter(e.target.value); setPage(1); }}>
            <option value="all">все ({allFormats.length})</option>
            {allFormats.map(f => <option key={f} value={f}>{f}</option>)}
          </select>
        </div>
        <div className="ap-toolbar__group">
          <label>Пати:</label>
          <select value={partyFilter} onChange={e=>{ setPartyFilter(e.target.value); setPage(1); }}>
            <option value="all">все</option>
            {allParties.map(p => <option key={p} value={p}>{p}</option>)}
          </select>
        </div>
        <div className="ap-toolbar__group ap-toolbar__group--search">
          <input type="text" className="ap-search" placeholder="поиск по названию…" value={search} onChange={e=>{ setSearch(e.target.value); setPage(1); }}/>
        </div>
      </div>

      <div className="ap-toolbar__result">
        Найдено <b>{filtered.length}</b> из {pictures.length} картин
        {formatFilter !== "all" && <span> · формат {formatFilter}</span>}
        {partyFilter !== "all" && <span> · {partyFilter}</span>}
        {search && <span> · по запросу "{search}"</span>}
      </div>

      {groups.map((g, gi) => (
        <div key={gi} className="ap-year-group">
          {g.year != null && <YearRail year={g.year} count={g.items.length}/>}
          <div className="ap-pic-grid">
            {g.items.map(p => <PictureCardMini key={p.id} pic={p}/>)}
          </div>
        </div>
      ))}

      {filtered.length === 0 && (
        <div className="ap-empty">Ничего не найдено. Попробуй другие фильтры.</div>
      )}

      <Pagination page={page} total={filtered.length} perPage={PAGE_SIZE_PIC} onChange={setPage}/>
    </div>
  );
}

function MusicTab({ tunes }) {
  const [page, setPage] = useState2(1);
  const [sort, setSort] = useState2("year-desc");
  const [chipFilter, setChipFilter] = useState2("all");
  const [search, setSearch] = useState2("");

  const allChips = useMemo2(() => [...new Set(tunes.map(t => t.chip))], [tunes]);

  const filtered = useMemo2(() => tunes.filter(t =>
    (chipFilter === "all" || t.chip === chipFilter) &&
    (search === "" || t.title.toLowerCase().includes(search.toLowerCase()))
  ), [tunes, chipFilter, search]);
  const sorted = useMemo2(() => {
    const arr = [...filtered];
    if (sort === "year-desc") arr.sort((a,b)=>b.year-a.year || b.votes-a.votes);
    if (sort === "year-asc")  arr.sort((a,b)=>a.year-b.year);
    if (sort === "votes")     arr.sort((a,b)=>b.votes-a.votes);
    if (sort === "plays")     arr.sort((a,b)=>b.plays-a.plays);
    if (sort === "downloads") arr.sort((a,b)=>(b.downloads||0)-(a.downloads||0));
    return arr;
  }, [filtered, sort]);

  const pageStart = (page - 1) * PAGE_SIZE_TUNE;
  const pageItems = sorted.slice(pageStart, pageStart + PAGE_SIZE_TUNE);

  const groupedByYear = (sort === "year-desc" || sort === "year-asc");
  const groups = useMemo2(() => {
    if (!groupedByYear) return [{ year: null, items: pageItems }];
    const m = new Map();
    pageItems.forEach(p => { if (!m.has(p.year)) m.set(p.year, []); m.get(p.year).push(p); });
    const arr = [...m.entries()].map(([year, items]) => ({ year, items }));
    if (sort === "year-asc") arr.sort((a,b)=>a.year-b.year); else arr.sort((a,b)=>b.year-a.year);
    return arr;
  }, [pageItems, sort, groupedByYear]);

  return (
    <div>
      <div className="ap-toolbar">
        <div className="ap-toolbar__group">
          <AP_I name="sort" size={14}/>
          <span>Сортировка:</span>
          {[
            ["year-desc", "новее"],
            ["year-asc",  "старее"],
            ["votes",     "по голосам"],
            ["plays",     "по запускам на сайте"],
            ["downloads", "по скачиваниям"],
          ].map(([k,l]) => (
            <button key={k} className={"ap-pill" + (sort===k ? " ap-pill--on" : "")} onClick={()=>{ setSort(k); setPage(1); }}>{l}</button>
          ))}
        </div>
        <div className="ap-toolbar__sep"/>
        <div className="ap-toolbar__group">
          <label>Тип звучания:</label>
          {[["all","все"], ...allChips.map(c => [c, c])].map(([k,l]) => (
            <button key={k} className={"ap-pill" + (chipFilter===k ? " ap-pill--on" : "")} onClick={()=>{ setChipFilter(k); setPage(1); }}>{l}</button>
          ))}
        </div>
        <div className="ap-toolbar__group ap-toolbar__group--search">
          <input type="text" className="ap-search" placeholder="поиск по названию…" value={search} onChange={e=>{ setSearch(e.target.value); setPage(1); }}/>
        </div>
      </div>

      <div className="ap-toolbar__result">
        Найдено <b>{filtered.length}</b> из {tunes.length} мелодий
      </div>

      {groups.map((g, gi) => (
        <div key={gi} className="ap-year-group">
          {g.year != null && <YearRail year={g.year} count={g.items.length}/>}
          <div className="ap-tune-list">
            {g.items.map(t => (
              <div key={t.id} className="zx-tune-row">
                <button className="zx-tune-row__play" aria-label="play"><AP_I name="play" size={14}/></button>
                <span className="zx-tune-row__title">{t.title}</span>
                <span className="zx-tune-row__author">{t.chip} · {t.duration}</span>
                <span className="zx-tune-row__chip">
                  <span className="ap-feat__star">★ {t.stars}</span>
                  <span style={{marginLeft:8, color:"var(--text-light-color)"}}>· ▶ {t.plays.toLocaleString("ru-RU")}</span>
                  <span style={{marginLeft:8, color:"var(--text-light-color)"}}>· ⬇ {t.downloads}</span>
                </span>
              </div>
            ))}
          </div>
        </div>
      ))}

      {filtered.length === 0 && (
        <div className="ap-empty">Ничего не найдено.</div>
      )}

      <Pagination page={page} total={filtered.length} perPage={PAGE_SIZE_TUNE} onChange={setPage}/>
    </div>
  );
}

/* Wraps the standard ProdCard with the author-context annotations
   (role chips for "что именно делал автор" + "intro для релиза …").
   The card itself is unchanged — chips sit in a separate row above it. */
function AuthorProdCard({ prod, authorHandle, showRoles = true }) {
  const adapted = {
    id: prod.id,
    title: prod.title,
    palette: prod.palette,
    kind: prod.kind,
    year: prod.year,
    stars: prod.stars,
    votes: prod.votes,
    authors: [authorHandle, ...prod.coAuthors],
    party: null,
    place: null,
  };
  return (
    <div className="ap-prodwrap">
      {showRoles && (prod.roles.length > 0 || prod.introRelease) && (
        <div className="ap-prodwrap__roles">
          {prod.roles.map(r => <RoleChip key={r} role={r}/>)}
          {prod.introRelease && (
            <span className="ap-prodwrap__intro">
              <RoleChip role="intro"/>
              <span>для релиза <a href="#" onClick={e=>e.preventDefault()}>{prod.introRelease}</a></span>
            </span>
          )}
        </div>
      )}
      <ProdCard prod={adapted}/>
    </div>
  );
}

function SoftwareTab({ prods, authorHandle }) {
  const [page, setPage] = useState2(1);
  const [roleFilter, setRoleFilter] = useState2("all");
  const [catFilter, setCatFilter] = useState2("all"); /* "all" | top-level kind | "kind/sub" */
  const [sort, setSort] = useState2("year-desc");

  const roleCounts = useMemo2(() => {
    const c = { all: prods.length, intro: 0 };
    Object.keys(ROLE_TYPES).forEach(r => c[r] = 0);
    prods.forEach(p => {
      p.roles.forEach(r => c[r] = (c[r] || 0) + 1);
      if (p.introRelease) c.intro = (c.intro || 0) + 1;
    });
    return c;
  }, [prods]);

  /* Build category tree from actual data: only show categories that exist. */
  const catTree = useMemo2(() => {
    const tree = {};
    prods.forEach(p => {
      if (!tree[p.kind]) tree[p.kind] = { total: 0, subs: {} };
      tree[p.kind].total += 1;
      if (p.subKind) tree[p.kind].subs[p.subKind] = (tree[p.kind].subs[p.subKind] || 0) + 1;
    });
    return tree;
  }, [prods]);

  const filtered = useMemo2(() => {
    let res = prods;
    if (catFilter !== "all") {
      if (catFilter.includes("/")) {
        const [k, sub] = catFilter.split("/");
        res = res.filter(p => p.kind === k && p.subKind === sub);
      } else {
        res = res.filter(p => p.kind === catFilter);
      }
    }
    if (roleFilter === "intro") res = res.filter(p => p.introRelease);
    else if (roleFilter !== "all") res = res.filter(p => p.roles.includes(roleFilter));
    return res;
  }, [prods, roleFilter, catFilter]);

  const sorted = useMemo2(() => {
    const arr = [...filtered];
    if (sort === "year-desc") arr.sort((a,b)=>b.year-a.year || b.votes-a.votes);
    if (sort === "year-asc")  arr.sort((a,b)=>a.year-b.year);
    if (sort === "votes")     arr.sort((a,b)=>b.votes-a.votes);
    if (sort === "plays")     arr.sort((a,b)=>(b.plays||0)-(a.plays||0));
    if (sort === "downloads") arr.sort((a,b)=>b.downloads-a.downloads);
    return arr;
  }, [filtered, sort]);

  const pageStart = (page - 1) * PAGE_SIZE_PROD;
  const pageItems = sorted.slice(pageStart, pageStart + PAGE_SIZE_PROD);

  /* Smart grouping per the brief: only group-by-year when no role filter,
     so the page never repeats "music music music" tags down the column. */
  const groupedByYear = roleFilter === "all" && (sort === "year-desc" || sort === "year-asc");
  const groups = useMemo2(() => {
    if (!groupedByYear) return [{ year: null, items: pageItems }];
    const m = new Map();
    pageItems.forEach(p => { if (!m.has(p.year)) m.set(p.year, []); m.get(p.year).push(p); });
    const arr = [...m.entries()].map(([year, items]) => ({ year, items }));
    if (sort === "year-asc") arr.sort((a,b)=>a.year-b.year); else arr.sort((a,b)=>b.year-a.year);
    return arr;
  }, [pageItems, sort, groupedByYear]);

  return (
    <div>
      <div className="ap-toolbar ap-toolbar--soft">
        <div className="ap-toolbar__group ap-toolbar__group--cats">
          <span className="ap-toolbar__group-title">Категория:</span>
          <button className={"ap-pill" + (catFilter==="all" ? " ap-pill--on" : "")} onClick={()=>{ setCatFilter("all"); setPage(1); }}>
            все <span className="ap-pill__num">{prods.length}</span>
          </button>
          {Object.entries(catTree).map(([k, info]) => (
            <React.Fragment key={k}>
              <button className={"ap-pill" + (catFilter===k ? " ap-pill--on" : "")} onClick={()=>{ setCatFilter(k); setPage(1); }}>
                {k} <span className="ap-pill__num">{info.total}</span>
              </button>
              {(catFilter === k || catFilter.startsWith(k + "/")) && Object.entries(info.subs).map(([s, n]) => {
                const key = k + "/" + s;
                return (
                  <button key={key} className={"ap-pill ap-pill--sub" + (catFilter===key ? " ap-pill--on" : "")} onClick={()=>{ setCatFilter(key); setPage(1); }}>
                    {s} <span className="ap-pill__num">{n}</span>
                  </button>
                );
              })}
            </React.Fragment>
          ))}
        </div>
        <div className="ap-toolbar__group ap-toolbar__group--roles">
          <span className="ap-toolbar__group-title">Роль автора в проде:</span>
          <button className={"ap-pill" + (roleFilter==="all" ? " ap-pill--on" : "")} onClick={()=>{ setRoleFilter("all"); setPage(1); }}>
            все <span className="ap-pill__num">{roleCounts.all}</span>
          </button>
          {[["music", "Музыка"], ["gfx", "Графика"], ["code", "Код"], ["design", "Гейм-дизайн"], ["sfx", "Звук"], ["intro", "Интро к релизу"]].map(([k,l]) => (
            roleCounts[k] > 0 && (
              <button key={k} className={"ap-pill ap-pill--role-" + (ROLE_TYPES[k]?.color || "intro") + (roleFilter===k ? " ap-pill--on" : "")} onClick={()=>{ setRoleFilter(k); setPage(1); }}>
                {l} <span className="ap-pill__num">{roleCounts[k]}</span>
              </button>
            )
          ))}
        </div>
        <div className="ap-toolbar__sep"/>
        <div className="ap-toolbar__group">
          <AP_I name="sort" size={14}/>
          <span>Сортировка:</span>
          {[
            ["year-desc", "новее"],
            ["votes",     "по голосам"],
            ["plays",     "по запускам на сайте"],
            ["downloads", "по скачиваниям"],
          ].map(([k,l]) => (
            <button key={k} className={"ap-pill" + (sort===k ? " ap-pill--on" : "")} onClick={()=>{ setSort(k); setPage(1); }}>{l}</button>
          ))}
        </div>
      </div>

      {/* Найдено <b>N</b> программ — role label moved into the toolbar group */}
      <div className="ap-toolbar__result">
        Найдено <b>{filtered.length}</b> {pluralRu(filtered.length, ["программа","программы","программ"])}
        {roleFilter !== "all" && <> · в роли <RoleChip role={roleFilter}/></>}
      </div>

      {groups.map((g, gi) => (
        <div key={gi} className="ap-year-group ap-year-group--prod">
          {g.year != null && <YearRail year={g.year} count={g.items.length}/>}
          <div className="ap-prod-grid">
            {g.items.map(p => <AuthorProdCard key={p.id} prod={p} authorHandle={authorHandle} showRoles={roleFilter === "all"}/>)}
          </div>
        </div>
      ))}

      {filtered.length === 0 && <div className="ap-empty">Ничего не найдено.</div>}

      <Pagination page={page} total={filtered.length} perPage={PAGE_SIZE_PROD} onChange={setPage}/>
    </div>
  );
}

function WorksNavigator({ pictures, tunes, prods, authorHandle, initialTab }) {
  const tabs = [];
  if (pictures.length > 0) tabs.push({ id: "gfx",   label: "Графика", icon: "image", count: pictures.length });
  if (tunes.length > 0)    tabs.push({ id: "music", label: "Музыка",  icon: "music", count: tunes.length });
  if (prods.length > 0)    tabs.push({ id: "soft",  label: "Софт",    icon: "game",  count: prods.length });
  const [tab, setTab] = useState2(tabs[0]?.id || "gfx");

  /* When the dashboard asks to jump to a tab, switch it. */
  React.useEffect(() => {
    if (initialTab && tabs.some(t => t.id === initialTab)) setTab(initialTab);
  }, [initialTab]);

  if (tabs.length === 0) {
    return (
      <section className="ap-works">
        <div className="ap-section__h"><h2>Работы</h2></div>
        <div className="ap-empty ap-empty--big">
          У этого автора пока нет добавленных работ.
          <div style={{ marginTop: 8, fontSize: "var(--font-xs)" }}>Если у вас есть его произведения — поделитесь, мы добавим.</div>
        </div>
      </section>
    );
  }

  return (
    <section className="ap-works">
      <div className="ap-section__h">
        <h2>Все работы</h2>
        <span className="ap-section__hint">фильтры по году, партии, чипу и поиск — без перезагрузки</span>
      </div>

      <div className="ap-worktabs">
        {tabs.map(t => (
          <button key={t.id} className={"ap-worktab" + (tab===t.id ? " ap-worktab--on" : "")} onClick={()=>setTab(t.id)}>
            <AP_I name={t.icon} size={14}/>
            {t.label}
            <span className="ap-worktab__count">{t.count.toLocaleString("ru-RU")}</span>
          </button>
        ))}
      </div>

      <div className="ap-worktab-body">
        {tab==="gfx"   && <GraphicsTab pictures={pictures}/>}
        {tab==="music" && <MusicTab tunes={tunes}/>}
        {tab==="soft"  && <SoftwareTab prods={prods} authorHandle={authorHandle}/>}
      </div>
    </section>
  );
}

/* ──────────────────────────────────────────────────────────────────────────
   COLLABORATORS + GROUPS
   ────────────────────────────────────────────────────────────────────────── */
function Collaborators({ people, groups }) {
  if (people.length === 0 && groups.length === 0) return null;
  return (
    <section className="ap-collab">
      <div className="ap-section__h">
        <h2>С кем работал</h2>
        <span className="ap-section__hint">соавторы и группы, отсортированы по числу совместных работ</span>
      </div>
      <div className="ap-collab__cols">
        <div className="ap-collab__col">
          <div className="ap-collab__col-h">Люди <span className="ap-section__count">{people.length}</span></div>
          <div className="ap-collab__people">
            {people.map(p => {
              const total = p.joint.pictures + p.joint.tunes + p.joint.prods;
              return (
                <a key={p.handle} href="#" className="ap-collab__person" onClick={e=>e.preventDefault()}>
                  <PixelAvatar seed={p.handle.charCodeAt(0) * 23} size={32}/>
                  <div className="ap-collab__person-body">
                    <div className="ap-collab__person-name">
                      {p.handle}
                      {p.years && <span className="ap-collab__person-years">{p.years}</span>}
                    </div>
                    <div className="ap-collab__person-groups">{p.groups}</div>
                    <div className="ap-collab__person-stats">
                      {p.joint.pictures > 0 && <span><AP_I name="image" size={10}/>{p.joint.pictures}</span>}
                      {p.joint.tunes > 0    && <span><AP_I name="music" size={10}/>{p.joint.tunes}</span>}
                      {p.joint.prods > 0    && <span><AP_I name="game"  size={10}/>{p.joint.prods}</span>}
                    </div>
                  </div>
                  <div className="ap-collab__person-total"><b>{total}</b><span>совм.</span></div>
                </a>
              );
            })}
          </div>
        </div>
        <div className="ap-collab__col">
          <div className="ap-collab__col-h">Группы <span className="ap-section__count">{groups.length}</span></div>
          <div className="ap-collab__groups">
            {groups.map(g => (
              <a key={g.name} href="#" className="ap-collab__group" onClick={e=>e.preventDefault()}>
                <div className="ap-collab__group-head">
                  <span className="ap-collab__group-name">{g.name}</span>
                  {g.years && <span className="ap-collab__group-years">{g.years}</span>}
                </div>
                <div className="ap-collab__group-meta">
                  {g.members} участников · {g.ourWorks} работ автора
                  {g.releases ? <> · {g.releases} релизов</> : null}
                </div>
              </a>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}

/* ──────────────────────────────────────────────────────────────────────────
   COMMENTS + VOTES feed — two horizontal columns
   ────────────────────────────────────────────────────────────────────────── */
function FeedColumns({ comments, votes }) {
  if (comments.length === 0 && votes.length === 0) return null;
  return (
    <section className="ap-feed">
      <div className="ap-section__h">
        <h2>Активность вокруг работ автора</h2>
        <span className="ap-section__hint">самое свежее: что говорят и как голосуют</span>
      </div>
      <div className="ap-feed__cols">
        <div className="ap-feed__col">
          <div className="ap-collab__col-h"><AP_I name="chat" size={14}/>Свежие комментарии <span className="ap-section__count">{comments.length}</span></div>
          <div className="ap-feed__comments">
            {comments.map(c => (
              <article key={c.id} className="ap-fcomment">
                <div className="ap-fcomment__head">
                  <a href="#" onClick={e=>e.preventDefault()} className="ap-fcomment__user">{c.by}</a>
                  <span className="ap-fcomment__work">
                    к {c.workType === "tune" ? "мелодии" : c.workType === "prod" ? "программе" : "графике"}{" "}
                    <a href="#" onClick={e=>e.preventDefault()}>«{c.workTitle}»</a>
                    {c.role && <span className="ap-fcomment__role"> · {c.role}</span>}
                  </span>
                  <span className="ap-fcomment__date">{c.date}</span>
                </div>
                <div className="ap-fcomment__body">{c.body}</div>
              </article>
            ))}
          </div>
        </div>
        <div className="ap-feed__col">
          <div className="ap-collab__col-h"><AP_I name="star" size={14}/>История голосов <span className="ap-section__count">{votes.length}</span></div>
          <div className="ap-feed__votes">
            {votes.map(v => (
              <div key={v.id} className="ap-fvote">
                <span className="ap-fvote__date">{v.date.slice(5)}</span>
                <a href="#" onClick={e=>e.preventDefault()} className="ap-fvote__user">{v.by}</a>
                <span className="ap-fvote__stars" title={`${v.score}/5`}>
                  {Array.from({length:5}).map((_,i) => (
                    <span key={i} className={i < v.score ? "on" : "off"}>★</span>
                  ))}
                </span>
                <span className="ap-fvote__work">
                  → <a href="#" onClick={e=>e.preventDefault()}>«{v.workTitle}»</a>
                </span>
                <span className="ap-fvote__type">{v.workType === "tune" ? "муз." : v.workType === "prod" ? "прог." : "гр."}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}

/* ──────────────────────────────────────────────────────────────────────────
   GUESTBOOK / WALL — комментарии не к работам, а к самому автору.
   Форма ввода свёрнута до однострочного триггера (как в других местах),
   раскрывается по клику. Используем те же rp-add / rp-comment стили,
   что и на странице релиза, чтобы не плодить нового.
   ────────────────────────────────────────────────────────────────────────── */
function AuthorWall({ entries, authorHandle }) {
  const [expanded, setExpanded] = useState2(false);
  const [text, setText] = useState2("");
  return (
    <section className="ap-wall">
      <div className="ap-section__h">
        <h2>Комментарии автору</h2>
        <span className="ap-section__hint">сообщения для <b>{authorHandle}</b> — не к конкретной работе, а ему лично</span>
        <span className="ap-section__count" style={{ marginLeft: "auto" }}>{entries.length}</span>
      </div>

      {/* Collapsed-by-default composer */}
      <div className={"rp-add ap-wall__add" + (expanded ? " ap-wall__add--open" : "")}>
        {!expanded ? (
          <button
            type="button"
            className="ap-wall__trigger"
            onClick={()=>setExpanded(true)}
            aria-label="Написать комментарий автору"
          >
            <AP_I name="chat" size={14}/>
            <span>Написать {authorHandle}…</span>
          </button>
        ) : (
          <>
            <div className="rp-add__h">Сообщение автору</div>
            <textarea
              autoFocus
              placeholder={"Напишите " + authorHandle + " — он увидит уведомление. Сюда обычно благодарят, задают вопросы по работам или зовут на пати."}
              value={text}
              onChange={e => setText(e.target.value)}
            />
            <div className="rp-add__foot">
              <span className="rp-add__hint">Markdown · виден всем посетителям профиля</span>
              <div className="zx-button-controls zx-button-controls--align-end">
                <button
                  className="zx-button zx-button--transparent zx-button--sm"
                  type="button"
                  onClick={()=>{ setExpanded(false); setText(""); }}
                >Отмена</button>
                <button
                  className="zx-button zx-button--primary zx-button--sm"
                  type="button"
                  disabled={!text.trim()}
                  onClick={()=>{ setExpanded(false); setText(""); }}
                >Отправить</button>
              </div>
            </div>
          </>
        )}
      </div>

      {/* Existing entries */}
      {entries.length > 0 ? (
        <div className="ap-wall__list">
          {entries.map(c => (
            <div key={c.id} className="rp-comment">
              <div className="rp-comment__head">
                <a href="#" onClick={e=>e.preventDefault()} className="rp-comment__user" style={{ color: "inherit", textDecoration: "none" }}>{c.by}</a>
                <span className="rp-comment__date">{c.date}</span>
              </div>
              <div className="rp-comment__body">{c.body}</div>
            </div>
          ))}
        </div>
      ) : (
        <p className="rp-comment--empty">Будьте первым, кто напишет {authorHandle}.</p>
      )}
    </section>
  );
}

window.AuthorWall = AuthorWall;

/* ──────────────────────────────────────────────────────────────────────────
   PAGE SHELL
   ────────────────────────────────────────────────────────────────────────── */
function AuthorPage({ preset = "moroz" }) {
  const data = useMemo2(() => buildAuthorData(preset), [preset]);
  const { profile, pictures, tunes, prods, collaborators, collabGroups, comments, votes, wall } = data;
  const letter = profile.handle[0].toUpperCase();
  const LETTERS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ#".split("");

  const counters = {
    pictures: pictures.length,
    tunes: tunes.length,
    prods: prods.length,
    comments: comments.length || profile.counters.comments,
  };

  const isEmpty = pictures.length + tunes.length + prods.length === 0;

  const worksRef = React.useRef(null);
  const [pendingTab, setPendingTab] = useState2(null);
  const jumpToTab = (key) => {
    setPendingTab(key);
    /* defer to next paint so the tab change has rendered */
    requestAnimationFrame(() => {
      worksRef.current?.scrollIntoView({ behavior: "smooth", block: "start" });
    });
  };

  return (
    <div className="ap-root">
      {/* breadcrumbs */}
      <nav className="ap-crumbs" aria-label="breadcrumb">
        <a href="#">Главная</a> <span className="sep">/</span>{" "}
        <a href="#">Авторы</a> <span className="sep">/</span>{" "}
        <a href="#">{letter}</a> <span className="sep">/</span>{" "}
        <span className="here">{profile.handle}</span>
      </nav>

      <div className="ap-letters">
        {LETTERS.map(L => (
          <a key={L} href="#" className={L === letter ? "ap-letters__on" : ""} onClick={e=>e.preventDefault()}>{L}</a>
        ))}
      </div>

      <AuthorHeader profile={profile} counters={counters} totalRatings={profile.ratings}/>

      {!isEmpty && (
        <MiniDashboard pictures={pictures} tunes={tunes} prods={prods} authorHandle={profile.handle} onJumpToTab={jumpToTab}/>
      )}

      <div ref={worksRef}>
        <WorksNavigator pictures={pictures} tunes={tunes} prods={prods} authorHandle={profile.handle} initialTab={pendingTab}/>
      </div>

      <Collaborators people={collaborators} groups={collabGroups}/>

      <FeedColumns comments={comments} votes={votes}/>

      <AuthorWall entries={wall} authorHandle={profile.handle}/>

      {isEmpty && (
        <section className="ap-empty-cta">
          <h3>У этого автора пока нет работ в архиве</h3>
          <p>Если вы знаете его произведения — поделитесь файлом, мы добавим в каталог.</p>
          <div className="ap-empty-cta__actions">
            <button className="zx-button zx-button--primary zx-button--sm">Прислать материал</button>
            <button className="zx-button zx-button--outlined zx-button--sm">Дополнить профиль</button>
          </div>
        </section>
      )}
    </div>
  );
}

window.AuthorPage = AuthorPage;
window.WorksNavigator = WorksNavigator;
window.Collaborators = Collaborators;
window.FeedColumns = FeedColumns;
