/* @ds-bundle: {"format":4,"namespace":"ZXArtDesignSystem_019de4","components":[{"name":"ZxBadge","sourcePath":"components/zx-badge/ZxBadge.jsx"},{"name":"ZxButton","sourcePath":"components/zx-button/ZxButton.jsx"},{"name":"ZxMedal","sourcePath":"components/zx-medal/ZxMedal.jsx"},{"name":"ZxStars","sourcePath":"components/zx-stars/ZxStars.jsx"}],"sourceHashes":{"components/zx-badge/ZxBadge.jsx":"b72f501ebc46","components/zx-button/ZxButton.jsx":"89745f686cae","components/zx-medal/ZxMedal.jsx":"828d352de409","components/zx-stars/ZxStars.jsx":"6a1e300200c4","geo/geo-app.js":"d6b306be2b28","geo/geo-data.js":"9aa59ccd0020","stats/stats-app.js":"7a70306e6c53","stats/stats-data.js":"eeb8d2c175d4","ui_kits/website/App.jsx":"ea215cf427e6","ui_kits/website/AuthorPage.jsx":"ab7220de021f","ui_kits/website/AuthorPageWorks.jsx":"98351af1b54b","ui_kits/website/GroupPage.jsx":"bd004b5f669c","ui_kits/website/GroupPageWorks.jsx":"5b32d93b145d","ui_kits/website/Header.jsx":"d8e415e221a7","ui_kits/website/HomeScreen.jsx":"f19d87c92d2d","ui_kits/website/Icon.jsx":"b3c69b8745d2","ui_kits/website/MusicScreen.jsx":"bbfe5eef2349","ui_kits/website/Oscilloscope.jsx":"100aaaa1c239","ui_kits/website/PartyPage.jsx":"512280e03f75","ui_kits/website/PartyPageWorks.jsx":"a38b1e233481","ui_kits/website/PictureCard.jsx":"ec3f5f5c128c","ui_kits/website/PictureDetail.jsx":"cccdc7cff7a7","ui_kits/website/PicturePage.jsx":"84a287ff8550","ui_kits/website/PicturesScreen.jsx":"e27291bdb76a","ui_kits/website/Player.jsx":"89280fc88c40","ui_kits/website/Primitives.jsx":"3647aae7da72","ui_kits/website/ProdCard.jsx":"1d735f30e7e5","ui_kits/website/ProdPage.jsx":"564a11c5d57a","ui_kits/website/ProdPageMobile.jsx":"835dff64fb3e","ui_kits/website/ReleasePage.jsx":"ed0bd944ef27","ui_kits/website/TunePage.jsx":"3882e6faf1a8","ui_kits/website/VariantA.jsx":"f70a0b166c86","ui_kits/website/VariantB.jsx":"9becb3ed6887","ui_kits/website/VoteWidget.jsx":"595be2fc75ff","ui_kits/website/ZxScreen.jsx":"0c1538efee51","ui_kits/website/author-data.jsx":"60f1be538af2","ui_kits/website/data.jsx":"1aa1677cc8a2","ui_kits/website/design-canvas.jsx":"5d0e39003628","ui_kits/website/group-data.jsx":"d2b9e06f3630","ui_kits/website/ios-frame.jsx":"d67eb3ffe562","ui_kits/website/party-data.jsx":"99ba138cb994","ui_kits/website/picture-data.jsx":"3ffe977d1765","ui_kits/website/prod-data.jsx":"79a65d2685ec","ui_kits/website/release-data.jsx":"4f3a19e04be8","ui_kits/website/tune-data.jsx":"93aeca54ae34","ui_kits/website/tweaks-panel.jsx":"82c387552588"},"inlinedExternals":[],"unexposedExports":[]} */

(() => {

const __ds_ns = (window.ZXArtDesignSystem_019de4 = window.ZXArtDesignSystem_019de4 || {});

const __ds_scope = {};

(__ds_ns.__errors = __ds_ns.__errors || []);

// components/zx-badge/ZxBadge.jsx
try { (() => {
/** zx-badge — small status label. */
function ZxBadge({
  children,
  variant = 'secondary'
}) {
  return React.createElement('span', {
    className: 'zx-badge zx-badge--' + variant
  }, children);
}
Object.assign(__ds_scope, { ZxBadge });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/zx-badge/ZxBadge.jsx", error: String((e && e.message) || e) }); }

// components/zx-button/ZxButton.jsx
try { (() => {
/** zx-button — port of ng-zxart zx-button.component. Renders <a> when href is given. */
function ZxButton({
  children,
  size = 'md',
  variant = 'primary',
  shape,
  disabled = false,
  href,
  onClick,
  ariaLabel,
  style
}) {
  const classes = ['zx-button', 'zx-button--' + size, 'zx-button--' + variant, shape ? 'zx-button--' + shape : ''].filter(Boolean).join(' ');
  const Tag = href ? 'a' : 'button';
  return React.createElement(Tag, {
    className: classes,
    href,
    onClick: e => {
      if (!href && e && e.preventDefault) e.preventDefault();
      onClick && onClick(e);
    },
    disabled,
    'aria-label': ariaLabel,
    style
  }, children);
}
Object.assign(__ds_scope, { ZxButton });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/zx-button/ZxButton.jsx", error: String((e && e.message) || e) }); }

// components/zx-medal/ZxMedal.jsx
try { (() => {
/** zx-medal — circular compo placing (1 gold / 2 silver / 3 bronze). */
function ZxMedal({
  place = 1
}) {
  const klass = place === 1 ? 'gold' : place === 2 ? 'silver' : 'bronze';
  return React.createElement('span', {
    className: 'zx-medal zx-medal--' + klass
  }, place);
}
Object.assign(__ds_scope, { ZxMedal });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/zx-medal/ZxMedal.jsx", error: String((e && e.message) || e) }); }

// components/zx-stars/ZxStars.jsx
try { (() => {
const PATH = 'M12 2l3.09 6.26 6.91 1-5 4.87L18.18 22 12 18.27 5.82 22l1.18-7.87-5-4.87 6.91-1z';
/** zx-stars — 5-star vote strip with optional voter count. */
function ZxStars({
  value = 0,
  count = 0
}) {
  return React.createElement('span', {
    className: 'zx-vote'
  }, [1, 2, 3, 4, 5].map(i => React.createElement('svg', {
    key: i,
    className: 'star' + (i > value ? ' star--off' : ''),
    viewBox: '0 0 24 24',
    width: 14,
    height: 14,
    fill: 'currentColor'
  }, React.createElement('path', {
    d: PATH
  }))), count > 0 ? React.createElement('span', {
    className: 'count',
    style: {
      fontSize: 11,
      color: 'var(--text-light-color)',
      marginLeft: 4
    }
  }, count) : null);
}
Object.assign(__ds_scope, { ZxStars });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/zx-stars/ZxStars.jsx", error: String((e && e.message) || e) }); }

// geo/geo-app.js
try { (() => {
/* ZXArt geo prototype — app logic.
   Rule: zoom changes DETAIL only. Country/city are chosen by explicit click. */

const PAGE = 50;
const CITY_ZOOM = 5; // at/above this, the map paints city aggregates
const ENTITY_ZOOM = 7; // at/above this, the NO-filter panel lists entities for visible cities
const TILE = "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png";
const TILE_OPTS = {
  attribution: '© OpenStreetMap · © CARTO',
  subdomains: 'abcd',
  maxZoom: 18
};
const state = {
  filter: null,
  // null | {kind:'country', country} | {kind:'city', city}
  layers: {
    a: true,
    g: true,
    p: true
  },
  tab: null,
  // 'cities' | 'a' | 'g' | 'p'
  page: 1,
  query: "",
  sort: "name"
};

/* ---------- helpers ---------- */
const $ = s => document.querySelector(s);
function sum(o) {
  return (state.layers.a ? o.a : 0) + (state.layers.g ? o.g : 0) + (state.layers.p ? o.p : 0);
}
function fmt(n) {
  return n.toLocaleString("ru-RU");
}
function pin(count, kind, size) {
  const s = size || (count >= 1000 ? 52 : count >= 100 ? 42 : count >= 10 ? 34 : 28);
  const cls = "geo-pin" + (kind === "city" ? " city" : "");
  return L.divIcon({
    className: "",
    html: `<div class="${cls}" style="width:${s}px;height:${s}px">${count}</div>`,
    iconSize: [s, s],
    iconAnchor: [s / 2, s / 2]
  });
}

/* ---------- map ---------- */
const map = L.map("map", {
  zoomControl: true,
  worldCopyJump: true,
  minZoom: 2
}).setView([40, 15], 3);
L.tileLayer(TILE, TILE_OPTS).addTo(map);
let markers = {}; // key -> leaflet marker
let rowEls = {}; // key -> panel row element (for hover sync)

map.on("zoomend moveend", () => {
  if (!state.filter) renderAll();else renderMapOnly();
  updateZoomNote();
});
function updateZoomNote() {
  const z = map.getZoom();
  let mode;
  if (state.filter) mode = state.filter.kind === "city" ? "город" : "города страны";else mode = z < CITY_ZOOM ? "страны" : z < ENTITY_ZOOM ? "города" : "авторы · группы · пати";
  $("#zoomnote").innerHTML = `зум <b>${z}</b> · на карте: <b>${mode}</b>`;
}

/* ---------- map rendering ---------- */
function clearMarkers() {
  Object.values(markers).forEach(m => map.removeLayer(m));
  markers = {};
}
function renderMapOnly() {
  clearMarkers();
  if (state.filter && state.filter.kind === "city") {
    const c = state.filter.city;
    addMarker(c.key, c.ll, sum(c), "city", true);
  } else if (state.filter && state.filter.kind === "country") {
    state.filter.country.cities.forEach(c => {
      if (sum(c) > 0) addMarker(c.key, c.ll, sum(c), "city");
    });
  } else if (map.getZoom() < CITY_ZOOM) {
    COUNTRIES.forEach(c => {
      if (sum(c) > 0) addMarker(c.code, c.center, sum(c), "country");
    });
  } else {
    const b = map.getBounds();
    COUNTRIES.forEach(co => co.cities.forEach(c => {
      if (sum(c) > 0 && b.contains(c.ll)) addMarker(c.key, c.ll, sum(c), "city");
    }));
  }
}
function addMarker(key, ll, count, kind, selected) {
  const mk = L.marker(ll, {
    icon: pin(count, kind)
  }).addTo(map);
  markers[key] = mk;
  const el = () => mk._icon && mk._icon.querySelector(".geo-pin");
  if (selected && el()) el().classList.add("sel");
  mk.on("mouseover", () => {
    el() && el().classList.add("hot");
    rowEls[key] && rowEls[key].classList.add("hot");
  });
  mk.on("mouseout", () => {
    el() && el().classList.remove("hot");
    rowEls[key] && rowEls[key].classList.remove("hot");
  });
  mk.on("click", () => {
    if (kind === "country") selectCountry(COUNTRIES.find(c => c.code === key));else selectCity(CITY_INDEX[key]);
  });
}

/* ---------- selection ---------- */
function selectCountry(country) {
  state.filter = {
    kind: "country",
    country
  };
  state.tab = "cities";
  state.page = 1;
  state.query = "";
  state.sort = "name";
  const pts = country.cities.map(c => c.ll);
  map.flyToBounds(L.latLngBounds(pts).pad(0.25), {
    duration: 0.6,
    maxZoom: 7
  });
  renderAll();
}
function selectCity(city) {
  state.filter = {
    kind: "city",
    city
  };
  state.tab = "a";
  state.page = 1;
  state.query = "";
  state.sort = "rate";
  map.flyTo(city.ll, Math.max(map.getZoom(), 8), {
    duration: 0.6
  });
  renderAll();
}
function clearFilter() {
  state.filter = null;
  state.tab = null;
  renderAll();
}

/* ---------- panel: header ---------- */
function renderHeader() {
  const top = $("#pheadtop");
  if (state.filter) {
    const name = state.filter.kind === "city" ? state.filter.city.name : state.filter.country.name;
    const sub = state.filter.kind === "city" ? state.filter.city.country : "страна";
    top.innerHTML = `<div><div class="phead__eyebrow">Активный фильтр · ${sub}</div>
        <span class="filterchip">${name}<button class="x" id="clearf" title="Снять фильтр">×</button></span></div>`;
    $("#clearf").onclick = clearFilter;
  } else {
    const detail = map.getZoom() >= ENTITY_ZOOM;
    top.innerHTML = `<div><div class="phead__eyebrow">Без фильтра</div><h2>${detail ? "В этой области" : "На карте сейчас"}</h2></div>`;
  }
}

/* ---------- panel: body ---------- */
function renderBody() {
  const dyn = $("#dyn");
  if (!state.filter) {
    if (map.getZoom() >= ENTITY_ZOOM) {
      dyn.innerHTML = entityPanel(visibleCities(), {
        citiesTab: true,
        viewport: true
      });
      wireScope();
      return;
    }
    dyn.innerHTML = viewportView();
    wireRows();
    return;
  }
  if (state.filter.kind === "country") {
    dyn.innerHTML = entityPanel(state.filter.country.cities, {
      citiesTab: true
    });
    wireScope();
    return;
  }
  dyn.innerHTML = entityPanel([state.filter.city], {
    citiesTab: false
  });
  wireScope();
}

/* --- no filter: "on map now" + viewport list --- */
function viewportView() {
  const b = map.getBounds();
  const countryLevel = map.getZoom() < CITY_ZOOM;
  let countriesIn = 0,
    citiesIn = 0,
    A = 0,
    G = 0,
    P = 0;
  const rows = [];
  COUNTRIES.forEach(co => {
    let cHit = false;
    co.cities.forEach(c => {
      if (b.contains(c.ll)) {
        citiesIn++;
        A += c.a;
        G += c.g;
        P += c.p;
        cHit = true;
      }
    });
    if (cHit) countriesIn++;
  });
  const now = `<div class="nowbar">
    <div class="lbl">В видимой области</div>
    <div class="nowgrid">
      <div class="nowcell"><div class="v">${countryLevel ? countriesIn : citiesIn}</div><div class="k">${countryLevel ? "Страны" : "Города"}</div></div>
      <div class="nowcell a"><div class="v">${fmt(A)}</div><div class="k">Авторы</div></div>
      <div class="nowcell g"><div class="v">${fmt(G)}</div><div class="k">Группы</div></div>
      <div class="nowcell p"><div class="v">${fmt(P)}</div><div class="k">Демопати</div></div>
    </div></div>`;
  let listHtml, total;
  if (countryLevel) {
    const list = COUNTRIES.filter(c => sum(c) > 0).sort((a, b2) => sum(b2) - sum(a));
    total = list.length;
    listHtml = list.map(c => placeRow(c.code, c.name, c, "country")).join("");
  } else {
    const list = [];
    COUNTRIES.forEach(co => co.cities.forEach(c => {
      if (sum(c) > 0 && b.contains(c.ll)) list.push(c);
    }));
    list.sort((a, b2) => sum(b2) - sum(a));
    total = list.length;
    listHtml = list.length ? list.map(c => placeRow(c.key, c.name, c, "city")).join("") : `<div class="empty">Нет городов в видимой области.<br>Отдалите карту или сдвиньте её.</div>`;
  }
  return now + `<div class="hint"><span class="dot"></span>Клик по строке или метке — включить фильтр</div>
     <div class="body" id="scroll">
       <div class="listhead"><span class="ttl">${countryLevel ? "Страны · по активности" : "Города · по активности"}</span><span class="tot">${total}</span></div>
       ${listHtml}
     </div>`;
}
function placeRow(key, name, o, kind) {
  const a = state.layers.a ? `<span class="a" title="авторы">${o.a}</span>` : "";
  const g = state.layers.g ? `<span class="g" title="группы">${o.g}</span>` : "";
  const p = state.layers.p && o.p ? `<span class="p" title="демопати">${o.p}</span>` : "";
  return `<div class="prow" data-key="${key}" data-kind="${kind}">
      <span class="nm">${name}</span><span class="bd">${a}${g}${p}</span><span class="chev">›</span></div>`;
}

/* --- cities currently inside the map viewport (no-filter, high zoom) --- */
function visibleCities() {
  const b = map.getBounds();
  const out = [];
  COUNTRIES.forEach(co => co.cities.forEach(c => {
    if (sum(c) > 0 && b.contains(c.ll)) out.push(c);
  }));
  return out;
}
function plural(n, one, few, many) {
  const a = n % 10,
    b = n % 100;
  if (a === 1 && b !== 11) return one;
  if (a >= 2 && a <= 4 && (b < 10 || b >= 20)) return few;
  return many;
}

/* --- gather entity lists across a set of cities (city-level seeds → consistent on drill-down) --- */
function gatherEntities(cities) {
  let authors = [],
    groups = [],
    parties = [],
    a = 0,
    g = 0,
    p = 0;
  cities.forEach(c => {
    authors = authors.concat(genAuthors(c.key, c.a).map(e => ({
      ...e,
      city: c.name
    })));
    groups = groups.concat(genGroups(c.key, c.g).map(e => ({
      ...e,
      city: c.name
    })));
    parties = parties.concat(genParties(c.key, c.p).map(e => ({
      ...e,
      city: c.name
    })));
    a += c.a;
    g += c.g;
    p += c.p;
  });
  return {
    authors,
    groups,
    parties,
    a,
    g,
    p
  };
}

/* --- unified tabbed entity panel: country filter / city filter / no-filter high zoom --- */
function entityPanel(cities, opts) {
  opts = opts || {};
  if (opts.viewport && !cities.length) return `<div class="empty">В кадре нет городов с данными.<br>Сдвиньте карту или немного отдалите.</div>`;
  const ent = gatherEntities(cities);
  const authors = ent.authors,
    groups = ent.groups,
    parties = ent.parties;
  const multi = cities.length > 1;
  const entKeys = ["a", "g", "p"].filter(k => state.layers[k]);
  if (!(opts.citiesTab && state.tab === "cities") && !entKeys.includes(state.tab)) state.tab = entKeys[0] || "a";
  const chips = `<div class="nowbar">${opts.viewport ? `<div class="lbl">В видимой области · ${cities.length} ${plural(cities.length, "город", "города", "городов")}</div>` : ""}<div class="nowgrid">
      <div class="nowcell a"><div class="v">${fmt(ent.a)}</div><div class="k">Авторы</div></div>
      <div class="nowcell g"><div class="v">${fmt(ent.g)}</div><div class="k">Группы</div></div>
      <div class="nowcell p"><div class="v">${fmt(ent.p)}</div><div class="k">Демопати</div></div>
    </div></div>`;
  let tabsHtml = "";
  if (opts.citiesTab) tabsHtml += `<button class="tab ${state.tab === "cities" ? "active" : ""}" data-tab="cities">Города<span class="num">${cities.length}</span></button>`;
  if (state.layers.a) tabsHtml += `<button class="tab ${state.tab === "a" ? "active" : ""}" data-tab="a">Авторы<span class="num">${fmt(authors.length)}</span></button>`;
  if (state.layers.g) tabsHtml += `<button class="tab ${state.tab === "g" ? "active" : ""}" data-tab="g">Группы<span class="num">${fmt(groups.length)}</span></button>`;
  if (state.layers.p) tabsHtml += `<button class="tab ${state.tab === "p" ? "active" : ""}" data-tab="p">Демопати<span class="num">${fmt(parties.length)}</span></button>`;
  const tabs = `<div class="tabs">${tabsHtml}</div>`;
  if (state.tab === "cities") {
    const list = cities.slice().sort((a, b) => a.name.localeCompare(b.name, "ru"));
    return chips + tabs + `<div class="body" id="scroll"><div class="listhead"><span class="ttl">Города · A→Я</span><span class="tot">${cities.length}</span></div>` + list.map(c => placeRow(c.key, c.name, c, "city")).join("") + `</div>`;
  }

  // entity tab — toolbar + paginated list
  const toolbar = `<div class="toolbar">
      <div class="sbox" id="esbox"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.7.7l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0A4.5 4.5 0 1 1 14 9.5 4.5 4.5 0 0 1 9.5 14z"/></svg>
        <input id="esearch" placeholder="Поиск в списке…" value="${state.query.replace(/"/g, '&quot;')}" autocomplete="off"></div>
      <select class="sortsel" id="esort">
        <option value="name" ${state.sort === "name" ? "selected" : ""}>А→Я</option>
        <option value="rate" ${state.sort === "rate" ? "selected" : ""}>По рейтингу</option>
      </select></div>`;
  let arr = state.tab === "a" ? authors : state.tab === "g" ? groups : parties;
  const q = state.query.trim().toLowerCase();
  if (q) arr = arr.filter(e => (state.tab === "a" ? e.nick + " " + e.real + " " + e.city : e.name + " " + e.city).toLowerCase().includes(q));
  arr = arr.slice().sort((x, y) => {
    if (state.sort === "rate") {
      const rx = state.tab === "a" ? Math.max(+x.music || 0, +x.gfx || 0) : state.tab === "g" ? x.works : x.entries;
      const ry = state.tab === "a" ? Math.max(+y.music || 0, +y.gfx || 0) : state.tab === "g" ? y.works : y.entries;
      return ry - rx;
    }
    return (state.tab === "a" ? x.nick : x.name).localeCompare(state.tab === "a" ? y.nick : y.name, "ru");
  });
  const pages = Math.max(1, Math.ceil(arr.length / PAGE));
  if (state.page > pages) state.page = pages;
  if (state.page < 1) state.page = 1;
  const start = (state.page - 1) * PAGE;
  const shown = arr.slice(start, start + PAGE);
  let listHtml;
  if (!arr.length) {
    listHtml = `<div class="empty">Ничего не найдено.</div>`;
  } else if (state.tab === "a") {
    listHtml = `<table class="auth"><thead><tr><th></th><th>Никнейм</th>${multi ? "<th>Город</th>" : ""}<th>Группа</th><th style="text-align:right">Music</th><th style="text-align:right">Gfx</th></tr></thead><tbody>` + shown.map((e, i) => `<tr><td class="idx">${start + i + 1}</td>
        <td><div class="nick">${e.nick}</div>${e.real ? `<div class="real">${e.real}</div>` : ""}</td>
        ${multi ? `<td class="real">${e.city}</td>` : ""}
        <td>${e.group || "<span style='color:var(--secondary-300)'>—</span>"}</td>
        ${e.music ? `<td class="rate"><span class="star">★</span> ${e.music}</td>` : `<td class="rate empty">—</td>`}
        ${e.gfx ? `<td class="rate"><span class="star">★</span> ${e.gfx}</td>` : `<td class="rate empty">—</td>`}</tr>`).join("") + `</tbody></table>`;
  } else if (state.tab === "g") {
    listHtml = `<div class="ents">` + shown.map(e => `<div class="ent g">
        <div class="ic">${e.name[0]}</div><div class="mn"><div class="t">${e.name}</div>
        <div class="s">${e.years} · ${e.members} уч.${multi ? " · " + e.city : ""}</div></div><div class="rt">${e.works} работ</div></div>`).join("") + `</div>`;
  } else {
    listHtml = `<div class="ents">` + shown.map(e => `<div class="ent p">
        <div class="ic">★</div><div class="mn"><div class="t">${e.name}</div>
        <div class="s">${e.year} · ${e.compos} компо${multi ? " · " + e.city : ""}</div></div><div class="rt">${e.entries} работ</div></div>`).join("") + `</div>`;
  }
  const head = `<div class="listhead"><span class="ttl">${state.tab === "a" ? "Авторы" : state.tab === "g" ? "Группы" : "Демопати"}</span><span class="tot">${fmt(arr.length)}</span></div>`;
  const pager = renderPager(arr.length, start, shown.length, pages);
  return chips + tabs + toolbar + `<div class="body" id="scroll">${head}${listHtml}</div>${pager}`;
}

/* numbered pagination: ‹ 1 … 4 5 6 … 21 › + range */
function renderPager(total, start, count, pages) {
  if (total <= PAGE) return "";
  const cur = state.page;
  const nums = [];
  const add = p => nums.push(`<button class="pg ${p === cur ? "cur" : ""}" data-pg="${p}">${p}</button>`);
  let lo = Math.max(1, cur - 2),
    hi = Math.min(pages, cur + 2);
  if (cur <= 3) hi = Math.min(pages, 5);
  if (cur >= pages - 2) lo = Math.max(1, pages - 4);
  if (lo > 1) {
    add(1);
    if (lo > 2) nums.push(`<span class="pgdots">…</span>`);
  }
  for (let p = lo; p <= hi; p++) add(p);
  if (hi < pages) {
    if (hi < pages - 1) nums.push(`<span class="pgdots">…</span>`);
    add(pages);
  }
  return `<div class="pager">
     <button class="pg nav" data-pg="${cur - 1}" ${cur === 1 ? "disabled" : ""}>‹</button>
     ${nums.join("")}
     <button class="pg nav" data-pg="${cur + 1}" ${cur === pages ? "disabled" : ""}>›</button>
     <span class="pgrange">${start + 1}–${start + count} / ${fmt(total)}</span>
   </div>`;
}
function firstOn() {
  return state.layers.a ? "a" : state.layers.g ? "g" : state.layers.p ? "p" : "a";
}

/* ---------- wiring ---------- */
function wireRows() {
  rowEls = {};
  document.querySelectorAll(".prow").forEach(row => {
    const key = row.dataset.key,
      kind = row.dataset.kind;
    rowEls[key] = row;
    const mk = markers[key];
    row.addEventListener("mouseenter", () => {
      row.classList.add("hot");
      const e = mk && mk._icon && mk._icon.querySelector(".geo-pin");
      e && e.classList.add("hot");
    });
    row.addEventListener("mouseleave", () => {
      row.classList.remove("hot");
      const e = mk && mk._icon && mk._icon.querySelector(".geo-pin");
      e && e.classList.remove("hot");
    });
    row.addEventListener("click", () => {
      if (kind === "country") selectCountry(COUNTRIES.find(c => c.code === key));else selectCity(CITY_INDEX[key]);
    });
  });
}
function wireScope() {
  wireRows(); // city rows inside country "cities" tab
  document.querySelectorAll(".tab").forEach(t => t.addEventListener("click", () => {
    state.tab = t.dataset.tab;
    state.page = 1;
    state.query = "";
    renderBody();
  }));
  const es = $("#esearch");
  if (es) {
    es.addEventListener("input", () => {
      state.query = es.value;
      state.page = 1;
      renderBody();
      const n = $("#esearch");
      if (n) {
        n.focus();
        n.selectionStart = n.selectionEnd = n.value.length;
      }
    });
    es.parentElement.addEventListener("focusin", () => es.parentElement.classList.add("focused"));
    es.parentElement.addEventListener("focusout", () => es.parentElement.classList.remove("focused"));
  }
  const so = $("#esort");
  if (so) so.addEventListener("change", () => {
    state.sort = so.value;
    state.page = 1;
    renderBody();
  });
  document.querySelectorAll(".pg[data-pg]").forEach(b => b.addEventListener("click", () => {
    if (b.hasAttribute("disabled")) return;
    const p = +b.dataset.pg;
    if (p >= 1) {
      state.page = p;
      renderBody();
      const sc = $("#scroll");
      if (sc) sc.scrollTop = 0;
    }
  }));
}

/* ---------- layers ---------- */
document.querySelectorAll(".layer").forEach(l => l.addEventListener("click", () => {
  const k = l.dataset.k;
  const on = Object.values(state.layers).filter(Boolean).length;
  if (state.layers[k] && on === 1) return; // keep at least one layer on
  state.layers[k] = !state.layers[k];
  l.classList.toggle("on", state.layers[k]);
  renderAll();
}));

/* ---------- map search + autocomplete ---------- */
const sInput = $("#search"),
  ac = $("#ac"),
  sbox = $("#sbox");
sInput.addEventListener("focus", () => sbox.classList.add("focused"));
sInput.addEventListener("blur", () => {
  sbox.classList.remove("focused");
  setTimeout(() => ac.innerHTML = "", 150);
});
sInput.addEventListener("input", () => {
  const q = sInput.value.trim().toLowerCase();
  if (q.length < 1) {
    ac.innerHTML = "";
    return;
  }
  const cities = [],
    countries = [],
    authors = [];
  COUNTRIES.forEach(co => {
    if (co.name.toLowerCase().includes(q)) countries.push(co);
    co.cities.forEach(c => {
      if (c.name.toLowerCase().includes(q)) cities.push(c);
    });
  });
  // author hits — scan a few cities' generated lists for a believable demo
  COUNTRIES.slice(0, 4).forEach(co => co.cities.slice(0, 3).forEach(c => {
    genAuthors(c.key, c.a).slice(0, 40).forEach(au => {
      if (authors.length < 5 && au.nick.toLowerCase().includes(q)) authors.push({
        au,
        c
      });
    });
  }));
  let html = "";
  if (cities.length) html += `<div class="ac__group"><div class="ac__glabel">Города</div>` + cities.slice(0, 6).map(c => `<div class="ac__row" data-city="${c.key}"><svg class="ac__ico" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 14.5 9 2.5 2.5 0 0 1 12 11.5z"/></svg><span class="ac__txt">${hl(c.name, q)} · <span class="ac__sub">${c.country}</span></span><span class="ac__cnt">${c.a}</span></div>`).join("") + `</div>`;
  if (countries.length) html += `<div class="ac__group"><div class="ac__glabel">Страны</div>` + countries.slice(0, 6).map(co => `<div class="ac__row" data-country="${co.code}"><svg class="ac__ico" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm6.93 6h-2.95a15.7 15.7 0 0 0-1.38-3.56A8 8 0 0 1 18.93 8z"/></svg><span class="ac__txt">${hl(co.name, q)}</span><span class="ac__cnt">${co.a}</span></div>`).join("") + `</div>`;
  if (authors.length) html += `<div class="ac__group"><div class="ac__glabel">Авторы</div>` + authors.map(({
    au,
    c
  }) => `<div class="ac__row" data-city="${c.key}"><svg class="ac__ico" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z"/></svg><span class="ac__txt">${hl(au.nick, q)} · <span class="ac__sub">${c.name}, ${c.country}</span></span></div>`).join("") + `</div>`;
  ac.innerHTML = html ? `<div class="ac">${html}</div>` : "";
  ac.querySelectorAll(".ac__row").forEach(r => r.addEventListener("mousedown", e => {
    e.preventDefault();
    sInput.value = "";
    ac.innerHTML = "";
    if (r.dataset.city) selectCity(CITY_INDEX[r.dataset.city]);else selectCountry(COUNTRIES.find(c => c.code === r.dataset.country));
  }));
});
function hl(text, q) {
  const i = text.toLowerCase().indexOf(q);
  if (i < 0) return text;
  return text.slice(0, i) + "<b>" + text.slice(i, i + q.length) + "</b>" + text.slice(i + q.length);
}

/* ---------- reset ---------- */
$("#reset").addEventListener("click", () => {
  clearFilter();
  map.flyTo([40, 15], 3, {
    duration: 0.6
  });
});

/* ---------- master render ---------- */
function renderAll() {
  renderMapOnly();
  renderHeader();
  renderBody();
  $("#reset").style.display = state.filter || map.getZoom() > 3 ? "block" : "none";
  updateZoomNote();
}
setTimeout(() => {
  map.invalidateSize();
  renderAll();
}, 120);
})(); } catch (e) { __ds_ns.__errors.push({ path: "geo/geo-app.js", error: String((e && e.message) || e) }); }

// geo/geo-data.js
try { (() => {
/* ZXArt geo prototype — demo data + deterministic entity generator.
   City-level precision only (matches real ZXArt data). Counts are illustrative. */

/* country: name, code, capital center [lat,lng], cities[]
   city:    [name, lat, lng, authors]  (groups/parties derived) */
const RAW = [["Россия", "ru", [55.75, 37.62], [["Москва", 55.75, 37.62, 1028], ["Санкт-Петербург", 59.94, 30.31, 266], ["Екатеринбург", 56.83, 60.60, 270], ["Самара", 53.20, 50.15, 253], ["Новосибирск", 55.03, 82.92, 205], ["Нижний Новгород", 56.30, 43.99, 42], ["Ростов-на-Дону", 47.23, 39.70, 33], ["Омск", 54.99, 73.37, 23], ["Мурманск", 68.97, 33.08, 22], ["Казань", 55.79, 49.12, 20], ["Калининград", 54.71, 20.51, 20], ["Владивосток", 43.11, 131.87, 20], ["Уфа", 54.74, 55.97, 12], ["Архангельск", 64.54, 40.51, 9]]], ["Великобритания", "gb", [51.51, -0.13], [["Лондон", 51.51, -0.13, 3160], ["Манчестер", 53.48, -2.24, 420], ["Бирмингем", 52.49, -1.89, 300], ["Глазго", 55.86, -4.25, 250], ["Лидс", 53.80, -1.55, 180], ["Бристоль", 51.45, -2.59, 120]]], ["Испания", "es", [40.42, -3.70], [["Мадрид", 40.42, -3.70, 610], ["Барселона", 41.39, 2.16, 520], ["Валенсия", 39.47, -0.38, 210], ["Севилья", 37.39, -5.99, 130]]], ["Чехия", "cz", [50.08, 14.44], [["Прага", 50.08, 14.44, 240], ["Брно", 49.20, 16.61, 110], ["Острава", 49.84, 18.29, 55]]], ["Украина", "ua", [50.45, 30.52], [["Киев", 50.45, 30.52, 182], ["Харьков", 49.99, 36.23, 92], ["Львов", 49.84, 24.03, 44], ["Одесса", 46.48, 30.72, 38]]], ["Польша", "pl", [52.23, 21.01], [["Варшава", 52.23, 21.01, 150], ["Краков", 50.06, 19.94, 92], ["Вроцлав", 51.11, 17.04, 60], ["Лодзь", 51.76, 19.46, 40]]], ["Германия", "de", [52.52, 13.40], [["Берлин", 52.52, 13.40, 140], ["Гамбург", 53.55, 9.99, 70], ["Мюнхен", 48.14, 11.58, 62], ["Кёльн", 50.94, 6.96, 40]]], ["Беларусь", "by", [53.90, 27.56], [["Минск", 53.90, 27.56, 150], ["Гомель", 52.42, 31.01, 30], ["Брест", 52.10, 23.73, 18]]], ["Нидерланды", "nl", [52.37, 4.90], [["Амстердам", 52.37, 4.90, 62], ["Роттердам", 51.92, 4.48, 30], ["Утрехт", 52.09, 5.12, 20]]], ["Италия", "it", [41.90, 12.50], [["Рим", 41.90, 12.50, 90], ["Милан", 45.46, 9.19, 70], ["Турин", 45.07, 7.69, 40]]], ["Финляндия", "fi", [60.17, 24.94], [["Хельсинки", 60.17, 24.94, 80], ["Тампере", 61.50, 23.79, 24]]], ["Швеция", "se", [59.33, 18.06], [["Стокгольм", 59.33, 18.06, 40], ["Гётеборг", 57.71, 11.97, 16]]], ["США", "us", [40.71, -74.01], [["Нью-Йорк", 40.71, -74.01, 60], ["Лос-Анджелес", 34.05, -118.24, 40], ["Чикаго", 41.88, -87.63, 25]]], ["Австралия", "au", [-33.87, 151.21], [["Сидней", -33.87, 151.21, 18], ["Мельбурн", -37.81, 144.96, 12]]], ["Аргентина", "ar", [-34.60, -58.38], [["Буэнос-Айрес", -34.60, -58.38, 28]]], ["Бразилия", "br", [-23.55, -46.63], [["Сан-Паулу", -23.55, -46.63, 33]]]];

/* ---- deterministic RNG so generated lists are stable across renders ---- */
function hashStr(s) {
  let h = 2166136261;
  for (let i = 0; i < s.length; i++) {
    h ^= s.charCodeAt(i);
    h = Math.imul(h, 16777619);
  }
  return h >>> 0;
}
function mulberry32(a) {
  return function () {
    a |= 0;
    a = a + 0x6D2B79F5 | 0;
    let t = Math.imul(a ^ a >>> 15, 1 | a);
    t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t;
    return ((t ^ t >>> 14) >>> 0) / 4294967296;
  };
}
const NICKS = ["Casio", "Ra", "Msa", "Lasoft", "Dimka", "Lion", "Abk", "Kompleks", "Panther", "Shark", "Lds", "Proha", "zksystem", "Diver", "Megus", "Nyuk", "Karbofos", "Shiru", "Introspec", "DMX", "Mast", "Vinnny", "Quiet", "Goblin", "Random", "Skull", "Voxel", "Pixel", "Raster", "Sprite", "Octane", "Flux", "Echo", "Cyber", "Nik", "Riskej", "Alone Coder", "Hacker", "Mod7", "Tristar", "Wbc", "Excess", "Sage", "Gluk", "Bfox", "Scor", "Nortexx", "Tiboh", "Wlodek", "Hally"];
const FIRST = ["Игорь", "Дмитрий", "Александр", "Сергей", "Андрей", "Евгений", "Руслан", "Максим", "Павел", "Николай", "Олег", "Виктор", "Артём", "Денис", "Роман", "Константин", "Марат", "Амир"];
const LAST = ["Петрунин", "Насыров", "Лёвин", "Никифоров", "Зуйков", "Антонов", "Лисенков", "Плясунов", "Сухов", "Иванов", "Кузнецов", "Смирнов", "Попов", "Волков", "Морозов", "Соколов", "Нагимов"];
const GROUPS = ["Excess Team", "Hooy-Program", "Triebkraft", "Skrju", "Antares", "Power of Sound", "Eternity Industry", "Thesuper", "Digital Reality", "Avalon", "Brainwave", "Phantasy", "Extreme", "Sage", "Sindikat", "Placebo", "Joker", "X-Trade", "Progress", "Sands", "Razzlers", "Code Busters", "Crymax", "Speccy.pl", "Mayhem"];
const PARTIES = ["Chaos Constructions", "DiHalt", "Multimatograf", "Forever", "Millennium", "Outline", "CAFe", "Antarctic", "ArtField", "Demodulation", "Insomnia", "Sundown", "Revision", "Function"];
function pick(rng, arr) {
  return arr[Math.floor(rng() * arr.length)];
}
function maybeRate(rng) {
  return rng() > 0.55 ? (rng() * 30).toFixed(2) : "";
}
const _cache = {};
function genAuthors(seed, n) {
  if (_cache["a:" + seed]) return _cache["a:" + seed];
  const rng = mulberry32(hashStr("a" + seed));
  const out = [];
  for (let i = 0; i < n; i++) {
    let nick = NICKS[i % NICKS.length];
    if (i >= NICKS.length) nick += " " + (Math.floor(i / NICKS.length) + 1);
    const real = rng() > 0.42 ? pick(rng, FIRST) + " " + pick(rng, LAST) : "";
    const group = rng() > 0.5 ? pick(rng, GROUPS) : "";
    out.push({
      nick,
      real,
      group,
      music: maybeRate(rng),
      gfx: maybeRate(rng)
    });
  }
  _cache["a:" + seed] = out;
  return out;
}
function genGroups(seed, n) {
  if (_cache["g:" + seed]) return _cache["g:" + seed];
  const rng = mulberry32(hashStr("g" + seed));
  const out = [];
  for (let i = 0; i < n; i++) {
    let name = GROUPS[i % GROUPS.length];
    if (i >= GROUPS.length) name += " " + (Math.floor(i / GROUPS.length) + 1);
    const y0 = 1990 + Math.floor(rng() * 12);
    const y1 = y0 + Math.floor(rng() * 16);
    out.push({
      name,
      works: 2 + Math.floor(rng() * 46),
      years: y0 + "–" + y1,
      members: 1 + Math.floor(rng() * 7)
    });
  }
  _cache["g:" + seed] = out;
  return out;
}
function genParties(seed, n) {
  if (_cache["p:" + seed]) return _cache["p:" + seed];
  const rng = mulberry32(hashStr("p" + seed));
  const out = [];
  for (let i = 0; i < n; i++) {
    let name = PARTIES[i % PARTIES.length];
    if (i >= PARTIES.length) name += " " + (Math.floor(i / PARTIES.length) + 1);
    out.push({
      name,
      year: 1996 + Math.floor(rng() * 28),
      compos: 1 + Math.floor(rng() * 5),
      entries: 5 + Math.floor(rng() * 120)
    });
  }
  _cache["p:" + seed] = out;
  return out;
}

/* ---- build structured model with derived counts ---- */
const COUNTRIES = RAW.map(([name, code, center, cityRows]) => {
  const cities = cityRows.map(([cn, lat, lng, a]) => {
    const g = Math.max(1, Math.round(a * 0.10));
    const p = a > 200 ? 2 : a > 40 ? 1 : a > 12 ? 1 : 0;
    return {
      name: cn,
      key: code + "|" + cn,
      ll: [lat, lng],
      a,
      g,
      p,
      country: name,
      countryCode: code
    };
  });
  const sum = k => cities.reduce((s, c) => s + c[k], 0);
  return {
    name,
    code,
    center,
    cities,
    a: sum("a"),
    g: sum("g"),
    p: sum("p")
  };
});
const CITY_INDEX = {};
COUNTRIES.forEach(c => c.cities.forEach(ct => {
  CITY_INDEX[ct.key] = ct;
}));
})(); } catch (e) { __ds_ns.__errors.push({ path: "geo/geo-data.js", error: String((e && e.message) || e) }); }

// stats/stats-app.js
try { (() => {
/* ZXArt statistics prototype — rendering. Vanilla, no deps. */
(function () {
  const S = window.STATS;
  const $ = s => document.querySelector(s);
  const fmt = n => Number(n).toLocaleString("ru-RU");
  const el = (t, c, h) => {
    const e = document.createElement(t);
    if (c) e.className = c;
    if (h != null) e.innerHTML = h;
    return e;
  };
  const sum = a => a.reduce((x, y) => x + Number(y), 0);
  const peakYear = s => s.years[s.all.indexOf(Math.max(...s.all))];

  /* ---------- inline icons ---------- */
  const IC = {
    soft: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"></path></svg>',
    music: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6z"></path></svg>',
    gfx: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"></path></svg>',
    users: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"></path></svg>',
    disc: '<svg viewBox="0 0 547.74 547.74" fill="currentColor"><path d="M273.87,345.132c39.293,0,71.262-31.969,71.262-71.262c0-39.293-31.969-71.261-71.262-71.261c-39.293,0-71.261,31.968-71.261,71.261C202.609,313.163,234.577,345.132,273.87,345.132z M273.87,228.735c24.927,0,45.135,20.208,45.135,45.135s-20.208,45.135-45.135,45.135s-45.135-20.208-45.135-45.135S248.943,228.735,273.87,228.735z"></path><path d="M273.87,547.74c151.256,0,273.87-122.617,273.87-273.87S425.126,0,273.87,0S0,122.617,0,273.87S122.614,547.74,273.87,547.74z M273.87,187.309c47.729,0,86.562,38.832,86.562,86.562c0,47.729-38.832,86.562-86.562,86.562c-47.73,0-86.562-38.832-86.562-86.562C187.309,226.14,226.14,187.309,273.87,187.309z"></path></svg>',
    list: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"></path></svg>',
    star: '<svg viewBox="32.9 34 369.75 351.78" fill="currentColor"><path d="M 332.25647,385.51933 L 217.94322,325.58331 L 103.76342,385.77314 L 125.44132,258.53357 L 32.91382,168.54182 L 160.62472,149.83945 L 217.61942,34.031678 L 274.87122,149.71255 L 402.62329,168.13113 L 310.29602,258.32822 L 332.25647,385.51933 z"></path></svg>',
    check: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"></path></svg>'
  };

  /* ---------- tooltip ---------- */
  const tip = $("#tip");
  function showTip(html, x, y) {
    tip.innerHTML = html;
    tip.style.opacity = "1";
    const w = tip.offsetWidth,
      h = tip.offsetHeight;
    let left = x + 14,
      top = y - h - 12;
    if (left + w > window.innerWidth - 8) left = x - w - 14;
    if (top < 8) top = y + 18;
    tip.style.left = left + "px";
    tip.style.top = top + "px";
  }
  const hideTip = () => {
    tip.style.opacity = "0";
  };

  /* ---------- user cell ---------- */
  function usr(u) {
    const badge = u.tier === "vip" ? `<span class="badge vip" title="VIP">${IC.star}</span>` : u.tier === "vol" ? `<span class="badge vol" title="Волонтёр">${IC.check}</span>` : "";
    const name = u.url ? `<a class="nm" href="${u.url}" target="_blank" rel="noopener">${u.name}</a>` : `<span class="nm plain">${u.name}</span>`;
    return `<span class="usr">${badge}${name}</span>`;
  }

  /* ---------- top table ---------- */
  function topTable(rows, unit) {
    const t = el("div", "tablewrap");
    let body = "";
    rows.forEach((u, i) => {
      const rk = i < 3 ? `<span class="medal m${i + 1}">${i + 1}</span>` : `<span class="rnum">${i + 1}</span>`;
      body += `<tr><td class="rk">${rk}</td><td>${usr(u)}</td><td class="cnt">${fmt(u.n)}</td></tr>`;
    });
    t.innerHTML = `<table class="tops"><thead><tr><th></th><th>Участник</th><th class="r">${unit}</th></tr></thead><tbody>${body}</tbody></table>`;
    return t;
  }

  /* ---------- year bar chart (total + above-average overlay) ---------- */
  function yearChart(s, noun) {
    const max = Math.max(...s.all);
    const wrap = el("div", "chart");
    wrap.appendChild(el("div", "ymax", "макс " + fmt(max)));
    const bars = el("div", "bars");
    s.years.forEach((yr, i) => {
      const total = s.all[i],
        rated = s.rated[i];
      const b = el("div", "bar");
      const colH = Math.max(total / max * 100, total > 0 ? 1.5 : 0);
      const fillH = total > 0 ? rated / total * 100 : 0;
      b.innerHTML = `<div class="col" style="height:${colH}%"><div class="fill" style="height:${fillH}%"></div></div>`;
      b.addEventListener("mouseenter", e => {
        b.classList.add("hot");
        showTip(`<div class="tt">${yr}</div>
          <div class="r"><span class="lab">всего ${noun}</span><span class="all">${fmt(total)}</span></div>
          <div class="r"><span class="lab">выше среднего</span><span class="rated">${fmt(rated)}</span></div>`, e.clientX, e.clientY);
      });
      b.addEventListener("mousemove", e => showTip(tip.innerHTML, e.clientX, e.clientY));
      b.addEventListener("mouseleave", () => {
        b.classList.remove("hot");
        hideTip();
      });
      bars.appendChild(b);
    });
    wrap.appendChild(bars);
    wrap.appendChild(yearAxis(s.years));
    return wrap;
  }

  /* shared year axis: label every 5 years + endpoints */
  function yearAxis(years) {
    const axis = el("div", "axis");
    years.forEach((yr, i) => {
      const show = yr % 5 === 0 || i === 0 || i === years.length - 1;
      axis.appendChild(el("div", "t", show ? "’" + String(yr).slice(2) : ""));
    });
    return axis;
  }

  /* ---------- average-rating line chart (scale 2.5–5 ★) ---------- */
  function lineChart(years, values) {
    const W = 1000,
      H = 170,
      padT = 16,
      padB = 6,
      yMin = 2.5,
      yMax = 5,
      plotH = H - padT - padB;
    const xx = i => years.length > 1 ? i / (years.length - 1) * W : W / 2;
    const yy = v => padT + (1 - (Math.max(yMin, Math.min(yMax, v)) - yMin) / (yMax - yMin)) * plotH;
    const pts = values.map((v, i) => [xx(i), yy(v)]);
    const ln = pts.map((p, i) => (i ? "L" : "M") + p[0].toFixed(1) + " " + p[1].toFixed(1)).join(" ");
    const area = "M" + pts[0][0].toFixed(1) + " " + (H - padB) + " " + pts.map(p => "L" + p[0].toFixed(1) + " " + p[1].toFixed(1)).join(" ") + " L" + pts[pts.length - 1][0].toFixed(1) + " " + (H - padB) + " Z";
    const grid = [3, 4, 5].map(g => `<line x1="0" y1="${yy(g).toFixed(1)}" x2="${W}" y2="${yy(g).toFixed(1)}"></line>`).join("");
    const wrap = el("div", "chart");
    const plot = el("div", "lc-plot");
    plot.innerHTML = `<svg class="lc" viewBox="0 0 ${W} ${H}" preserveAspectRatio="none"><g class="grid">${grid}</g><path class="area" d="${area}"></path><path class="ln" d="${ln}"></path></svg><div class="lc-dot"></div>`;
    const dot = plot.querySelector(".lc-dot");
    const hit = el("div", "lc-hit");
    years.forEach((yr, i) => {
      const c = el("div", "hit");
      const v = values[i];
      const show = e => {
        dot.style.left = xx(i) / W * 100 + "%";
        dot.style.top = yy(v) + "px";
        dot.style.opacity = "1";
        showTip(`<div class="tt">${yr}</div><div class="r"><span class="lab">средняя оценка</span><span class="all">${v.toFixed(1).replace(".", ",")} ★</span></div>`, e.clientX, e.clientY);
      };
      c.addEventListener("mouseenter", show);
      c.addEventListener("mousemove", show);
      c.addEventListener("mouseleave", () => {
        dot.style.opacity = "0";
        hideTip();
      });
      hit.appendChild(c);
    });
    plot.appendChild(hit);
    wrap.appendChild(plot);
    wrap.appendChild(yearAxis(years));
    return wrap;
  }

  /* ---------- 100% stacked distribution by year ---------- */
  function distLegend(classes) {
    const l = el("div", "legend wrap");
    l.innerHTML = classes.map(c => `<span><i style="background:${c.color}"></i>${c.name}</span>`).join("");
    return l;
  }
  function stackedChart(years, dist) {
    const wrap = el("div", "chart");
    const bars = el("div", "bars stacked");
    years.forEach((yr, i) => {
      const col = el("div", "scol");
      dist.classes.forEach((c, ci) => {
        const seg = el("div", "sseg");
        seg.style.height = dist.rows[i][ci] * 100 + "%";
        seg.style.background = c.color;
        col.appendChild(seg);
      });
      const tip = `<div class="tt">${yr}</div>` + dist.classes.map((c, ci) => `<div class="r"><span class="lab"><i style="background:${c.color}"></i>${c.name}</span><span>${Math.round(dist.rows[i][ci] * 100)}%</span></div>`).join("");
      const show = e => showTip(tip, e.clientX, e.clientY);
      col.addEventListener("mouseenter", e => {
        col.classList.add("hot");
        show(e);
      });
      col.addEventListener("mousemove", show);
      col.addEventListener("mouseleave", () => {
        col.classList.remove("hot");
        hideTip();
      });
      bars.appendChild(col);
    });
    wrap.appendChild(bars);
    wrap.appendChild(yearAxis(years));
    return wrap;
  }

  /* ---------- daily bar chart ---------- */
  function dailyChart(dates, data, label) {
    const max = Math.max(...data.map(Number));
    const wrap = el("div", "chart");
    wrap.appendChild(el("div", "ymax", "макс " + fmt(max)));
    const bars = el("div", "bars daily");
    data.forEach((v, i) => {
      const n = Number(v);
      const b = el("div", "bar solid");
      const h = Math.max(n / max * 100, n > 0 ? 1.5 : 0);
      b.innerHTML = `<div class="col" style="height:${h}%"></div>`;
      b.addEventListener("mouseenter", e => {
        b.classList.add("hot");
        showTip(`<div class="tt">${dates[i]}</div>
          <div class="r"><span class="lab">${label}</span><span class="all">${fmt(n)}</span></div>`, e.clientX, e.clientY);
      });
      b.addEventListener("mousemove", e => showTip(tip.innerHTML, e.clientX, e.clientY));
      b.addEventListener("mouseleave", () => {
        b.classList.remove("hot");
        hideTip();
      });
      bars.appendChild(b);
    });
    wrap.appendChild(bars);
    const axis = el("div", "axis");
    dates.forEach((d, i) => {
      const show = i % 5 === 0 || i === dates.length - 1;
      axis.appendChild(el("div", "t", show ? d.slice(0, 5) : ""));
    });
    wrap.appendChild(axis);
    return wrap;
  }

  /* ---------- builders ---------- */
  function panel(title, meta, bodyNodes) {
    const p = el("div", "panel");
    const h = el("div", "panel__h");
    h.appendChild(el("h2", null, title));
    if (meta) {
      const m = el("div", "meta");
      if (typeof meta === "string") m.innerHTML = meta;else m.appendChild(meta);
      h.appendChild(m);
    }
    p.appendChild(h);
    const b = el("div", "panel__b");
    (Array.isArray(bodyNodes) ? bodyNodes : [bodyNodes]).forEach(n => b.appendChild(typeof n === "string" ? el("div", null, n) : n));
    p.appendChild(b);
    return p;
  }
  function legend(kind) {
    const l = el("div", "legend");
    if (kind === "year") l.innerHTML = `<span><i class="l-all"></i>всего в базе</span><span><i class="l-rated"></i>с оценкой выше средней</span>`;else l.innerHTML = `<span><i class="l-daily"></i>${kind}</span>`;
    return l;
  }
  function subkpis(items) {
    const w = el("div", "subkpis");
    items.forEach(it => {
      const c = el("div", "subkpi");
      c.innerHTML = `<div class="v">${it.v}</div><div class="k">${it.k}</div>`;
      w.appendChild(c);
    });
    return w;
  }

  /* ---------- category section (soft/music/gfx) ---------- */
  function workSection(cfg) {
    const s = cfg.data,
      sec = el("section", "sec");
    sec.dataset.cat = cfg.key;
    sec.appendChild(subkpis([{
      v: fmt(sum(s.all)),
      k: "Всего " + cfg.genitive
    }, {
      v: peakYear(s),
      k: "Пиковый год"
    }, {
      v: fmt(sum(cfg.daily)),
      k: cfg.dailyLabel + " · 30 дней"
    }]));
    sec.appendChild(panel(cfg.yearTitle, legend("year"), yearChart(s, cfg.noun)));
    sec.appendChild(panel("Средняя оценка по годам", "шкала 2,5–5,0 ★", lineChart(s.years, cfg.avg)));
    (cfg.stacks || []).forEach(st => sec.appendChild(panel(st.title, null, [distLegend(st.dist.classes), stackedChart(s.years, st.dist)])));
    sec.appendChild(panel(cfg.dailyTitle, legend(cfg.dailyLabel.toLowerCase()), dailyChart(S.daily.dates, cfg.daily, cfg.dailyLabel.toLowerCase())));
    sec.appendChild(panel(cfg.topTitle, fmt(cfg.top.length) + " участников", topTable(cfg.top, cfg.topUnit)));
    return sec;
  }

  /* ---------- users section ---------- */
  function usersSection() {
    const sec = el("section", "sec");
    sec.dataset.cat = "users";
    sec.appendChild(subkpis([{
      v: fmt(S.tops.voters[0].n),
      k: "Голосов у лидера"
    }, {
      v: fmt(S.tops.comments[0].n),
      k: "Комментариев у лидера"
    }, {
      v: fmt(S.tops.tags[0].n),
      k: "Тегов у лидера"
    }]));
    const grid = el("div", "ugrid");
    grid.appendChild(panel("Больше всего голосовали", fmt(S.tops.voters.length), topTable(S.tops.voters, "голосов")));
    grid.appendChild(panel("Больше всех комментировали", fmt(S.tops.comments.length), topTable(S.tops.comments, "коммент.")));
    grid.appendChild(panel("Больше всех добавили тегов", fmt(S.tops.tags.length), topTable(S.tops.tags, "тегов")));
    sec.appendChild(grid);
    // comments history (empty in source)
    const emptyNote = el("div", "empty", "Нет данных по комментариям за период.");
    sec.appendChild(panel("Комментарии · 30 дней", null, emptyNote));
    return sec;
  }

  /* ---------- categories ---------- */
  const CATS = [{
    key: "soft",
    label: "Софт",
    icon: IC.soft,
    count: () => fmt(sum(S.prods.all)),
    build: () => workSection({
      key: "soft",
      data: S.prods,
      noun: "прог.",
      genitive: "программ",
      yearTitle: "Программы по годам",
      avg: S.dist.avg.prods,
      stacks: [{
        title: "Компьютер по годам",
        dist: S.dist.softComputer
      }, {
        title: "Категория по годам",
        dist: S.dist.softCategory
      }],
      daily: S.daily.uploads,
      dailyLabel: "Закачки",
      dailyTitle: "Закачки · 30 дней",
      top: S.tops.soft,
      topTitle: "Больше всего загрузили программ",
      topUnit: "прог."
    })
  }, {
    key: "music",
    label: "Музыка",
    icon: IC.music,
    count: () => fmt(sum(S.music.all)),
    build: () => workSection({
      key: "music",
      data: S.music,
      noun: "мел.",
      genitive: "мелодий",
      yearTitle: "Мелодии по годам",
      avg: S.dist.avg.music,
      stacks: [{
        title: "Формат по годам",
        dist: S.dist.musicFormat
      }],
      daily: S.daily.plays,
      dailyLabel: "Прослушивания",
      dailyTitle: "Прослушивания · 30 дней",
      top: S.tops.music,
      topTitle: "Больше всех добавили музыки",
      topUnit: "мелодий"
    })
  }, {
    key: "gfx",
    label: "Графика",
    icon: IC.gfx,
    count: () => fmt(sum(S.pics.all)),
    build: () => workSection({
      key: "gfx",
      data: S.pics,
      noun: "карт.",
      genitive: "картинок",
      yearTitle: "Картинки по годам",
      avg: S.dist.avg.pics,
      stacks: [{
        title: "Техника по годам",
        dist: S.dist.gfxFormat
      }],
      daily: S.daily.views,
      dailyLabel: "Просмотры",
      dailyTitle: "Просмотры · 30 дней",
      top: S.tops.gfx,
      topTitle: "Больше всех добавили графики",
      topUnit: "картинок"
    })
  }, {
    key: "users",
    label: "Пользователи",
    icon: IC.users,
    count: () => fmt(S.tops.voters.length + S.tops.comments.length + S.tops.tags.length),
    build: usersSection
  }];

  /* ---------- top KPI strip ---------- */
  function buildKpis() {
    const k = $("#kpis");
    const cards = [{
      ic: IC.soft,
      v: fmt(sum(S.prods.all)),
      kk: "Программ"
    }, {
      ic: IC.disc,
      v: "24 137",
      kk: "Релизов программ"
    }, {
      ic: IC.users,
      v: '6 842<span class="sl">/</span>9 215',
      kk: "Авторы / с альясами"
    }, {
      ic: IC.list,
      v: '1 530<span class="sl">/</span>1 884',
      kk: "Группы / с альясами"
    }, {
      ic: IC.music,
      v: fmt(sum(S.music.all)),
      kk: "Мелодий"
    }, {
      ic: IC.gfx,
      v: fmt(sum(S.pics.all)),
      kk: "Картинок"
    }];
    cards.forEach(c => {
      const e = el("div", "kpi");
      e.innerHTML = `<div class="kpi__ic">${c.ic}</div><div class="kpi__v">${c.v}</div><div class="kpi__k">${c.kk}</div>` + (c.sub ? `<div class="kpi__sub">${c.sub}</div>` : "");
      k.appendChild(e);
    });
  }

  /* ---------- tabs + sections ---------- */
  function build() {
    buildKpis();
    const cats = $("#cats"),
      sections = $("#sections");
    CATS.forEach((c, i) => {
      const btn = el("button", "cat" + (i === 0 ? " active" : ""));
      btn.innerHTML = `${c.icon}${c.label}`;
      btn.addEventListener("click", () => select(c.key));
      btn.dataset.cat = c.key;
      cats.appendChild(btn);
      const sec = c.build();
      if (i === 0) sec.classList.add("active");
      sections.appendChild(sec);
    });
  }
  function select(key) {
    document.querySelectorAll(".cat").forEach(b => b.classList.toggle("active", b.dataset.cat === key));
    document.querySelectorAll(".sec").forEach(s => s.classList.toggle("active", s.dataset.cat === key));
    hideTip();
  }

  /* ---------- theme ---------- */
  const TKEY = "zxart-stats-theme";
  function applyTheme(t) {
    document.documentElement.classList.toggle("dark-mode", t === "dark");
    document.documentElement.classList.toggle("light-mode", t !== "dark");
  }
  (function initTheme() {
    let t = "light";
    try {
      t = localStorage.getItem(TKEY) || "light";
    } catch (e) {}
    applyTheme(t);
    $("#theme").addEventListener("click", () => {
      t = document.documentElement.classList.contains("dark-mode") ? "light" : "dark";
      applyTheme(t);
      try {
        localStorage.setItem(TKEY, t);
      } catch (e) {}
    });
  })();
  build();
})();
})(); } catch (e) { __ds_ns.__errors.push({ path: "stats/stats-app.js", error: String((e && e.message) || e) }); }

// stats/stats-data.js
try { (() => {
/* ZXArt statistics prototype — data.
   Transcribed verbatim from the legacy stats page (chartsData + top tables).
   tier: 'vip' | 'vol' | ''   url: author page or null */
window.STATS = {
  prods: {
    years: [1905, 1980, 1981, 1982, 1983, 1984, 1985, 1986, 1987, 1988, 1989, 1990, 1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998, 1999, 2000, 2001, 2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026],
    all: [1, 1, 165, 1122, 3625, 3898, 3425, 2219, 1775, 1437, 1363, 1234, 1145, 1182, 1270, 1625, 2037, 2080, 1911, 1260, 1003, 592, 374, 343, 306, 256, 227, 189, 195, 308, 228, 378, 383, 382, 380, 469, 449, 420, 423, 435, 500, 549, 537, 473, 505, 460, 487, 123],
    rated: [1, 1, 162, 904, 2625, 2979, 2950, 1928, 1568, 1289, 1259, 1151, 1107, 1146, 1251, 1606, 2023, 2049, 1890, 1244, 991, 584, 370, 336, 304, 254, 226, 187, 192, 304, 227, 375, 383, 375, 371, 462, 443, 407, 401, 406, 479, 521, 528, 454, 478, 439, 463, 120]
  },
  pics: {
    years: [1982, 1983, 1984, 1985, 1986, 1987, 1988, 1989, 1990, 1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998, 1999, 2000, 2001, 2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026],
    all: [2, 40, 140, 242, 356, 518, 515, 597, 613, 411, 263, 146, 246, 408, 409, 832, 539, 453, 481, 342, 260, 215, 138, 83, 100, 166, 208, 185, 331, 219, 250, 328, 548, 448, 456, 475, 471, 383, 438, 627, 969, 968, 701, 854, 217],
    rated: [1, 24, 91, 178, 260, 393, 393, 472, 518, 323, 195, 108, 136, 303, 256, 462, 287, 232, 205, 161, 126, 130, 81, 45, 58, 92, 127, 105, 182, 137, 168, 215, 415, 330, 321, 345, 384, 326, 356, 547, 735, 769, 674, 839, 200]
  },
  music: {
    years: [1983, 1984, 1985, 1986, 1987, 1988, 1989, 1990, 1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998, 1999, 2000, 2001, 2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026],
    all: [6, 50, 121, 195, 480, 451, 508, 349, 324, 95, 168, 612, 1575, 1754, 1852, 1562, 1618, 1430, 907, 933, 517, 325, 262, 339, 149, 63, 121, 137, 77, 64, 105, 218, 174, 160, 191, 252, 181, 145, 150, 137, 211, 155, 216, 66],
    rated: [2, 15, 70, 127, 320, 408, 433, 300, 299, 90, 125, 560, 1496, 1674, 1778, 1526, 1574, 1398, 893, 911, 510, 318, 258, 335, 143, 63, 118, 128, 76, 62, 99, 195, 163, 147, 186, 231, 179, 141, 149, 135, 200, 148, 183, 66]
  },
  daily: {
    dates: ["21.05.2026", "22.05.2026", "23.05.2026", "24.05.2026", "25.05.2026", "26.05.2026", "27.05.2026", "28.05.2026", "29.05.2026", "30.05.2026", "31.05.2026", "01.06.2026", "02.06.2026", "03.06.2026", "04.06.2026", "05.06.2026", "06.06.2026", "07.06.2026", "08.06.2026", "09.06.2026", "10.06.2026", "11.06.2026", "12.06.2026", "13.06.2026", "14.06.2026", "15.06.2026", "16.06.2026", "17.06.2026", "18.06.2026", "19.06.2026"],
    views: [6580, 5948, 3509, 2098, 1221, 1902, 1259, 3139, 1271, 2006, 3325, 2673, 2816, 16516, 21077, 12834, 232, 289, 384, 223, 315, 522, 267, 219, 199, 523, 367, 276, 243, 279],
    plays: [313, 602, 241, 103, 334, 421, 342, 231, 456, 216, 162, 274, 622, 506, 539, 521, 281, 261, 566, 419, 579, 734, 332, 236, 411, 592, 386, 602, 416, 495],
    uploads: [3, 1, 1, 2, 1, 0, 1, 4, 0, 5, 5, 6, 7, 7, 6, 6, 13, 1, 1, 1, 16, 0, 6, 0, 2, 2, 4, 0, 1, 1],
    comments: []
  },
  tops: {
    gfx: [{
      n: 7967,
      name: "moroz1999",
      tier: "vip",
      url: "https://zxart.ee/rus/avtory/m/moroz1999/"
    }, {
      n: 1708,
      name: "tiboh",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/t/tiboh/"
    }, {
      n: 1228,
      name: "diver",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/d/diver/"
    }, {
      n: 1100,
      name: "Xela",
      tier: "vol",
      url: null
    }, {
      n: 790,
      name: "Grongy",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/g/grongy/"
    }, {
      n: 649,
      name: "vassa",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/v/vassa/"
    }, {
      n: 613,
      name: "Art-top",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/a/art-top/"
    }, {
      n: 522,
      name: "Vinnny",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/v/vinnny/"
    }, {
      n: 492,
      name: "Slider",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/s/slider2/"
    }],
    music: [{
      n: 2745,
      name: "moroz1999",
      tier: "vip",
      url: "https://zxart.ee/rus/avtory/m/moroz1999/"
    }, {
      n: 876,
      name: "tiboh",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/t/tiboh/"
    }, {
      n: 537,
      name: "Vitamin",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/v/vitamin/"
    }, {
      n: 528,
      name: "diver",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/d/diver/"
    }, {
      n: 459,
      name: "tutty",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/t/tutty/"
    }, {
      n: 280,
      name: "n1k-o",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/n/nq/"
    }, {
      n: 218,
      name: "Xela",
      tier: "vol",
      url: null
    }, {
      n: 178,
      name: "breeze",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/b/breeze/"
    }, {
      n: 173,
      name: "Vinnny",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/v/vinnny/"
    }, {
      n: 135,
      name: "wbcbz7",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/w/wbcbz7/"
    }],
    soft: [{
      n: 528,
      name: "Xela",
      tier: "vol",
      url: null
    }, {
      n: 274,
      name: "moroz1999",
      tier: "vip",
      url: "https://zxart.ee/rus/avtory/m/moroz1999/"
    }, {
      n: 140,
      name: "Vinnny",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/v/vinnny/"
    }, {
      n: 45,
      name: "SCjoe",
      tier: "",
      url: null
    }, {
      n: 25,
      name: "G.Nerc=Y.uR",
      tier: "",
      url: null
    }, {
      n: 18,
      name: "n1k-o",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/n/nq/"
    }, {
      n: 16,
      name: "breeze",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/b/breeze/"
    }, {
      n: 16,
      name: "miheichm",
      tier: "",
      url: null
    }, {
      n: 15,
      name: "tiboh",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/t/tiboh/"
    }],
    voters: [{
      n: 104788,
      name: "creator",
      tier: "",
      url: "https://zxart.ee/rus/avtory/c/creator/"
    }, {
      n: 34574,
      name: "moroz1999",
      tier: "vip",
      url: "https://zxart.ee/rus/avtory/m/moroz1999/"
    }, {
      n: 32710,
      name: "tiboh",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/t/tiboh/"
    }, {
      n: 15395,
      name: "Dimidrol",
      tier: "",
      url: "https://zxart.ee/rus/avtory/d/dimidrol/"
    }, {
      n: 13757,
      name: "diver",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/d/diver/"
    }, {
      n: 9756,
      name: "WattTack",
      tier: "",
      url: "https://zxart.ee/rus/avtory/w/watttack/"
    }, {
      n: 6911,
      name: "Ricardo",
      tier: "",
      url: "https://zxart.ee/rus/avtory/letter20332/4throck/"
    }, {
      n: 6730,
      name: "Yuran",
      tier: "",
      url: null
    }, {
      n: 6567,
      name: "Grongy",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/g/grongy/"
    }, {
      n: 5114,
      name: "vassa",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/v/vassa/"
    }, {
      n: 4211,
      name: "scalesmann",
      tier: "",
      url: null
    }, {
      n: 3951,
      name: "tutty",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/t/tutty/"
    }, {
      n: 3771,
      name: "Art-top",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/a/art-top/"
    }, {
      n: 3060,
      name: "Xela",
      tier: "vol",
      url: null
    }, {
      n: 2662,
      name: "KOD",
      tier: "",
      url: null
    }, {
      n: 2481,
      name: "Tzerra",
      tier: "",
      url: "https://zxart.ee/rus/avtory/t/tzerra/"
    }, {
      n: 2398,
      name: "pulsar",
      tier: "",
      url: null
    }, {
      n: 2037,
      name: "nyuk",
      tier: "",
      url: "https://zxart.ee/rus/avtory/n/nyuk/"
    }, {
      n: 1978,
      name: "breeze",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/b/breeze/"
    }, {
      n: 1699,
      name: "MCat78",
      tier: "",
      url: null
    }],
    comments: [{
      n: 3297,
      name: "moroz1999",
      tier: "vip",
      url: "https://zxart.ee/rus/avtory/m/moroz1999/"
    }, {
      n: 824,
      name: "diver",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/d/diver/"
    }, {
      n: 319,
      name: "prof4d",
      tier: "",
      url: "https://zxart.ee/rus/avtory/p/prof4d/"
    }, {
      n: 295,
      name: "breeze",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/b/breeze/"
    }, {
      n: 250,
      name: "Kurashi Nikkeru",
      tier: "",
      url: "https://zxart.ee/rus/avtory/b/brightentayle/"
    }, {
      n: 250,
      name: "aGGreSSor",
      tier: "",
      url: "https://zxart.ee/rus/avtory/a/aggressor/"
    }, {
      n: 227,
      name: "czasnaretro",
      tier: "",
      url: null
    }, {
      n: 211,
      name: "r0bat",
      tier: "",
      url: "https://zxart.ee/rus/avtory/r/r0bat/"
    }, {
      n: 205,
      name: "wbcbz7",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/w/wbcbz7/"
    }, {
      n: 197,
      name: "unbeliever",
      tier: "",
      url: null
    }, {
      n: 191,
      name: "sq",
      tier: "",
      url: null
    }, {
      n: 190,
      name: "Zont",
      tier: "",
      url: "https://zxart.ee/rus/avtory/z/zont/"
    }, {
      n: 186,
      name: "Loopaseen",
      tier: "",
      url: null
    }, {
      n: 181,
      name: "ax34",
      tier: "",
      url: "https://zxart.ee/rus/avtory/a/ax34/"
    }, {
      n: 178,
      name: "VBI",
      tier: "",
      url: null
    }, {
      n: 167,
      name: "karbo",
      tier: "",
      url: null
    }, {
      n: 152,
      name: "tiboh",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/t/tiboh/"
    }, {
      n: 152,
      name: "introspec",
      tier: "",
      url: null
    }, {
      n: 149,
      name: "ЯeAniMaToR",
      tier: "",
      url: null
    }, {
      n: 146,
      name: "n1k-o",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/n/nq/"
    }, {
      n: 134,
      name: "nyuk",
      tier: "",
      url: null
    }, {
      n: 134,
      name: "Grongy",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/g/grongy/"
    }, {
      n: 129,
      name: "Pator",
      tier: "",
      url: "https://zxart.ee/rus/avtory/p/pator/"
    }, {
      n: 128,
      name: "abelenki",
      tier: "",
      url: "https://zxart.ee/rus/avtory/a/anton-belenki/"
    }, {
      n: 121,
      name: "Xela",
      tier: "vol",
      url: null
    }, {
      n: 118,
      name: "Gibson",
      tier: "",
      url: "https://zxart.ee/rus/avtory/g/gibson/"
    }],
    tags: [{
      n: 38965,
      name: "tiboh",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/t/tiboh/"
    }, {
      n: 6403,
      name: "moroz1999",
      tier: "vip",
      url: "https://zxart.ee/rus/avtory/m/moroz1999/"
    }, {
      n: 5975,
      name: "Art-top",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/a/art-top/"
    }, {
      n: 2716,
      name: "vassa",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/v/vassa/"
    }, {
      n: 2359,
      name: "diver",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/d/diver/"
    }, {
      n: 1856,
      name: "NeilParsons",
      tier: "vol",
      url: null
    }, {
      n: 863,
      name: "Vinnny",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/v/vinnny/"
    }, {
      n: 715,
      name: "mch",
      tier: "vol",
      url: "https://zxart.ee/rus/avtory/m/mch1/"
    }, {
      n: 586,
      name: "prof4d",
      tier: "",
      url: "https://zxart.ee/rus/avtory/p/prof4d/"
    }, {
      n: 470,
      name: "Alessandro Grussu",
      tier: "",
      url: null
    }, {
      n: 448,
      name: "ax34",
      tier: "",
      url: "https://zxart.ee/rus/avtory/a/ax34/"
    }, {
      n: 408,
      name: "nodeus",
      tier: "",
      url: "https://zxart.ee/rus/avtory/n/nodeus/"
    }, {
      n: 396,
      name: "helpcomputer0",
      tier: "",
      url: "https://zxart.ee/rus/avtory/h/helpcomputer0/"
    }, {
      n: 355,
      name: "Xela",
      tier: "vol",
      url: null
    }, {
      n: 343,
      name: "Free_Wind",
      tier: "",
      url: "https://zxart.ee/rus/avtory/f/free_wind/"
    }, {
      n: 303,
      name: "Blogerator",
      tier: "",
      url: "https://zxart.ee/rus/avtory/b/blogerator/"
    }, {
      n: 265,
      name: "KOD",
      tier: "",
      url: null
    }, {
      n: 236,
      name: "non@me",
      tier: "",
      url: "https://zxart.ee/rus/avtory/b/buddy/"
    }, {
      n: 192,
      name: "Ricardo",
      tier: "",
      url: "https://zxart.ee/rus/avtory/letter20332/4throck/"
    }]
  }
};

/* ---------- derived mock analytics (avg rating + multicolor distributions) ---------- */
(function () {
  var S = window.STATS;
  function rnd(n) {
    var x = Math.sin(n * 127.1 + 3.7) * 43758.5453;
    return x - Math.floor(x);
  }
  function avgSeries(years) {
    return years.map(function (y, i) {
      var t = years.length > 1 ? i / (years.length - 1) : 0;
      var v = 3.35 + 0.85 * t + 0.18 * Math.sin(i * 0.9) + (rnd(i + years.length) - 0.5) * 0.4;
      return Math.max(2.7, Math.min(4.8, Math.round(v * 10) / 10));
    });
  }
  function stack(years, defs) {
    var T = years.length;
    var rows = years.map(function (y, i) {
      var t = T > 1 ? i / (T - 1) : 0;
      var w = defs.map(function (d, k) {
        return Math.max(0, d.p(t) * (0.8 + 0.4 * rnd(i * 9 + k * 31)));
      });
      var s = w.reduce(function (a, b) {
        return a + b;
      }, 0) || 1;
      return w.map(function (v) {
        return v / s;
      });
    });
    return {
      classes: defs.map(function (d) {
        return {
          name: d.name,
          color: d.color
        };
      }),
      rows: rows
    };
  }
  var P = ['oklch(0.62 0.14 250)', 'oklch(0.66 0.13 152)', 'oklch(0.76 0.13 84)', 'oklch(0.64 0.16 32)', 'oklch(0.56 0.13 300)'];
  S.dist = {
    avg: {
      prods: avgSeries(S.prods.years),
      music: avgSeries(S.music.years),
      pics: avgSeries(S.pics.years)
    },
    softComputer: stack(S.prods.years, [{
      name: "ZX 48K",
      color: P[0],
      p: function (t) {
        return Math.max(0.05, 1.15 - 1.05 * t);
      }
    }, {
      name: "ZX 128K",
      color: P[1],
      p: function (t) {
        return 0.45 + 0.55 * Math.min(1, t * 1.8);
      }
    }, {
      name: "Pentagon",
      color: P[2],
      p: function (t) {
        return 0.95 * Math.exp(-Math.pow((t - 0.52) / 0.26, 2));
      }
    }, {
      name: "ATM / Evo",
      color: P[3],
      p: function (t) {
        return 0.12 + 0.55 * Math.max(0, t - 0.45);
      }
    }, {
      name: "ZX Next",
      color: P[4],
      p: function (t) {
        return t < 0.8 ? 0 : (t - 0.8) / 0.2 * 0.7;
      }
    }]),
    softCategory: stack(S.prods.years, [{
      name: "Демо",
      color: P[0],
      p: function (t) {
        return 0.3 + 0.7 * t;
      }
    }, {
      name: "Игры",
      color: P[1],
      p: function (t) {
        return 0.95 - 0.4 * t;
      }
    }, {
      name: "Интро",
      color: P[2],
      p: function (t) {
        return 0.55 - 0.2 * t;
      }
    }, {
      name: "Пресса",
      color: P[3],
      p: function (t) {
        return 0.5 * Math.exp(-Math.pow((t - 0.55) / 0.24, 2));
      }
    }, {
      name: "Утилиты",
      color: P[4],
      p: function (t) {
        return 0.32 + 0.05 * Math.sin(t * 3);
      }
    }]),
    musicFormat: stack(S.music.years, [{
      name: "AY",
      color: P[0],
      p: function (t) {
        return 0.9;
      }
    }, {
      name: "Beeper",
      color: P[1],
      p: function (t) {
        return Math.max(0.07, 0.55 - 0.8 * t) + (t > 0.72 ? (t - 0.72) * 0.6 : 0);
      }
    }, {
      name: "Turbosound",
      color: P[2],
      p: function (t) {
        return 0.05 + 0.65 * Math.max(0, t - 0.32);
      }
    }, {
      name: "Covox / DAC",
      color: P[3],
      p: function (t) {
        return 0.08 + 0.22 * t;
      }
    }]),
    gfxFormat: stack(S.pics.years, [{
      name: "Standard",
      color: P[0],
      p: function (t) {
        return 1.15 - 0.6 * t;
      }
    }, {
      name: "Multicolor",
      color: P[1],
      p: function (t) {
        return 0.2 + 0.6 * t;
      }
    }, {
      name: "Gigascreen",
      color: P[2],
      p: function (t) {
        return 0.15 + 0.5 * t;
      }
    }, {
      name: "Border FX",
      color: P[3],
      p: function (t) {
        return 0.18 + 0.1 * Math.sin(t * 4);
      }
    }, {
      name: "Realtime",
      color: P[4],
      p: function (t) {
        return t < 0.55 ? 0.03 : (t - 0.55) / 0.45 * 0.5;
      }
    }])
  };
})();
})(); } catch (e) { __ds_ns.__errors.push({ path: "stats/stats-data.js", error: String((e && e.message) || e) }); }

// ui_kits/website/App.jsx
try { (() => {
/** Generic placeholder — for routes that the kit hasn't built out yet. */
const PlaceholderScreen = ({
  name
}) => /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
  className: "crumbs"
}, /*#__PURE__*/React.createElement("a", {
  href: "#"
}, "Home"), /*#__PURE__*/React.createElement("span", {
  className: "sep"
}, "\u203A"), " ", /*#__PURE__*/React.createElement("span", {
  style: {
    textTransform: "capitalize"
  }
}, name)), /*#__PURE__*/React.createElement("div", {
  className: "empty-screen"
}, "\"", name, "\" \u2014 this section isn't part of the UI kit yet."));

/** Top-level app — stitches header, screens, and player. */
const App = () => {
  const [route, setRoute] = React.useState("home");
  const [theme, setTheme] = React.useState("light");
  const [openPicture, setOpenPicture] = React.useState(null);
  const [currentTune, setCurrentTune] = React.useState(null);
  const [isPlaying, setIsPlaying] = React.useState(false);
  React.useEffect(() => {
    document.documentElement.classList.toggle("dark-mode", theme === "dark");
    document.documentElement.classList.toggle("light-mode", theme === "light");
  }, [theme]);
  const handleNavigate = id => {
    setOpenPicture(null);
    setRoute(id);
    window.scrollTo({
      top: 0
    });
  };
  const handleOpenPicture = p => {
    setOpenPicture(p);
    window.scrollTo({
      top: 0
    });
  };
  const handlePlayTune = tune => {
    if (currentTune?.id === tune.id) {
      setIsPlaying(p => !p);
    } else {
      setCurrentTune(tune);
      setIsPlaying(true);
    }
  };
  const handleNext = () => {
    if (!currentTune) return;
    const idx = SAMPLE_TUNES.findIndex(t => t.id === currentTune.id);
    setCurrentTune(SAMPLE_TUNES[(idx + 1) % SAMPLE_TUNES.length]);
    setIsPlaying(true);
  };
  const handlePrev = () => {
    if (!currentTune) return;
    const idx = SAMPLE_TUNES.findIndex(t => t.id === currentTune.id);
    setCurrentTune(SAMPLE_TUNES[(idx - 1 + SAMPLE_TUNES.length) % SAMPLE_TUNES.length]);
    setIsPlaying(true);
  };
  let screen;
  if (openPicture) {
    screen = /*#__PURE__*/React.createElement(PictureDetail, {
      picture: openPicture,
      onBack: () => setOpenPicture(null)
    });
  } else if (route === "home") {
    screen = /*#__PURE__*/React.createElement(HomeScreen, {
      onPlayTune: handlePlayTune,
      currentTuneId: currentTune?.id,
      isPlaying: isPlaying,
      onOpenPicture: handleOpenPicture,
      onNavigate: handleNavigate
    });
  } else if (route === "pictures") {
    screen = /*#__PURE__*/React.createElement(PicturesScreen, {
      onOpenPicture: handleOpenPicture
    });
  } else if (route === "music") {
    screen = /*#__PURE__*/React.createElement(MusicScreen, {
      onPlayTune: handlePlayTune,
      currentTuneId: currentTune?.id,
      isPlaying: isPlaying
    });
  } else {
    screen = /*#__PURE__*/React.createElement(PlaceholderScreen, {
      name: route
    });
  }
  return /*#__PURE__*/React.createElement("div", {
    className: "app-shell"
  }, /*#__PURE__*/React.createElement(Header, {
    active: openPicture ? "pictures" : route,
    onNavigate: handleNavigate,
    theme: theme,
    onToggleTheme: () => setTheme(t => t === "light" ? "dark" : "light")
  }), /*#__PURE__*/React.createElement("main", {
    className: "main"
  }, screen), currentTune && /*#__PURE__*/React.createElement("div", {
    className: "player-spacer"
  }), currentTune && /*#__PURE__*/React.createElement("div", {
    className: "player-fixed"
  }, /*#__PURE__*/React.createElement(Player, {
    tune: currentTune,
    isPlaying: isPlaying,
    onTogglePlay: () => setIsPlaying(p => !p),
    onNext: handleNext,
    onPrev: handlePrev
  })));
};
window.App = App;
ReactDOM.createRoot(document.getElementById("root")).render(/*#__PURE__*/React.createElement(App, null));
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/App.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/AuthorPage.jsx
try { (() => {
/* AuthorPage.jsx — Author page for ZXArt.
   Composes:
   - Header (identity + counters + groups + aliases + links + tech-specs)
   - Mini-dashboard (top works per category)
   - Works navigator (Music / Graphics / Software tabs with smart filters)
   - Collaborators (people + groups)
   - Comments + votes feed (two parallel columns)

   Modes: preset = "moroz" | "newbie", heroStyle = "rich" | "calm"
*/

const {
  useState,
  useMemo
} = React;

/* ── inline svg icon (matches design-system 24x24 paths) ── */
function AP_I({
  name,
  size = 16
}) {
  const p = {
    play: "M8 5v14l11-7z",
    pause: "M6 19h4V5H6v14zm8-14v14h4V5h-4z",
    star: "M12 2l3.09 6.26 6.91 1-5 4.87L18.18 22 12 18.27 5.82 22l1.18-7.87-5-4.87 6.91-1z",
    image: "M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm0 13l4-4 3 3 5-5 3 3V5H5v12z",
    music: "M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6z",
    game: "M21 6H3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2zM11 13H8v3H6v-3H3v-2h3V8h2v3h3v2zm4.5 2a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm4-3a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z",
    code: "M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0L19.2 12l-4.6-4.6L16 6l6 6-6 6-1.4-1.4z",
    chevron: "M16.6 8.6L12 13.2 7.4 8.6 6 10l6 6 6-6z",
    chevronUp: "M7.4 15.4L12 10.8l4.6 4.6L18 14l-6-6-6 6z",
    link: "M3.9 12a4.1 4.1 0 0 1 4.1-4.1h4V6H8a6 6 0 1 0 0 12h4v-1.9H8A4.1 4.1 0 0 1 3.9 12zm5.1 1h6v-2H9v2zm7-7h-4v1.9h4a4.1 4.1 0 0 1 0 8.2h-4V18h4a6 6 0 0 0 0-12z",
    location: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z",
    person: "M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0 2c-3 0-9 1.5-9 4.5V21h18v-2.5c0-3-6-4.5-9-4.5z",
    crown: "M2 5l4 4 6-7 6 7 4-4-2 14H4L2 5z",
    award: "M12 2l2.39 4.84L19.78 8l-3.89 3.79.92 5.4L12 14.77 7.19 17.19l.92-5.4L4.22 8l5.39-1.16L12 2z",
    chat: "M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h11c.55 0 1-.45 1-1z",
    download: "M5 20h14v-2H5v2zm7-18l-5.5 5.5h3.5V14h4V7.5h3.5L12 2z",
    visible: "M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5C21.3 7.6 17 4.5 12 4.5zm0 12.5a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z",
    filter: "M3 5h18l-7 8v6l-4 2v-8L3 5z",
    sort: "M3 18h6v-2H3v2zm0-5h12v-2H3v2zm0-7v2h18V6H3z"
  };
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: "0 0 24 24",
    width: size,
    height: size,
    fill: "currentColor",
    "aria-hidden": "true",
    style: {
      flexShrink: 0
    }
  }, /*#__PURE__*/React.createElement("path", {
    d: p[name] || ""
  }));
}

/* Russian plural helper: pluralRu(5, ["мелодию","мелодии","мелодий"]) */
function pluralRu(n, [one, few, many]) {
  const mod10 = n % 10,
    mod100 = n % 100;
  if (mod10 === 1 && mod100 !== 11) return one;
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return few;
  return many;
}

/* ── pixelized avatar (procedural placeholder for an old scanned photo) ── */
function PixelAvatar({
  seed = 42,
  size = 84
}) {
  const cols = 14,
    rows = 14;
  let s = seed * 9301 + 49297;
  const rand = () => {
    s = (s * 9301 + 49297) % 233280;
    return s / 233280;
  };
  const cells = [];
  /* face mask roughly oval */
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      const cy = rows / 2,
        cx = cols / 2;
      const dy = (y - cy) / cy,
        dx = (x - cx) / cx;
      const inOval = dy * dy + dx * dx * 0.9 < 0.9;
      const v = rand();
      let c;
      if (!inOval) c = "#000";else if (y < 4) c = v > 0.4 ? "#000" : "#222"; /* hair */else if (y > rows - 4) c = v > 0.6 ? "#1a1a1a" : "#0a0a0a"; /* beard/shadow */else c = v > 0.55 ? "#dadada" : v > 0.3 ? "#888" : "#333"; /* skin */
      cells.push(/*#__PURE__*/React.createElement("rect", {
        key: `${x}-${y}`,
        x: x,
        y: y,
        width: 1,
        height: 1,
        fill: c
      }));
    }
  }
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: `0 0 ${cols} ${rows}`,
    width: size,
    height: size,
    style: {
      imageRendering: "pixelated",
      display: "block",
      borderRadius: 2,
      background: "#000"
    },
    shapeRendering: "crispEdges"
  }, cells);
}

/* ── ZxScreen-style mini picture used for thumbnails (kept independent of ZxScreen import) ── */
function MiniPicture({
  seed,
  palette = "default",
  style
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      width: "100%",
      height: "100%",
      ...style
    }
  }, /*#__PURE__*/React.createElement(PixelArtSVG, {
    seed: seed,
    palette: palette
  }));
}

/* ── role chip ── */
function RoleChip({
  role,
  size = "md"
}) {
  const r = ROLE_TYPES[role];
  if (!r) return null;
  const iconMap = {
    music: "music",
    gfx: "image",
    code: "code",
    intro: "game",
    sfx: "music",
    design: "code"
  };
  return /*#__PURE__*/React.createElement("span", {
    className: "ap-role-chip ap-role-chip--" + r.color + " ap-role-chip--" + size
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: iconMap[role],
    size: size === "sm" ? 10 : 12
  }), r.label);
}

/* ──────────────────────────────────────────────────────────────────────────
   HEADER block — biography (left, one block) + technical specs (collapsible)
   ────────────────────────────────────────────────────────────────────────── */
function AuthorHeader({
  profile,
  counters,
  totalRatings
}) {
  const [showAliases, setShowAliases] = useState(false);
  const [showPlayback, setShowPlayback] = useState(false);
  const VISIBLE_ALIASES = 7;
  const visibleAliases = showAliases ? profile.aliases : profile.aliases.slice(0, VISIBLE_ALIASES);
  const hiddenCount = profile.aliases.length - VISIBLE_ALIASES;

  /* One award at most (VIP > Волонтёр priority). */
  const award = profile.badges.includes("VIP-спонсор") ? {
    kind: "vip",
    label: "VIP-спонсор",
    hint: "поддерживает архив пожертвованиями"
  } : profile.badges.includes("Волонтёр") ? {
    kind: "vol",
    label: "Волонтёр",
    hint: "редактирует архив и добавляет материалы"
  } : null;
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-header__left"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-header__avatar"
  }, profile.avatar === "pixel" ? /*#__PURE__*/React.createElement(PixelAvatar, {
    seed: profile.handle.charCodeAt(0) * 17
  }) : /*#__PURE__*/React.createElement("div", {
    className: "ap-avatar--empty"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "person",
    size: 40
  })))), /*#__PURE__*/React.createElement("div", {
    className: "ap-header__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-header__title-row"
  }, /*#__PURE__*/React.createElement("h1", {
    className: "ap-header__name"
  }, profile.handle), award && /*#__PURE__*/React.createElement("span", {
    className: "ap-award ap-award--" + award.kind,
    title: award.hint
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: award.kind === "vip" ? "crown" : "award",
    size: 12
  }), award.label), /*#__PURE__*/React.createElement("div", {
    className: "ap-header__role-chips"
  }, profile.roles.includes("artist") && /*#__PURE__*/React.createElement("span", {
    className: "ap-tag ap-tag--gfx"
  }, "  ", /*#__PURE__*/React.createElement(AP_I, {
    name: "image",
    size: 12
  }), "\u0425\u0443\u0434\u043E\u0436\u043D\u0438\u043A"), profile.roles.includes("musician") && /*#__PURE__*/React.createElement("span", {
    className: "ap-tag ap-tag--music"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "music",
    size: 12
  }), "\u041C\u0443\u0437\u044B\u043A\u0430\u043D\u0442"), profile.roles.includes("coder") && /*#__PURE__*/React.createElement("span", {
    className: "ap-tag ap-tag--code"
  }, " ", /*#__PURE__*/React.createElement(AP_I, {
    name: "code",
    size: 12
  }), "\u041A\u043E\u0434\u0435\u0440"))), /*#__PURE__*/React.createElement("div", {
    className: "ap-header__bio"
  }, profile.realName && profile.realName !== "—" && /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__name"
  }, profile.realName), profile.location && /*#__PURE__*/React.createElement(React.Fragment, null, profile.realName !== "—" && /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__sep"
  }, "\xB7"), /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__loc"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "location",
    size: 12
  }), profile.location.map((p, i) => /*#__PURE__*/React.createElement(React.Fragment, {
    key: p
  }, i > 0 && ", ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, p))))), /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__sep"
  }, "\xB7"), /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__joined"
  }, "\u041D\u0430 ZX-Art \u0441 ", profile.joined.slice(0, 4), " (\u043B\u043E\u0433\u0438\u043D ", /*#__PURE__*/React.createElement("code", null, profile.siteUser), ")")), /*#__PURE__*/React.createElement("p", {
    className: "ap-stats-sentence"
  }, profile.realName && profile.realName !== "—" ? "Он " : "Этот автор ", [counters.pictures > 0 && /*#__PURE__*/React.createElement(React.Fragment, {
    key: "g"
  }, "\u043D\u0430\u0440\u0438\u0441\u043E\u0432\u0430\u043B ", /*#__PURE__*/React.createElement("b", null, counters.pictures.toLocaleString("ru-RU")), " ", pluralRu(counters.pictures, ["картину", "картины", "картин"])), counters.tunes > 0 && /*#__PURE__*/React.createElement(React.Fragment, {
    key: "m"
  }, "\u043D\u0430\u043F\u0438\u0441\u0430\u043B ", /*#__PURE__*/React.createElement("b", null, counters.tunes.toLocaleString("ru-RU")), " ", pluralRu(counters.tunes, ["мелодию", "мелодии", "мелодий"])), counters.prods > 0 && /*#__PURE__*/React.createElement(React.Fragment, {
    key: "p"
  }, "\u0443\u0447\u0430\u0441\u0442\u0432\u043E\u0432\u0430\u043B \u0432 \u0440\u0430\u0437\u0440\u0430\u0431\u043E\u0442\u043A\u0435 ", /*#__PURE__*/React.createElement("b", null, counters.prods.toLocaleString("ru-RU")), " ", pluralRu(counters.prods, ["программы", "программ", "программ"]))].filter(Boolean).reduce((acc, el, i, arr) => {
    const sep = i === 0 ? "" : i === arr.length - 1 ? " и " : ", ";
    acc.push(sep, el);
    return acc;
  }, []).concat([" — и получил ", /*#__PURE__*/React.createElement("b", {
    key: "c"
  }, counters.comments), " ", pluralRu(counters.comments, ["комментарий", "комментария", "комментариев"]), "."])), (totalRatings.artist > 0 || totalRatings.musician > 0) && /*#__PURE__*/React.createElement("div", {
    className: "ap-rating-strip"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-rating-strip__label"
  }, "\u0420\u0435\u0439\u0442\u0438\u043D\u0433 \u043F\u043E \u0433\u043E\u043B\u043E\u0441\u0430\u043C \u0441\u043E\u043E\u0431\u0449\u0435\u0441\u0442\u0432\u0430:"), totalRatings.artist > 0 && /*#__PURE__*/React.createElement("span", {
    className: "ap-rating-strip__item",
    title: "\u0421\u0443\u043C\u043C\u0430 \u0437\u0432\u0451\u0437\u0434 \u0437\u0430 \u0445\u043E\u0440\u043E\u0448\u043E \u043F\u0440\u043E\u0433\u043E\u043B\u043E\u0441\u043E\u0432\u0430\u043D\u043D\u044B\u0435 \u043A\u0430\u0440\u0442\u0438\u043D\u044B"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "image",
    size: 12
  }), /*#__PURE__*/React.createElement("b", null, totalRatings.artist.toFixed(2)), /*#__PURE__*/React.createElement("span", {
    className: "ap-rating-strip__sub"
  }, "\u0445\u0443\u0434\u043E\u0436\u043D\u0438\u043A")), totalRatings.musician > 0 && /*#__PURE__*/React.createElement("span", {
    className: "ap-rating-strip__item",
    title: "\u0421\u0443\u043C\u043C\u0430 \u0437\u0432\u0451\u0437\u0434 \u0437\u0430 \u0445\u043E\u0440\u043E\u0448\u043E \u043F\u0440\u043E\u0433\u043E\u043B\u043E\u0441\u043E\u0432\u0430\u043D\u043D\u044B\u0435 \u043C\u0435\u043B\u043E\u0434\u0438\u0438"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "music",
    size: 12
  }), /*#__PURE__*/React.createElement("b", null, totalRatings.musician.toFixed(2)), /*#__PURE__*/React.createElement("span", {
    className: "ap-rating-strip__sub"
  }, "\u043C\u0443\u0437\u044B\u043A\u0430\u043D\u0442"))), profile.groups.length > 0 && /*#__PURE__*/React.createElement("div", {
    className: "ap-meta-row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-meta-row__label"
  }, "\u0413\u0440\u0443\u043F\u043F\u044B:"), /*#__PURE__*/React.createElement("div", {
    className: "ap-chips"
  }, profile.groups.map(g => /*#__PURE__*/React.createElement("a", {
    key: g.name,
    href: "#",
    className: "ap-group-chip" + (g.parent ? " ap-group-chip--sub" : ""),
    onClick: e => e.preventDefault()
  }, g.parent && /*#__PURE__*/React.createElement("span", {
    className: "ap-group-chip__sub"
  }, "\u21B3 ", g.parent, " /"), /*#__PURE__*/React.createElement("span", {
    className: "ap-group-chip__name"
  }, g.name), g.years && /*#__PURE__*/React.createElement("span", {
    className: "ap-group-chip__years"
  }, g.years))))), profile.aliases.length > 0 && /*#__PURE__*/React.createElement("div", {
    className: "ap-meta-row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-meta-row__label"
  }, "\u041D\u0438\u043A\u0438:"), /*#__PURE__*/React.createElement("div", {
    className: "ap-aliases"
  }, visibleAliases.map((a, i) => /*#__PURE__*/React.createElement("a", {
    key: a + i,
    href: "#",
    onClick: e => e.preventDefault()
  }, a, i < visibleAliases.length - 1 ? "," : "")), hiddenCount > 0 && !showAliases && /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "ap-aliases__more",
    onClick: () => setShowAliases(true)
  }, "+", hiddenCount), showAliases && hiddenCount > 0 && /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "ap-aliases__more",
    onClick: () => setShowAliases(false)
  }, "\u0441\u0432\u0435\u0440\u043D\u0443\u0442\u044C"))), profile.links.length > 0 && /*#__PURE__*/React.createElement("div", {
    className: "ap-meta-row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-meta-row__label"
  }, "\u0421\u0441\u044B\u043B\u043A\u0438:"), /*#__PURE__*/React.createElement("div", {
    className: "ap-ext-links"
  }, profile.links.map((l, i) => /*#__PURE__*/React.createElement("a", {
    key: i,
    href: "#",
    onClick: e => e.preventDefault(),
    title: l.label
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-ext-icon"
  }, l.icon), l.label)))), /*#__PURE__*/React.createElement("div", {
    className: "ap-meta-row ap-meta-row--tech"
  }, /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "ap-tech-toggle",
    onClick: () => setShowPlayback(!showPlayback)
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: showPlayback ? "chevronUp" : "chevron",
    size: 14
  }), "\u041D\u0430\u0441\u0442\u0440\u043E\u0439\u043A\u0438 \u043A\u043E\u043D\u0432\u0435\u0440\u0442\u0430\u0446\u0438\u0438 \u043F\u043E \u0443\u043C\u043E\u043B\u0447\u0430\u043D\u0438\u044E", /*#__PURE__*/React.createElement("span", {
    className: "ap-tech-toggle__hint"
  }, "\u0430\u0432\u0442\u043E\u0440\u0441\u043A\u0438\u0435 \u0434\u0435\u0444\u043E\u043B\u0442\u044B \u0434\u043B\u044F ogg/png \u2014 \u043A\u043E\u043D\u043A\u0440\u0435\u0442\u043D\u0430\u044F \u0440\u0430\u0431\u043E\u0442\u0430 \u043C\u043E\u0436\u0435\u0442 \u0438\u0445 \u043F\u0435\u0440\u0435\u043E\u043F\u0440\u0435\u0434\u0435\u043B\u0438\u0442\u044C")), showPlayback && /*#__PURE__*/React.createElement("div", {
    className: "ap-tech"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-tech__row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-tech__k"
  }, "\u041F\u0430\u043B\u0438\u0442\u0440\u0430 \u043A\u0430\u0440\u0442\u0438\u043D"), /*#__PURE__*/React.createElement("span", {
    className: "ap-tech__v"
  }, profile.tech.palette)), /*#__PURE__*/React.createElement("div", {
    className: "ap-tech__row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-tech__k"
  }, "\u0427\u0438\u043F AY"), /*#__PURE__*/React.createElement("span", {
    className: "ap-tech__v"
  }, profile.tech.ayChip)), /*#__PURE__*/React.createElement("div", {
    className: "ap-tech__row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-tech__k"
  }, "\u0420\u0430\u0441\u043A\u043B\u0430\u0434\u043A\u0430 \u043A\u0430\u043D\u0430\u043B\u043E\u0432 AY"), /*#__PURE__*/React.createElement("span", {
    className: "ap-tech__v"
  }, profile.tech.ayChannels)), /*#__PURE__*/React.createElement("div", {
    className: "ap-tech__row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-tech__k"
  }, "\u0422\u0430\u043A\u0442\u043E\u0432\u0430\u044F \u0447\u0430\u0441\u0442\u043E\u0442\u0430 AY"), /*#__PURE__*/React.createElement("span", {
    className: "ap-tech__v"
  }, profile.tech.ayClock)), /*#__PURE__*/React.createElement("div", {
    className: "ap-tech__row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-tech__k"
  }, "\u0427\u0430\u0441\u0442\u043E\u0442\u0430 \u043F\u0440\u0435\u0440\u044B\u0432\u0430\u043D\u0438\u0439 INT"), /*#__PURE__*/React.createElement("span", {
    className: "ap-tech__v"
  }, profile.tech.intFreq))))));
}

/* ──────────────────────────────────────────────────────────────────────────
   MINI-DASHBOARD — три колонки, 3–4 работы в каждой.
   Сюда вынесено самое сильное; подробные списки с фильтрами/пагинацией
   живут ниже в вкладках «Все работы».
   ────────────────────────────────────────────────────────────────────────── */
function MiniDashboard({
  pictures,
  tunes,
  prods,
  authorHandle,
  onJumpToTab
}) {
  const [sort, setSort] = useState("votes");
  const sorter = (a, b) => {
    if (sort === "votes") return b.votes - a.votes;
    if (sort === "year") return b.year - a.year;
    if (sort === "plays") return (b.plays || 0) - (a.plays || 0);
    if (sort === "downloads") return (b.downloads || 0) - (a.downloads || 0);
    return 0;
  };
  const topPics = useMemo(() => [...pictures].sort(sorter).slice(0, 4), [pictures, sort]);
  const topTunes = useMemo(() => [...tunes].sort(sorter).slice(0, 4), [tunes, sort]);
  const topProds = useMemo(() => [...prods].sort(sorter).slice(0, 4), [prods, sort]);
  const cols = [pictures.length > 0 && {
    key: "gfx",
    label: "Графика",
    total: pictures.length,
    items: topPics
  }, tunes.length > 0 && {
    key: "music",
    label: "Музыка",
    total: tunes.length,
    items: topTunes
  }, prods.length > 0 && {
    key: "soft",
    label: "Софт",
    total: prods.length,
    items: topProds
  }].filter(Boolean);
  if (cols.length === 0) return null;
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-dashboard"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u041B\u0443\u0447\u0448\u0438\u0435 \u0440\u0430\u0431\u043E\u0442\u044B"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0441\u0430\u043C\u043E\u0435 \u0441\u0438\u043B\u044C\u043D\u043E\u0435 \u0438\u0437 \u043A\u0430\u0436\u0434\u043E\u0439 \u043A\u0430\u0442\u0435\u0433\u043E\u0440\u0438\u0438 \u2014 \u043F\u043E\u0434\u0440\u043E\u0431\u043D\u044B\u0435 \u0441\u043F\u0438\u0441\u043A\u0438 \u0441 \u0444\u0438\u043B\u044C\u0442\u0440\u0430\u043C\u0438 \u0432 \u0432\u043A\u043B\u0430\u0434\u043A\u0430\u0445 \u043D\u0438\u0436\u0435"), /*#__PURE__*/React.createElement("div", {
    className: "ap-dashboard__sort"
  }, /*#__PURE__*/React.createElement("label", null, "\u0442\u043E\u043F \u043F\u043E"), /*#__PURE__*/React.createElement("select", {
    value: sort,
    onChange: e => setSort(e.target.value)
  }, /*#__PURE__*/React.createElement("option", {
    value: "votes"
  }, "\u0433\u043E\u043B\u043E\u0441\u0430\u043C \u2605"), /*#__PURE__*/React.createElement("option", {
    value: "year"
  }, "\u0433\u043E\u0434\u0443 \u2193"), /*#__PURE__*/React.createElement("option", {
    value: "plays"
  }, "\u0437\u0430\u043F\u0443\u0441\u043A\u0430\u043C \u043D\u0430 \u0441\u0430\u0439\u0442\u0435 \u25B6"), /*#__PURE__*/React.createElement("option", {
    value: "downloads"
  }, "\u0441\u043A\u0430\u0447\u0438\u0432\u0430\u043D\u0438\u044F\u043C \u2B07")))), /*#__PURE__*/React.createElement("div", {
    className: "ap-dash-grid",
    style: {
      gridTemplateColumns: `repeat(${cols.length}, 1fr)`
    }
  }, cols.map(col => /*#__PURE__*/React.createElement("div", {
    key: col.key,
    className: "ap-dash-col ap-dash-col--" + col.key
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-dash-col__head"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: col.key === "gfx" ? "image" : col.key === "music" ? "music" : "game",
    size: 14
  }), /*#__PURE__*/React.createElement("span", {
    className: "ap-dash-col__label"
  }, col.label), /*#__PURE__*/React.createElement("span", {
    className: "ap-dash-col__count"
  }, col.total), /*#__PURE__*/React.createElement("a", {
    className: "ap-dash-col__all",
    href: "#",
    onClick: e => {
      e.preventDefault();
      onJumpToTab && onJumpToTab(col.key);
    }
  }, "\u0432\u0441\u0435 \u2192")), col.key === "gfx" && /*#__PURE__*/React.createElement("div", {
    className: "ap-dash-pics"
  }, col.items.map(p => /*#__PURE__*/React.createElement("a", {
    key: p.id,
    href: "#",
    className: "ap-dash-pic",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-dash-pic__art"
  }, /*#__PURE__*/React.createElement(MiniPicture, {
    seed: p.id,
    palette: p.palette
  }), p.place && /*#__PURE__*/React.createElement("span", {
    className: "ap-dash-pic__place"
  }, p.place)), /*#__PURE__*/React.createElement("div", {
    className: "ap-dash-pic__title"
  }, p.title), /*#__PURE__*/React.createElement("div", {
    className: "ap-dash-pic__meta"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-dash-pic__year"
  }, p.year), /*#__PURE__*/React.createElement("span", {
    className: "ap-dash-pic__star"
  }, "\u2605 ", p.stars, ".", p.votes % 9), /*#__PURE__*/React.createElement("span", {
    className: "ap-dash-pic__votes"
  }, "\xB7 ", p.votes))))), col.key === "music" && /*#__PURE__*/React.createElement("div", {
    className: "ap-dash-tunes"
  }, col.items.map((t, i) => /*#__PURE__*/React.createElement("div", {
    key: t.id,
    className: "ap-dash-tune"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-dash-tune__rank"
  }, i + 1), /*#__PURE__*/React.createElement("button", {
    className: "ap-dash-tune__play",
    "aria-label": "Play"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "play",
    size: 12
  })), /*#__PURE__*/React.createElement("div", {
    className: "ap-dash-tune__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-dash-tune__title"
  }, t.title), /*#__PURE__*/React.createElement("div", {
    className: "ap-dash-tune__meta"
  }, t.chip, " \xB7 ", t.duration, " \xB7 ", t.year)), /*#__PURE__*/React.createElement("div", {
    className: "ap-dash-tune__stats"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-feat__star"
  }, "\u2605 ", t.stars, ".", t.votes % 9), /*#__PURE__*/React.createElement("span", {
    className: "ap-dash-tune__plays"
  }, "\u25B6 ", t.plays.toLocaleString("ru-RU")))))), col.key === "soft" && /*#__PURE__*/React.createElement("div", {
    className: "ap-dash-prods"
  }, col.items.map(p => {
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
      place: null
    };
    return /*#__PURE__*/React.createElement("div", {
      key: p.id,
      className: "ap-prodwrap ap-prodwrap--compact"
    }, (p.roles.length > 0 || p.introRelease) && /*#__PURE__*/React.createElement("div", {
      className: "ap-prodwrap__roles"
    }, p.roles.map(r => /*#__PURE__*/React.createElement(RoleChip, {
      key: r,
      role: r
    })), p.introRelease && /*#__PURE__*/React.createElement(RoleChip, {
      role: "intro"
    })), /*#__PURE__*/React.createElement(ProdCard, {
      prod: adapted
    }));
  }))))));
}
window.AuthorHeader = AuthorHeader;
window.MiniDashboard = MiniDashboard;
window.AP_I = AP_I;
window.PixelAvatar = PixelAvatar;
window.RoleChip = RoleChip;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/AuthorPage.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/AuthorPageWorks.jsx
try { (() => {
/* AuthorPageWorks.jsx — Works navigator (tabs + filters + pagination),
   Collaborators, Comments & Votes feed, and the top-level AuthorPage shell. */

const {
  useState: useState2,
  useMemo: useMemo2
} = React;

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
function Pagination({
  page,
  total,
  perPage,
  onChange
}) {
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
    if (i > 0 && dedup[i] - dedup[i - 1] > 1) out.push("…");
    out.push(dedup[i]);
  }
  return /*#__PURE__*/React.createElement("nav", {
    className: "ap-pagination",
    "aria-label": "pagination"
  }, /*#__PURE__*/React.createElement("button", {
    className: "ap-page-btn",
    disabled: page === 1,
    onClick: () => onChange(page - 1)
  }, "\u2190 \u043F\u0440\u0435\u0434."), out.map((n, i) => n === "…" ? /*#__PURE__*/React.createElement("span", {
    key: i,
    className: "ap-page-gap"
  }, "\u2026") : /*#__PURE__*/React.createElement("button", {
    key: i,
    className: "ap-page-btn" + (n === page ? " ap-page-btn--active" : ""),
    onClick: () => onChange(n)
  }, n)), /*#__PURE__*/React.createElement("button", {
    className: "ap-page-btn",
    disabled: page === pages,
    onClick: () => onChange(page + 1)
  }, "\u0441\u043B\u0435\u0434. \u2192"), /*#__PURE__*/React.createElement("span", {
    className: "ap-pagination__total"
  }, "\u0438\u0437 ", pages, " \u0441\u0442\u0440\u0430\u043D\u0438\u0446 \xB7 \u0432\u0441\u0435\u0433\u043E ", total));
}

/* Compact picture card used in the year-grouped grid — uses the existing PictureCard. */
function PictureCardMini({
  pic
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "ap-pic-wrap"
  }, /*#__PURE__*/React.createElement(PictureCard, {
    picture: {
      id: pic.id,
      title: pic.title,
      palette: pic.palette,
      authors: pic.authors,
      year: pic.year,
      stars: pic.stars,
      votes: pic.votes,
      party: pic.party || "",
      place: pic.place || null,
      format: pic.format,
      realtime: pic.realtime,
      flickering: pic.flickering
    }
  }));
}
function YearRail({
  year,
  count
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "ap-year-rail"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-year-rail__dot"
  }), /*#__PURE__*/React.createElement("div", {
    className: "ap-year-rail__year"
  }, year), /*#__PURE__*/React.createElement("div", {
    className: "ap-year-rail__count"
  }, count));
}
function GraphicsTab({
  pictures
}) {
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
    return pictures.filter(p => (formatFilter === "all" || p.format === formatFilter) && (partyFilter === "all" || p.party === partyFilter) && (search === "" || p.title.toLowerCase().includes(search.toLowerCase())));
  }, [pictures, formatFilter, partyFilter, search]);
  const sorted = useMemo2(() => {
    const arr = [...filtered];
    if (sort === "year-desc") arr.sort((a, b) => b.year - a.year || b.votes - a.votes);
    if (sort === "year-asc") arr.sort((a, b) => a.year - b.year || b.votes - a.votes);
    if (sort === "votes") arr.sort((a, b) => b.votes - a.votes);
    if (sort === "plays") arr.sort((a, b) => (b.plays || 0) - (a.plays || 0));
    if (sort === "downloads") arr.sort((a, b) => (b.downloads || 0) - (a.downloads || 0));
    return arr;
  }, [filtered, sort]);
  const pageStart = (page - 1) * PAGE_SIZE_PIC;
  const pageItems = sorted.slice(pageStart, pageStart + PAGE_SIZE_PIC);

  /* group page items by year when sort is by year, otherwise show flat */
  const groupedByYear = sort === "year-desc" || sort === "year-asc";
  const groups = useMemo2(() => {
    if (!groupedByYear) return [{
      year: null,
      items: pageItems
    }];
    const m = new Map();
    pageItems.forEach(p => {
      if (!m.has(p.year)) m.set(p.year, []);
      m.get(p.year).push(p);
    });
    const arr = [...m.entries()].map(([year, items]) => ({
      year,
      items
    }));
    if (sort === "year-asc") arr.sort((a, b) => a.year - b.year);else arr.sort((a, b) => b.year - a.year);
    return arr;
  }, [pageItems, sort, groupedByYear]);
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "sort",
    size: 14
  }), /*#__PURE__*/React.createElement("span", null, "\u0421\u043E\u0440\u0442\u0438\u0440\u043E\u0432\u043A\u0430:"), [["year-desc", "новее"], ["year-asc", "старее"], ["votes", "по голосам"], ["plays", "по запускам на сайте"], ["downloads", "по скачиваниям"]].map(([k, l]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    className: "ap-pill" + (sort === k ? " ap-pill--on" : ""),
    onClick: () => {
      setSort(k);
      setPage(1);
    }
  }, l))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__sep"
  }), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group"
  }, /*#__PURE__*/React.createElement("label", null, "\u0424\u043E\u0440\u043C\u0430\u0442:"), /*#__PURE__*/React.createElement("select", {
    value: formatFilter,
    onChange: e => {
      setFormatFilter(e.target.value);
      setPage(1);
    }
  }, /*#__PURE__*/React.createElement("option", {
    value: "all"
  }, "\u0432\u0441\u0435 (", allFormats.length, ")"), allFormats.map(f => /*#__PURE__*/React.createElement("option", {
    key: f,
    value: f
  }, f)))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group"
  }, /*#__PURE__*/React.createElement("label", null, "\u041F\u0430\u0442\u0438:"), /*#__PURE__*/React.createElement("select", {
    value: partyFilter,
    onChange: e => {
      setPartyFilter(e.target.value);
      setPage(1);
    }
  }, /*#__PURE__*/React.createElement("option", {
    value: "all"
  }, "\u0432\u0441\u0435"), allParties.map(p => /*#__PURE__*/React.createElement("option", {
    key: p,
    value: p
  }, p)))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group ap-toolbar__group--search"
  }, /*#__PURE__*/React.createElement("input", {
    type: "text",
    className: "ap-search",
    placeholder: "\u043F\u043E\u0438\u0441\u043A \u043F\u043E \u043D\u0430\u0437\u0432\u0430\u043D\u0438\u044E\u2026",
    value: search,
    onChange: e => {
      setSearch(e.target.value);
      setPage(1);
    }
  }))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__result"
  }, "\u041D\u0430\u0439\u0434\u0435\u043D\u043E ", /*#__PURE__*/React.createElement("b", null, filtered.length), " \u0438\u0437 ", pictures.length, " \u043A\u0430\u0440\u0442\u0438\u043D", formatFilter !== "all" && /*#__PURE__*/React.createElement("span", null, " \xB7 \u0444\u043E\u0440\u043C\u0430\u0442 ", formatFilter), partyFilter !== "all" && /*#__PURE__*/React.createElement("span", null, " \xB7 ", partyFilter), search && /*#__PURE__*/React.createElement("span", null, " \xB7 \u043F\u043E \u0437\u0430\u043F\u0440\u043E\u0441\u0443 \"", search, "\"")), groups.map((g, gi) => /*#__PURE__*/React.createElement("div", {
    key: gi,
    className: "ap-year-group"
  }, g.year != null && /*#__PURE__*/React.createElement(YearRail, {
    year: g.year,
    count: g.items.length
  }), /*#__PURE__*/React.createElement("div", {
    className: "ap-pic-grid"
  }, g.items.map(p => /*#__PURE__*/React.createElement(PictureCardMini, {
    key: p.id,
    pic: p
  }))))), filtered.length === 0 && /*#__PURE__*/React.createElement("div", {
    className: "ap-empty"
  }, "\u041D\u0438\u0447\u0435\u0433\u043E \u043D\u0435 \u043D\u0430\u0439\u0434\u0435\u043D\u043E. \u041F\u043E\u043F\u0440\u043E\u0431\u0443\u0439 \u0434\u0440\u0443\u0433\u0438\u0435 \u0444\u0438\u043B\u044C\u0442\u0440\u044B."), /*#__PURE__*/React.createElement(Pagination, {
    page: page,
    total: filtered.length,
    perPage: PAGE_SIZE_PIC,
    onChange: setPage
  }));
}
function MusicTab({
  tunes
}) {
  const [page, setPage] = useState2(1);
  const [sort, setSort] = useState2("year-desc");
  const [chipFilter, setChipFilter] = useState2("all");
  const [search, setSearch] = useState2("");
  const allChips = useMemo2(() => [...new Set(tunes.map(t => t.chip))], [tunes]);
  const filtered = useMemo2(() => tunes.filter(t => (chipFilter === "all" || t.chip === chipFilter) && (search === "" || t.title.toLowerCase().includes(search.toLowerCase()))), [tunes, chipFilter, search]);
  const sorted = useMemo2(() => {
    const arr = [...filtered];
    if (sort === "year-desc") arr.sort((a, b) => b.year - a.year || b.votes - a.votes);
    if (sort === "year-asc") arr.sort((a, b) => a.year - b.year);
    if (sort === "votes") arr.sort((a, b) => b.votes - a.votes);
    if (sort === "plays") arr.sort((a, b) => b.plays - a.plays);
    if (sort === "downloads") arr.sort((a, b) => (b.downloads || 0) - (a.downloads || 0));
    return arr;
  }, [filtered, sort]);
  const pageStart = (page - 1) * PAGE_SIZE_TUNE;
  const pageItems = sorted.slice(pageStart, pageStart + PAGE_SIZE_TUNE);
  const groupedByYear = sort === "year-desc" || sort === "year-asc";
  const groups = useMemo2(() => {
    if (!groupedByYear) return [{
      year: null,
      items: pageItems
    }];
    const m = new Map();
    pageItems.forEach(p => {
      if (!m.has(p.year)) m.set(p.year, []);
      m.get(p.year).push(p);
    });
    const arr = [...m.entries()].map(([year, items]) => ({
      year,
      items
    }));
    if (sort === "year-asc") arr.sort((a, b) => a.year - b.year);else arr.sort((a, b) => b.year - a.year);
    return arr;
  }, [pageItems, sort, groupedByYear]);
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "sort",
    size: 14
  }), /*#__PURE__*/React.createElement("span", null, "\u0421\u043E\u0440\u0442\u0438\u0440\u043E\u0432\u043A\u0430:"), [["year-desc", "новее"], ["year-asc", "старее"], ["votes", "по голосам"], ["plays", "по запускам на сайте"], ["downloads", "по скачиваниям"]].map(([k, l]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    className: "ap-pill" + (sort === k ? " ap-pill--on" : ""),
    onClick: () => {
      setSort(k);
      setPage(1);
    }
  }, l))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__sep"
  }), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group"
  }, /*#__PURE__*/React.createElement("label", null, "\u0422\u0438\u043F \u0437\u0432\u0443\u0447\u0430\u043D\u0438\u044F:"), [["all", "все"], ...allChips.map(c => [c, c])].map(([k, l]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    className: "ap-pill" + (chipFilter === k ? " ap-pill--on" : ""),
    onClick: () => {
      setChipFilter(k);
      setPage(1);
    }
  }, l))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group ap-toolbar__group--search"
  }, /*#__PURE__*/React.createElement("input", {
    type: "text",
    className: "ap-search",
    placeholder: "\u043F\u043E\u0438\u0441\u043A \u043F\u043E \u043D\u0430\u0437\u0432\u0430\u043D\u0438\u044E\u2026",
    value: search,
    onChange: e => {
      setSearch(e.target.value);
      setPage(1);
    }
  }))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__result"
  }, "\u041D\u0430\u0439\u0434\u0435\u043D\u043E ", /*#__PURE__*/React.createElement("b", null, filtered.length), " \u0438\u0437 ", tunes.length, " \u043C\u0435\u043B\u043E\u0434\u0438\u0439"), groups.map((g, gi) => /*#__PURE__*/React.createElement("div", {
    key: gi,
    className: "ap-year-group"
  }, g.year != null && /*#__PURE__*/React.createElement(YearRail, {
    year: g.year,
    count: g.items.length
  }), /*#__PURE__*/React.createElement("div", {
    className: "ap-tune-list"
  }, g.items.map(t => /*#__PURE__*/React.createElement("div", {
    key: t.id,
    className: "zx-tune-row"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-tune-row__play",
    "aria-label": "play"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "play",
    size: 14
  })), /*#__PURE__*/React.createElement("span", {
    className: "zx-tune-row__title"
  }, t.title), /*#__PURE__*/React.createElement("span", {
    className: "zx-tune-row__author"
  }, t.chip, " \xB7 ", t.duration), /*#__PURE__*/React.createElement("span", {
    className: "zx-tune-row__chip"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-feat__star"
  }, "\u2605 ", t.stars), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: 8,
      color: "var(--text-light-color)"
    }
  }, "\xB7 \u25B6 ", t.plays.toLocaleString("ru-RU")), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: 8,
      color: "var(--text-light-color)"
    }
  }, "\xB7 \u2B07 ", t.downloads))))))), filtered.length === 0 && /*#__PURE__*/React.createElement("div", {
    className: "ap-empty"
  }, "\u041D\u0438\u0447\u0435\u0433\u043E \u043D\u0435 \u043D\u0430\u0439\u0434\u0435\u043D\u043E."), /*#__PURE__*/React.createElement(Pagination, {
    page: page,
    total: filtered.length,
    perPage: PAGE_SIZE_TUNE,
    onChange: setPage
  }));
}

/* Wraps the standard ProdCard with the author-context annotations
   (role chips for "что именно делал автор" + "intro для релиза …").
   The card itself is unchanged — chips sit in a separate row above it. */
function AuthorProdCard({
  prod,
  authorHandle,
  showRoles = true
}) {
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
    place: null
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "ap-prodwrap"
  }, showRoles && (prod.roles.length > 0 || prod.introRelease) && /*#__PURE__*/React.createElement("div", {
    className: "ap-prodwrap__roles"
  }, prod.roles.map(r => /*#__PURE__*/React.createElement(RoleChip, {
    key: r,
    role: r
  })), prod.introRelease && /*#__PURE__*/React.createElement("span", {
    className: "ap-prodwrap__intro"
  }, /*#__PURE__*/React.createElement(RoleChip, {
    role: "intro"
  }), /*#__PURE__*/React.createElement("span", null, "\u0434\u043B\u044F \u0440\u0435\u043B\u0438\u0437\u0430 ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, prod.introRelease)))), /*#__PURE__*/React.createElement(ProdCard, {
    prod: adapted
  }));
}
function SoftwareTab({
  prods,
  authorHandle
}) {
  const [page, setPage] = useState2(1);
  const [roleFilter, setRoleFilter] = useState2("all");
  const [catFilter, setCatFilter] = useState2("all"); /* "all" | top-level kind | "kind/sub" */
  const [sort, setSort] = useState2("year-desc");
  const roleCounts = useMemo2(() => {
    const c = {
      all: prods.length,
      intro: 0
    };
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
      if (!tree[p.kind]) tree[p.kind] = {
        total: 0,
        subs: {}
      };
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
    if (roleFilter === "intro") res = res.filter(p => p.introRelease);else if (roleFilter !== "all") res = res.filter(p => p.roles.includes(roleFilter));
    return res;
  }, [prods, roleFilter, catFilter]);
  const sorted = useMemo2(() => {
    const arr = [...filtered];
    if (sort === "year-desc") arr.sort((a, b) => b.year - a.year || b.votes - a.votes);
    if (sort === "year-asc") arr.sort((a, b) => a.year - b.year);
    if (sort === "votes") arr.sort((a, b) => b.votes - a.votes);
    if (sort === "plays") arr.sort((a, b) => (b.plays || 0) - (a.plays || 0));
    if (sort === "downloads") arr.sort((a, b) => b.downloads - a.downloads);
    return arr;
  }, [filtered, sort]);
  const pageStart = (page - 1) * PAGE_SIZE_PROD;
  const pageItems = sorted.slice(pageStart, pageStart + PAGE_SIZE_PROD);

  /* Smart grouping per the brief: only group-by-year when no role filter,
     so the page never repeats "music music music" tags down the column. */
  const groupedByYear = roleFilter === "all" && (sort === "year-desc" || sort === "year-asc");
  const groups = useMemo2(() => {
    if (!groupedByYear) return [{
      year: null,
      items: pageItems
    }];
    const m = new Map();
    pageItems.forEach(p => {
      if (!m.has(p.year)) m.set(p.year, []);
      m.get(p.year).push(p);
    });
    const arr = [...m.entries()].map(([year, items]) => ({
      year,
      items
    }));
    if (sort === "year-asc") arr.sort((a, b) => a.year - b.year);else arr.sort((a, b) => b.year - a.year);
    return arr;
  }, [pageItems, sort, groupedByYear]);
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar ap-toolbar--soft"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group ap-toolbar__group--cats"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-toolbar__group-title"
  }, "\u041A\u0430\u0442\u0435\u0433\u043E\u0440\u0438\u044F:"), /*#__PURE__*/React.createElement("button", {
    className: "ap-pill" + (catFilter === "all" ? " ap-pill--on" : ""),
    onClick: () => {
      setCatFilter("all");
      setPage(1);
    }
  }, "\u0432\u0441\u0435 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-pill__num"
  }, prods.length)), Object.entries(catTree).map(([k, info]) => /*#__PURE__*/React.createElement(React.Fragment, {
    key: k
  }, /*#__PURE__*/React.createElement("button", {
    className: "ap-pill" + (catFilter === k ? " ap-pill--on" : ""),
    onClick: () => {
      setCatFilter(k);
      setPage(1);
    }
  }, k, " ", /*#__PURE__*/React.createElement("span", {
    className: "ap-pill__num"
  }, info.total)), (catFilter === k || catFilter.startsWith(k + "/")) && Object.entries(info.subs).map(([s, n]) => {
    const key = k + "/" + s;
    return /*#__PURE__*/React.createElement("button", {
      key: key,
      className: "ap-pill ap-pill--sub" + (catFilter === key ? " ap-pill--on" : ""),
      onClick: () => {
        setCatFilter(key);
        setPage(1);
      }
    }, s, " ", /*#__PURE__*/React.createElement("span", {
      className: "ap-pill__num"
    }, n));
  })))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group ap-toolbar__group--roles"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-toolbar__group-title"
  }, "\u0420\u043E\u043B\u044C \u0430\u0432\u0442\u043E\u0440\u0430 \u0432 \u043F\u0440\u043E\u0434\u0435:"), /*#__PURE__*/React.createElement("button", {
    className: "ap-pill" + (roleFilter === "all" ? " ap-pill--on" : ""),
    onClick: () => {
      setRoleFilter("all");
      setPage(1);
    }
  }, "\u0432\u0441\u0435 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-pill__num"
  }, roleCounts.all)), [["music", "Музыка"], ["gfx", "Графика"], ["code", "Код"], ["design", "Гейм-дизайн"], ["sfx", "Звук"], ["intro", "Интро к релизу"]].map(([k, l]) => roleCounts[k] > 0 && /*#__PURE__*/React.createElement("button", {
    key: k,
    className: "ap-pill ap-pill--role-" + (ROLE_TYPES[k]?.color || "intro") + (roleFilter === k ? " ap-pill--on" : ""),
    onClick: () => {
      setRoleFilter(k);
      setPage(1);
    }
  }, l, " ", /*#__PURE__*/React.createElement("span", {
    className: "ap-pill__num"
  }, roleCounts[k])))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__sep"
  }), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "sort",
    size: 14
  }), /*#__PURE__*/React.createElement("span", null, "\u0421\u043E\u0440\u0442\u0438\u0440\u043E\u0432\u043A\u0430:"), [["year-desc", "новее"], ["votes", "по голосам"], ["plays", "по запускам на сайте"], ["downloads", "по скачиваниям"]].map(([k, l]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    className: "ap-pill" + (sort === k ? " ap-pill--on" : ""),
    onClick: () => {
      setSort(k);
      setPage(1);
    }
  }, l)))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__result"
  }, "\u041D\u0430\u0439\u0434\u0435\u043D\u043E ", /*#__PURE__*/React.createElement("b", null, filtered.length), " ", pluralRu(filtered.length, ["программа", "программы", "программ"]), roleFilter !== "all" && /*#__PURE__*/React.createElement(React.Fragment, null, " \xB7 \u0432 \u0440\u043E\u043B\u0438 ", /*#__PURE__*/React.createElement(RoleChip, {
    role: roleFilter
  }))), groups.map((g, gi) => /*#__PURE__*/React.createElement("div", {
    key: gi,
    className: "ap-year-group ap-year-group--prod"
  }, g.year != null && /*#__PURE__*/React.createElement(YearRail, {
    year: g.year,
    count: g.items.length
  }), /*#__PURE__*/React.createElement("div", {
    className: "ap-prod-grid"
  }, g.items.map(p => /*#__PURE__*/React.createElement(AuthorProdCard, {
    key: p.id,
    prod: p,
    authorHandle: authorHandle,
    showRoles: roleFilter === "all"
  }))))), filtered.length === 0 && /*#__PURE__*/React.createElement("div", {
    className: "ap-empty"
  }, "\u041D\u0438\u0447\u0435\u0433\u043E \u043D\u0435 \u043D\u0430\u0439\u0434\u0435\u043D\u043E."), /*#__PURE__*/React.createElement(Pagination, {
    page: page,
    total: filtered.length,
    perPage: PAGE_SIZE_PROD,
    onChange: setPage
  }));
}
function WorksNavigator({
  pictures,
  tunes,
  prods,
  authorHandle,
  initialTab
}) {
  const tabs = [];
  if (pictures.length > 0) tabs.push({
    id: "gfx",
    label: "Графика",
    icon: "image",
    count: pictures.length
  });
  if (tunes.length > 0) tabs.push({
    id: "music",
    label: "Музыка",
    icon: "music",
    count: tunes.length
  });
  if (prods.length > 0) tabs.push({
    id: "soft",
    label: "Софт",
    icon: "game",
    count: prods.length
  });
  const [tab, setTab] = useState2(tabs[0]?.id || "gfx");

  /* When the dashboard asks to jump to a tab, switch it. */
  React.useEffect(() => {
    if (initialTab && tabs.some(t => t.id === initialTab)) setTab(initialTab);
  }, [initialTab]);
  if (tabs.length === 0) {
    return /*#__PURE__*/React.createElement("section", {
      className: "ap-works"
    }, /*#__PURE__*/React.createElement("div", {
      className: "ap-section__h"
    }, /*#__PURE__*/React.createElement("h2", null, "\u0420\u0430\u0431\u043E\u0442\u044B")), /*#__PURE__*/React.createElement("div", {
      className: "ap-empty ap-empty--big"
    }, "\u0423 \u044D\u0442\u043E\u0433\u043E \u0430\u0432\u0442\u043E\u0440\u0430 \u043F\u043E\u043A\u0430 \u043D\u0435\u0442 \u0434\u043E\u0431\u0430\u0432\u043B\u0435\u043D\u043D\u044B\u0445 \u0440\u0430\u0431\u043E\u0442.", /*#__PURE__*/React.createElement("div", {
      style: {
        marginTop: 8,
        fontSize: "var(--font-xs)"
      }
    }, "\u0415\u0441\u043B\u0438 \u0443 \u0432\u0430\u0441 \u0435\u0441\u0442\u044C \u0435\u0433\u043E \u043F\u0440\u043E\u0438\u0437\u0432\u0435\u0434\u0435\u043D\u0438\u044F \u2014 \u043F\u043E\u0434\u0435\u043B\u0438\u0442\u0435\u0441\u044C, \u043C\u044B \u0434\u043E\u0431\u0430\u0432\u0438\u043C.")));
  }
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-works"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0412\u0441\u0435 \u0440\u0430\u0431\u043E\u0442\u044B"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0444\u0438\u043B\u044C\u0442\u0440\u044B \u043F\u043E \u0433\u043E\u0434\u0443, \u043F\u0430\u0440\u0442\u0438\u0438, \u0447\u0438\u043F\u0443 \u0438 \u043F\u043E\u0438\u0441\u043A \u2014 \u0431\u0435\u0437 \u043F\u0435\u0440\u0435\u0437\u0430\u0433\u0440\u0443\u0437\u043A\u0438")), /*#__PURE__*/React.createElement("div", {
    className: "ap-worktabs"
  }, tabs.map(t => /*#__PURE__*/React.createElement("button", {
    key: t.id,
    className: "ap-worktab" + (tab === t.id ? " ap-worktab--on" : ""),
    onClick: () => setTab(t.id)
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: t.icon,
    size: 14
  }), t.label, /*#__PURE__*/React.createElement("span", {
    className: "ap-worktab__count"
  }, t.count.toLocaleString("ru-RU"))))), /*#__PURE__*/React.createElement("div", {
    className: "ap-worktab-body"
  }, tab === "gfx" && /*#__PURE__*/React.createElement(GraphicsTab, {
    pictures: pictures
  }), tab === "music" && /*#__PURE__*/React.createElement(MusicTab, {
    tunes: tunes
  }), tab === "soft" && /*#__PURE__*/React.createElement(SoftwareTab, {
    prods: prods,
    authorHandle: authorHandle
  })));
}

/* ──────────────────────────────────────────────────────────────────────────
   COLLABORATORS + GROUPS
   ────────────────────────────────────────────────────────────────────────── */
function Collaborators({
  people,
  groups
}) {
  if (people.length === 0 && groups.length === 0) return null;
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-collab"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0421 \u043A\u0435\u043C \u0440\u0430\u0431\u043E\u0442\u0430\u043B"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0441\u043E\u0430\u0432\u0442\u043E\u0440\u044B \u0438 \u0433\u0440\u0443\u043F\u043F\u044B, \u043E\u0442\u0441\u043E\u0440\u0442\u0438\u0440\u043E\u0432\u0430\u043D\u044B \u043F\u043E \u0447\u0438\u0441\u043B\u0443 \u0441\u043E\u0432\u043C\u0435\u0441\u0442\u043D\u044B\u0445 \u0440\u0430\u0431\u043E\u0442")), /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__cols"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col-h"
  }, "\u041B\u044E\u0434\u0438 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count"
  }, people.length)), /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__people"
  }, people.map(p => {
    const total = p.joint.pictures + p.joint.tunes + p.joint.prods;
    return /*#__PURE__*/React.createElement("a", {
      key: p.handle,
      href: "#",
      className: "ap-collab__person",
      onClick: e => e.preventDefault()
    }, /*#__PURE__*/React.createElement(PixelAvatar, {
      seed: p.handle.charCodeAt(0) * 23,
      size: 32
    }), /*#__PURE__*/React.createElement("div", {
      className: "ap-collab__person-body"
    }, /*#__PURE__*/React.createElement("div", {
      className: "ap-collab__person-name"
    }, p.handle, p.years && /*#__PURE__*/React.createElement("span", {
      className: "ap-collab__person-years"
    }, p.years)), /*#__PURE__*/React.createElement("div", {
      className: "ap-collab__person-groups"
    }, p.groups), /*#__PURE__*/React.createElement("div", {
      className: "ap-collab__person-stats"
    }, p.joint.pictures > 0 && /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement(AP_I, {
      name: "image",
      size: 10
    }), p.joint.pictures), p.joint.tunes > 0 && /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement(AP_I, {
      name: "music",
      size: 10
    }), p.joint.tunes), p.joint.prods > 0 && /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement(AP_I, {
      name: "game",
      size: 10
    }), p.joint.prods))), /*#__PURE__*/React.createElement("div", {
      className: "ap-collab__person-total"
    }, /*#__PURE__*/React.createElement("b", null, total), /*#__PURE__*/React.createElement("span", null, "\u0441\u043E\u0432\u043C.")));
  }))), /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col-h"
  }, "\u0413\u0440\u0443\u043F\u043F\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count"
  }, groups.length)), /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__groups"
  }, groups.map(g => /*#__PURE__*/React.createElement("a", {
    key: g.name,
    href: "#",
    className: "ap-collab__group",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__group-head"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-collab__group-name"
  }, g.name), g.years && /*#__PURE__*/React.createElement("span", {
    className: "ap-collab__group-years"
  }, g.years)), /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__group-meta"
  }, g.members, " \u0443\u0447\u0430\u0441\u0442\u043D\u0438\u043A\u043E\u0432 \xB7 ", g.ourWorks, " \u0440\u0430\u0431\u043E\u0442 \u0430\u0432\u0442\u043E\u0440\u0430", g.releases ? /*#__PURE__*/React.createElement(React.Fragment, null, " \xB7 ", g.releases, " \u0440\u0435\u043B\u0438\u0437\u043E\u0432") : null)))))));
}

/* ──────────────────────────────────────────────────────────────────────────
   COMMENTS + VOTES feed — two horizontal columns
   ────────────────────────────────────────────────────────────────────────── */
function FeedColumns({
  comments,
  votes
}) {
  if (comments.length === 0 && votes.length === 0) return null;
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-feed"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0410\u043A\u0442\u0438\u0432\u043D\u043E\u0441\u0442\u044C \u0432\u043E\u043A\u0440\u0443\u0433 \u0440\u0430\u0431\u043E\u0442 \u0430\u0432\u0442\u043E\u0440\u0430"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0441\u0430\u043C\u043E\u0435 \u0441\u0432\u0435\u0436\u0435\u0435: \u0447\u0442\u043E \u0433\u043E\u0432\u043E\u0440\u044F\u0442 \u0438 \u043A\u0430\u043A \u0433\u043E\u043B\u043E\u0441\u0443\u044E\u0442")), /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__cols"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col-h"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "chat",
    size: 14
  }), "\u0421\u0432\u0435\u0436\u0438\u0435 \u043A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0438 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count"
  }, comments.length)), /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__comments"
  }, comments.map(c => /*#__PURE__*/React.createElement("article", {
    key: c.id,
    className: "ap-fcomment"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-fcomment__head"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault(),
    className: "ap-fcomment__user"
  }, c.by), /*#__PURE__*/React.createElement("span", {
    className: "ap-fcomment__work"
  }, "\u043A ", c.workType === "tune" ? "мелодии" : c.workType === "prod" ? "программе" : "графике", " ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, "\xAB", c.workTitle, "\xBB"), c.role && /*#__PURE__*/React.createElement("span", {
    className: "ap-fcomment__role"
  }, " \xB7 ", c.role)), /*#__PURE__*/React.createElement("span", {
    className: "ap-fcomment__date"
  }, c.date)), /*#__PURE__*/React.createElement("div", {
    className: "ap-fcomment__body"
  }, c.body))))), /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col-h"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "star",
    size: 14
  }), "\u0418\u0441\u0442\u043E\u0440\u0438\u044F \u0433\u043E\u043B\u043E\u0441\u043E\u0432 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count"
  }, votes.length)), /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__votes"
  }, votes.map(v => /*#__PURE__*/React.createElement("div", {
    key: v.id,
    className: "ap-fvote"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-fvote__date"
  }, v.date.slice(5)), /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault(),
    className: "ap-fvote__user"
  }, v.by), /*#__PURE__*/React.createElement("span", {
    className: "ap-fvote__stars",
    title: `${v.score}/5`
  }, Array.from({
    length: 5
  }).map((_, i) => /*#__PURE__*/React.createElement("span", {
    key: i,
    className: i < v.score ? "on" : "off"
  }, "\u2605"))), /*#__PURE__*/React.createElement("span", {
    className: "ap-fvote__work"
  }, "\u2192 ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, "\xAB", v.workTitle, "\xBB")), /*#__PURE__*/React.createElement("span", {
    className: "ap-fvote__type"
  }, v.workType === "tune" ? "муз." : v.workType === "prod" ? "прог." : "гр.")))))));
}

/* ──────────────────────────────────────────────────────────────────────────
   GUESTBOOK / WALL — комментарии не к работам, а к самому автору.
   Форма ввода свёрнута до однострочного триггера (как в других местах),
   раскрывается по клику. Используем те же rp-add / rp-comment стили,
   что и на странице релиза, чтобы не плодить нового.
   ────────────────────────────────────────────────────────────────────────── */
function AuthorWall({
  entries,
  authorHandle
}) {
  const [expanded, setExpanded] = useState2(false);
  const [text, setText] = useState2("");
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-wall"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u041A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0438 \u0430\u0432\u0442\u043E\u0440\u0443"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0441\u043E\u043E\u0431\u0449\u0435\u043D\u0438\u044F \u0434\u043B\u044F ", /*#__PURE__*/React.createElement("b", null, authorHandle), " \u2014 \u043D\u0435 \u043A \u043A\u043E\u043D\u043A\u0440\u0435\u0442\u043D\u043E\u0439 \u0440\u0430\u0431\u043E\u0442\u0435, \u0430 \u0435\u043C\u0443 \u043B\u0438\u0447\u043D\u043E"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count",
    style: {
      marginLeft: "auto"
    }
  }, entries.length)), /*#__PURE__*/React.createElement("div", {
    className: "rp-add ap-wall__add" + (expanded ? " ap-wall__add--open" : "")
  }, !expanded ? /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "ap-wall__trigger",
    onClick: () => setExpanded(true),
    "aria-label": "\u041D\u0430\u043F\u0438\u0441\u0430\u0442\u044C \u043A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0439 \u0430\u0432\u0442\u043E\u0440\u0443"
  }, /*#__PURE__*/React.createElement(AP_I, {
    name: "chat",
    size: 14
  }), /*#__PURE__*/React.createElement("span", null, "\u041D\u0430\u043F\u0438\u0441\u0430\u0442\u044C ", authorHandle, "\u2026")) : /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "rp-add__h"
  }, "\u0421\u043E\u043E\u0431\u0449\u0435\u043D\u0438\u0435 \u0430\u0432\u0442\u043E\u0440\u0443"), /*#__PURE__*/React.createElement("textarea", {
    autoFocus: true,
    placeholder: "Напишите " + authorHandle + " — он увидит уведомление. Сюда обычно благодарят, задают вопросы по работам или зовут на пати.",
    value: text,
    onChange: e => setText(e.target.value)
  }), /*#__PURE__*/React.createElement("div", {
    className: "rp-add__foot"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rp-add__hint"
  }, "Markdown \xB7 \u0432\u0438\u0434\u0435\u043D \u0432\u0441\u0435\u043C \u043F\u043E\u0441\u0435\u0442\u0438\u0442\u0435\u043B\u044F\u043C \u043F\u0440\u043E\u0444\u0438\u043B\u044F"), /*#__PURE__*/React.createElement("div", {
    className: "zx-button-controls zx-button-controls--align-end"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--transparent zx-button--sm",
    type: "button",
    onClick: () => {
      setExpanded(false);
      setText("");
    }
  }, "\u041E\u0442\u043C\u0435\u043D\u0430"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--primary zx-button--sm",
    type: "button",
    disabled: !text.trim(),
    onClick: () => {
      setExpanded(false);
      setText("");
    }
  }, "\u041E\u0442\u043F\u0440\u0430\u0432\u0438\u0442\u044C"))))), entries.length > 0 ? /*#__PURE__*/React.createElement("div", {
    className: "ap-wall__list"
  }, entries.map(c => /*#__PURE__*/React.createElement("div", {
    key: c.id,
    className: "rp-comment"
  }, /*#__PURE__*/React.createElement("div", {
    className: "rp-comment__head"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault(),
    className: "rp-comment__user",
    style: {
      color: "inherit",
      textDecoration: "none"
    }
  }, c.by), /*#__PURE__*/React.createElement("span", {
    className: "rp-comment__date"
  }, c.date)), /*#__PURE__*/React.createElement("div", {
    className: "rp-comment__body"
  }, c.body)))) : /*#__PURE__*/React.createElement("p", {
    className: "rp-comment--empty"
  }, "\u0411\u0443\u0434\u044C\u0442\u0435 \u043F\u0435\u0440\u0432\u044B\u043C, \u043A\u0442\u043E \u043D\u0430\u043F\u0438\u0448\u0435\u0442 ", authorHandle, "."));
}
window.AuthorWall = AuthorWall;

/* ──────────────────────────────────────────────────────────────────────────
   PAGE SHELL
   ────────────────────────────────────────────────────────────────────────── */
function AuthorPage({
  preset = "moroz"
}) {
  const data = useMemo2(() => buildAuthorData(preset), [preset]);
  const {
    profile,
    pictures,
    tunes,
    prods,
    collaborators,
    collabGroups,
    comments,
    votes,
    wall
  } = data;
  const letter = profile.handle[0].toUpperCase();
  const LETTERS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ#".split("");
  const counters = {
    pictures: pictures.length,
    tunes: tunes.length,
    prods: prods.length,
    comments: comments.length || profile.counters.comments
  };
  const isEmpty = pictures.length + tunes.length + prods.length === 0;
  const worksRef = React.useRef(null);
  const [pendingTab, setPendingTab] = useState2(null);
  const jumpToTab = key => {
    setPendingTab(key);
    /* defer to next paint so the tab change has rendered */
    requestAnimationFrame(() => {
      worksRef.current?.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
    });
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "ap-root"
  }, /*#__PURE__*/React.createElement("nav", {
    className: "ap-crumbs",
    "aria-label": "breadcrumb"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0413\u043B\u0430\u0432\u043D\u0430\u044F"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0410\u0432\u0442\u043E\u0440\u044B"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, letter), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("span", {
    className: "here"
  }, profile.handle)), /*#__PURE__*/React.createElement("div", {
    className: "ap-letters"
  }, LETTERS.map(L => /*#__PURE__*/React.createElement("a", {
    key: L,
    href: "#",
    className: L === letter ? "ap-letters__on" : "",
    onClick: e => e.preventDefault()
  }, L))), /*#__PURE__*/React.createElement(AuthorHeader, {
    profile: profile,
    counters: counters,
    totalRatings: profile.ratings
  }), !isEmpty && /*#__PURE__*/React.createElement(MiniDashboard, {
    pictures: pictures,
    tunes: tunes,
    prods: prods,
    authorHandle: profile.handle,
    onJumpToTab: jumpToTab
  }), /*#__PURE__*/React.createElement("div", {
    ref: worksRef
  }, /*#__PURE__*/React.createElement(WorksNavigator, {
    pictures: pictures,
    tunes: tunes,
    prods: prods,
    authorHandle: profile.handle,
    initialTab: pendingTab
  })), /*#__PURE__*/React.createElement(Collaborators, {
    people: collaborators,
    groups: collabGroups
  }), /*#__PURE__*/React.createElement(FeedColumns, {
    comments: comments,
    votes: votes
  }), /*#__PURE__*/React.createElement(AuthorWall, {
    entries: wall,
    authorHandle: profile.handle
  }), isEmpty && /*#__PURE__*/React.createElement("section", {
    className: "ap-empty-cta"
  }, /*#__PURE__*/React.createElement("h3", null, "\u0423 \u044D\u0442\u043E\u0433\u043E \u0430\u0432\u0442\u043E\u0440\u0430 \u043F\u043E\u043A\u0430 \u043D\u0435\u0442 \u0440\u0430\u0431\u043E\u0442 \u0432 \u0430\u0440\u0445\u0438\u0432\u0435"), /*#__PURE__*/React.createElement("p", null, "\u0415\u0441\u043B\u0438 \u0432\u044B \u0437\u043D\u0430\u0435\u0442\u0435 \u0435\u0433\u043E \u043F\u0440\u043E\u0438\u0437\u0432\u0435\u0434\u0435\u043D\u0438\u044F \u2014 \u043F\u043E\u0434\u0435\u043B\u0438\u0442\u0435\u0441\u044C \u0444\u0430\u0439\u043B\u043E\u043C, \u043C\u044B \u0434\u043E\u0431\u0430\u0432\u0438\u043C \u0432 \u043A\u0430\u0442\u0430\u043B\u043E\u0433."), /*#__PURE__*/React.createElement("div", {
    className: "ap-empty-cta__actions"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--primary zx-button--sm"
  }, "\u041F\u0440\u0438\u0441\u043B\u0430\u0442\u044C \u043C\u0430\u0442\u0435\u0440\u0438\u0430\u043B"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined zx-button--sm"
  }, "\u0414\u043E\u043F\u043E\u043B\u043D\u0438\u0442\u044C \u043F\u0440\u043E\u0444\u0438\u043B\u044C"))));
}
window.AuthorPage = AuthorPage;
window.WorksNavigator = WorksNavigator;
window.Collaborators = Collaborators;
window.FeedColumns = FeedColumns;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/AuthorPageWorks.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/GroupPage.jsx
try { (() => {
/* GroupPage.jsx — Group page for ZXArt.
   Part 1: icon helper, monogram, role chips, header, subgroups, roster (HERO),
   best-works dashboard. The works navigator + feed + wall + shell live in
   GroupPageWorks.jsx. Visually consistent with the Author page (ap-* classes
   reused; gp-* added for group-specific blocks).
*/

const {
  useState: gUseState,
  useMemo: gUseMemo
} = React;

/* ── inline svg icons (24x24, design-system paths) ── */
function GP_I({
  name,
  size = 16
}) {
  const p = {
    code: "M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0L19.2 12l-4.6-4.6L16 6l6 6-6 6-1.4-1.4z",
    image: "M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm0 13l4-4 3 3 5-5 3 3V5H5v12z",
    music: "M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6z",
    game: "M21 6H3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2zM11 13H8v3H6v-3H3v-2h3V8h2v3h3v2zm4.5 2a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm4-3a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z",
    person: "M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0 2c-3 0-9 1.5-9 4.5V21h18v-2.5c0-3-6-4.5-9-4.5z",
    people: "M16 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-8 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm0 2c-2.7 0-8 1.3-8 4v3h8v-3c0-1 .4-1.9 1.1-2.6C8.8 13.1 8.4 13 8 13zm8 0c-.4 0-.9 0-1.4.1 1.4 1 2.4 2.3 2.4 3.9v3h7v-3c0-2.7-5.3-4-8-4z",
    location: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z",
    link: "M3.9 12a4.1 4.1 0 0 1 4.1-4.1h4V6H8a6 6 0 1 0 0 12h4v-1.9H8A4.1 4.1 0 0 1 3.9 12zm5.1 1h6v-2H9v2zm7-7h-4v1.9h4a4.1 4.1 0 0 1 0 8.2h-4V18h4a6 6 0 0 0 0-12z",
    star: "M12 2l3.09 6.26 6.91 1-5 4.87L18.18 22 12 18.27 5.82 22l1.18-7.87-5-4.87 6.91-1z",
    chat: "M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h11c.55 0 1-.45 1-1z",
    chevron: "M16.6 8.6L12 13.2 7.4 8.6 6 10l6 6 6-6z",
    chevronUp: "M7.4 15.4L12 10.8l4.6 4.6L18 14l-6-6-6 6z",
    sort: "M3 18h6v-2H3v2zm0-5h12v-2H3v2zm0-7v2h18V6H3z",
    book: "M18 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zM6 4h5v8l-2.5-1.5L6 12V4z",
    disc: "M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 13a3 3 0 1 1 0-6 3 3 0 0 1 0 6z",
    publish: "M5 4v2h14V4H5zm0 10h4v6h6v-6h4l-7-7-7 7z",
    crack: "M13 2L4.5 12.5h6L9 22l9.5-12h-6L13 2z",
    award: "M12 2l2.39 4.84L19.78 8l-3.89 3.79.92 5.4L12 14.77 7.19 17.19l.92-5.4L4.22 8l5.39-1.16L12 2z",
    external: "M14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7zM19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7h-2v7z"
  };
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: "0 0 24 24",
    width: size,
    height: size,
    fill: "currentColor",
    "aria-hidden": "true",
    style: {
      flexShrink: 0
    }
  }, /*#__PURE__*/React.createElement("path", {
    d: p[name] || ""
  }));
}

/* ── group monogram: pixel mosaic tinted by id + abbreviation overlay ── */
function GpMonogram({
  abbr,
  seed = 7,
  size = 96
}) {
  const cols = 12,
    rows = 12;
  let s = seed * 9301 + 49297;
  const rand = () => {
    s = (s * 9301 + 49297) % 233280;
    return s / 233280;
  };
  const hue = seed * 47 % 360;
  const cells = [];
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      const v = rand();
      const l = 18 + Math.floor(v * 14); /* dark, low contrast */
      cells.push(/*#__PURE__*/React.createElement("rect", {
        key: `${x}-${y}`,
        x: x,
        y: y,
        width: 1,
        height: 1,
        fill: `oklch(${l}% 0.06 ${hue})`
      }));
    }
  }
  const label = (abbr || "?").slice(0, 4);
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: `0 0 ${cols} ${rows}`,
    width: size,
    height: size,
    style: {
      imageRendering: "pixelated",
      display: "block",
      borderRadius: 3,
      background: "#111"
    },
    shapeRendering: "crispEdges"
  }, cells, /*#__PURE__*/React.createElement("text", {
    x: cols / 2,
    y: cols / 2,
    fill: "#fff",
    fontSize: label.length > 3 ? 2.6 : 3.4,
    fontFamily: "monospace",
    fontWeight: "700",
    textAnchor: "middle",
    dominantBaseline: "central",
    style: {
      letterSpacing: "-0.02em"
    }
  }, label));
}

/* ── small pixel face for a member ── */
function GpFace({
  seed = 1,
  size = 40
}) {
  const cols = 12,
    rows = 12;
  let s = seed * 7919 + 311;
  const rand = () => {
    s = (s * 9301 + 49297) % 233280;
    return s / 233280;
  };
  const cells = [];
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      const cy = rows / 2,
        cx = cols / 2;
      const dy = (y - cy) / cy,
        dx = (x - cx) / cx;
      const inOval = dy * dy + dx * dx * 0.9 < 0.92;
      const v = rand();
      let c;
      if (!inOval) c = "#000";else if (y < 3) c = v > 0.4 ? "#000" : "#1c1c1c";else if (y > rows - 3) c = v > 0.6 ? "#171717" : "#0a0a0a";else c = v > 0.55 ? "#cfcfcf" : v > 0.3 ? "#7d7d7d" : "#2f2f2f";
      cells.push(/*#__PURE__*/React.createElement("rect", {
        key: `${x}-${y}`,
        x: x,
        y: y,
        width: 1,
        height: 1,
        fill: c
      }));
    }
  }
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: `0 0 ${cols} ${rows}`,
    width: size,
    height: size,
    style: {
      imageRendering: "pixelated",
      display: "block",
      borderRadius: 2,
      background: "#000"
    },
    shapeRendering: "crispEdges"
  }, cells);
}

/* ── member role chip ── */
function GpRoleChip({
  role,
  size = "md"
}) {
  const r = GROUP_ROLES[role];
  if (!r) return null;
  const iconMap = {
    code: "code",
    gfx: "image",
    music: "music",
    support: "person",
    text: "book",
    unknown: "person"
  };
  return /*#__PURE__*/React.createElement("span", {
    className: "gp-role-chip gp-role-chip--" + r.color + (size === "sm" ? " gp-role-chip--sm" : "")
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: iconMap[role],
    size: size === "sm" ? 10 : 12
  }), r.label);
}

/* ════════════════════════════════════════════════════════════════════════
   HEADER — identity (monogram + name + abbr + type + place + years),
   nature tags, summary sentence, external links.
   ════════════════════════════════════════════════════════════════════════ */
function GroupHeader({
  g
}) {
  const counts = {
    members: g.members.length,
    subs: g.subgroups.length,
    prods: g.prods.length,
    published: g.published.length,
    releases: g.releases.length
  };
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-header gp-header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-header__left"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-header__avatar"
  }, /*#__PURE__*/React.createElement(GpMonogram, {
    abbr: g.abbr,
    seed: g.id % 997
  }))), /*#__PURE__*/React.createElement("div", {
    className: "ap-header__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-header__title-row"
  }, /*#__PURE__*/React.createElement("h1", {
    className: "ap-header__name gp-header__name"
  }, g.abbr), /*#__PURE__*/React.createElement("div", {
    className: "gp-nature-tags"
  }, g.nature.map(n => /*#__PURE__*/React.createElement("span", {
    key: n,
    className: "gp-nature gp-nature--" + n,
    title: GROUP_NATURE[n].hint
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: n === "developer" ? "game" : n === "publisher" ? "publish" : "crack",
    size: 12
  }), GROUP_NATURE[n].label)))), /*#__PURE__*/React.createElement("div", {
    className: "gp-header__fullname"
  }, g.name), /*#__PURE__*/React.createElement("div", {
    className: "ap-header__bio gp-header__bio"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__name"
  }, g.type), /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__sep"
  }, "\xB7"), /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__loc"
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: "location",
    size: 12
  }), /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, g.country), g.city && /*#__PURE__*/React.createElement(React.Fragment, null, ",\xA0", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, g.city))), /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__sep"
  }, "\xB7"), /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__joined"
  }, "\u0410\u043A\u0442\u0438\u0432\u043D\u0430 ", /*#__PURE__*/React.createElement("code", null, g.years))), /*#__PURE__*/React.createElement("p", {
    className: "ap-stats-sentence"
  }, "\u0412 \u0441\u043E\u0441\u0442\u0430\u0432\u0435 ", /*#__PURE__*/React.createElement("b", null, counts.members), " ", pluralRuG(counts.members, ["участник", "участника", "участников"]), counts.subs > 0 && /*#__PURE__*/React.createElement(React.Fragment, null, " \u0438 ", /*#__PURE__*/React.createElement("b", null, counts.subs), " ", pluralRuG(counts.subs, ["подгруппа", "подгруппы", "подгрупп"])), ". ", [counts.prods > 0 && /*#__PURE__*/React.createElement(React.Fragment, {
    key: "p"
  }, "\u0432\u044B\u043F\u0443\u0441\u0442\u0438\u043B\u0430 ", /*#__PURE__*/React.createElement("b", null, counts.prods), " ", pluralRuG(counts.prods, ["свой продукт", "своих продукта", "своих продуктов"])), counts.published > 0 && /*#__PURE__*/React.createElement(React.Fragment, {
    key: "pub"
  }, "\u0438\u0437\u0434\u0430\u043B\u0430 ", /*#__PURE__*/React.createElement("b", null, counts.published), " ", pluralRuG(counts.published, ["чужую программу", "чужие программы", "чужих программ"])), counts.releases > 0 && /*#__PURE__*/React.createElement(React.Fragment, {
    key: "r"
  }, "\u0441\u043E\u0431\u0440\u0430\u043B\u0430 ", /*#__PURE__*/React.createElement("b", null, counts.releases), " ", pluralRuG(counts.releases, ["релиз", "релиза", "релизов"]), g.nature.includes("cracker") ? " (включая взломы)" : "")].filter(Boolean).reduce((acc, el, i, arr) => {
    const sep = i === 0 ? g.name ? "Группа " : "" : i === arr.length - 1 ? " и " : ", ";
    acc.push(sep, el);
    return acc;
  }, []).concat(counts.prods + counts.published + counts.releases > 0 ? ["."] : [])), g.links.length > 0 && /*#__PURE__*/React.createElement("div", {
    className: "ap-meta-row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-meta-row__label"
  }, "\u0421\u0441\u044B\u043B\u043A\u0438:"), /*#__PURE__*/React.createElement("div", {
    className: "ap-ext-links"
  }, g.links.map((l, i) => /*#__PURE__*/React.createElement("a", {
    key: i,
    href: "#",
    onClick: e => e.preventDefault(),
    title: l.label
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-ext-icon"
  }, l.icon), l.label))))));
}

/* ════════════════════════════════════════════════════════════════════════
   SUBGROUPS — child crews as clickable cards with mini stats.
   ════════════════════════════════════════════════════════════════════════ */
function Subgroups({
  subs,
  activeSub,
  onPick
}) {
  if (subs.length === 0) return null;
  return /*#__PURE__*/React.createElement("section", {
    className: "gp-subs"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u041F\u043E\u0434\u0433\u0440\u0443\u043F\u043F\u044B"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u043B\u043E\u043A\u0430\u043B\u044C\u043D\u044B\u0435 \u043A\u043E\u043C\u0430\u043D\u0434\u044B \u043F\u043E\u0434 \u043A\u0440\u044B\u043B\u043E\u043C \u0430\u0441\u0441\u043E\u0446\u0438\u0430\u0446\u0438\u0438 \u2014 \u043A\u043B\u0438\u043A\u043D\u0438\u0442\u0435, \u0447\u0442\u043E\u0431\u044B \u043E\u0442\u0444\u0438\u043B\u044C\u0442\u0440\u043E\u0432\u0430\u0442\u044C \u0441\u043E\u0441\u0442\u0430\u0432"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count",
    style: {
      marginLeft: "auto"
    }
  }, subs.length)), /*#__PURE__*/React.createElement("div", {
    className: "gp-subs__grid"
  }, subs.map(s => /*#__PURE__*/React.createElement("a", {
    key: s.id,
    href: "#",
    className: "gp-sub-card" + (activeSub === s.name ? " gp-sub-card--on" : ""),
    onClick: e => {
      e.preventDefault();
      onPick && onPick(activeSub === s.name ? "all" : s.name);
    }
  }, /*#__PURE__*/React.createElement(GpMonogram, {
    abbr: s.abbr,
    seed: s.id % 997,
    size: 44
  }), /*#__PURE__*/React.createElement("div", {
    className: "gp-sub-card__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "gp-sub-card__name"
  }, s.name), /*#__PURE__*/React.createElement("div", {
    className: "gp-sub-card__meta"
  }, /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement(GP_I, {
    name: "people",
    size: 11
  }), s.members), /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement(GP_I, {
    name: "game",
    size: 11
  }), s.prods), /*#__PURE__*/React.createElement("span", {
    className: "gp-sub-card__years"
  }, s.years)))))));
}

/* ════════════════════════════════════════════════════════════════════════
   ROSTER (HERO) — members with role + subgroup filters.
   ════════════════════════════════════════════════════════════════════════ */
function Roster({
  members,
  subgroups,
  activeSub,
  onPickSub
}) {
  const [roleFilter, setRoleFilter] = gUseState("all");
  const roleCounts = gUseMemo(() => {
    const c = {
      all: members.length
    };
    members.forEach(m => m.roles.forEach(r => {
      c[r] = (c[r] || 0) + 1;
    }));
    return c;
  }, [members]);
  const subNames = gUseMemo(() => subgroups.map(s => s.name), [subgroups]);
  const filtered = gUseMemo(() => members.filter(m => (activeSub === "all" || (m.subs || []).includes(activeSub)) && (roleFilter === "all" || m.roles.includes(roleFilter))), [members, activeSub, roleFilter]);
  const ROLE_ORDER = ["code", "gfx", "music", "support", "text", "unknown"];
  const presentRoles = ROLE_ORDER.filter(r => roleCounts[r] > 0);
  return /*#__PURE__*/React.createElement("section", {
    className: "gp-roster"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0421\u043E\u0441\u0442\u0430\u0432 \u0433\u0440\u0443\u043F\u043F\u044B"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u043B\u044E\u0434\u0438 \u0438 \u0438\u0445 \u0440\u043E\u043B\u0438; \u0443\u0447\u0430\u0441\u0442\u043D\u0438\u043A \u043C\u043E\u0436\u0435\u0442 \u0441\u043E\u0441\u0442\u043E\u044F\u0442\u044C \u0438 \u0432 \u043F\u043E\u0434\u0433\u0440\u0443\u043F\u043F\u0430\u0445"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count",
    style: {
      marginLeft: "auto"
    }
  }, members.length)), /*#__PURE__*/React.createElement("div", {
    className: "gp-roster__filters"
  }, subNames.length > 0 && /*#__PURE__*/React.createElement("div", {
    className: "gp-filter-line"
  }, /*#__PURE__*/React.createElement("span", {
    className: "gp-filter-line__label"
  }, "\u041F\u043E\u0434\u0433\u0440\u0443\u043F\u043F\u0430:"), /*#__PURE__*/React.createElement("button", {
    className: "ap-pill" + (activeSub === "all" ? " ap-pill--on" : ""),
    onClick: () => onPickSub("all")
  }, "\u0432\u0441\u044F \u0433\u0440\u0443\u043F\u043F\u0430 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-pill__num"
  }, members.length)), subgroups.map(s => {
    const n = members.filter(m => (m.subs || []).includes(s.name)).length;
    return /*#__PURE__*/React.createElement("button", {
      key: s.id,
      className: "ap-pill ap-pill--sub" + (activeSub === s.name ? " ap-pill--on" : ""),
      onClick: () => onPickSub(activeSub === s.name ? "all" : s.name)
    }, "\u21B3 ", s.name, " ", /*#__PURE__*/React.createElement("span", {
      className: "ap-pill__num"
    }, n));
  })), /*#__PURE__*/React.createElement("div", {
    className: "gp-filter-line"
  }, /*#__PURE__*/React.createElement("span", {
    className: "gp-filter-line__label"
  }, "\u0420\u043E\u043B\u044C:"), /*#__PURE__*/React.createElement("button", {
    className: "ap-pill" + (roleFilter === "all" ? " ap-pill--on" : ""),
    onClick: () => setRoleFilter("all")
  }, "\u0432\u0441\u0435 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-pill__num"
  }, members.length)), presentRoles.map(r => /*#__PURE__*/React.createElement("button", {
    key: r,
    className: "ap-pill ap-pill--role-" + GROUP_ROLES[r].color + (roleFilter === r ? " ap-pill--on" : ""),
    onClick: () => setRoleFilter(roleFilter === r ? "all" : r)
  }, GROUP_ROLES[r].label, " ", /*#__PURE__*/React.createElement("span", {
    className: "ap-pill__num"
  }, roleCounts[r]))))), (activeSub !== "all" || roleFilter !== "all") && /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__result",
    style: {
      margin: "0 0 10px 2px"
    }
  }, "\u041F\u043E\u043A\u0430\u0437\u0430\u043D\u043E ", /*#__PURE__*/React.createElement("b", null, filtered.length), " \u0438\u0437 ", members.length, activeSub !== "all" && /*#__PURE__*/React.createElement("span", null, " \xB7 \u043F\u043E\u0434\u0433\u0440\u0443\u043F\u043F\u0430 ", activeSub), roleFilter !== "all" && /*#__PURE__*/React.createElement("span", null, " \xB7 \u0440\u043E\u043B\u044C \xAB", GROUP_ROLES[roleFilter].label, "\xBB")), /*#__PURE__*/React.createElement("div", {
    className: "gp-roster__grid"
  }, filtered.map((m, i) => /*#__PURE__*/React.createElement("a", {
    key: m.handle,
    href: "#",
    className: "gp-member",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement(GpFace, {
    seed: m.handle.charCodeAt(0) * 13 + (m.handle.charCodeAt(1) || 7),
    size: 44
  }), /*#__PURE__*/React.createElement("div", {
    className: "gp-member__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "gp-member__top"
  }, /*#__PURE__*/React.createElement("span", {
    className: "gp-member__handle"
  }, m.handle), m.years && /*#__PURE__*/React.createElement("span", {
    className: "gp-member__years"
  }, m.years)), m.real && /*#__PURE__*/React.createElement("div", {
    className: "gp-member__real"
  }, m.real), /*#__PURE__*/React.createElement("div", {
    className: "gp-member__roles"
  }, m.roles.map(r => /*#__PURE__*/React.createElement(GpRoleChip, {
    key: r,
    role: r,
    size: "sm"
  }))), m.subs && m.subs.length > 0 && /*#__PURE__*/React.createElement("div", {
    className: "gp-member__subs"
  }, m.subs.map(sn => /*#__PURE__*/React.createElement("button", {
    key: sn,
    className: "gp-member__sub-tag",
    onClick: e => {
      e.preventDefault();
      e.stopPropagation();
      onPickSub(activeSub === sn ? "all" : sn);
    }
  }, "\u21B3 ", sn)))), m.works > 0 && /*#__PURE__*/React.createElement("div", {
    className: "gp-member__works"
  }, /*#__PURE__*/React.createElement("b", null, m.works), /*#__PURE__*/React.createElement("span", null, "\u0440\u0430\u0431\u043E\u0442"))))), filtered.length === 0 && /*#__PURE__*/React.createElement("div", {
    className: "ap-empty"
  }, "\u0412 \u044D\u0442\u043E\u0439 \u043F\u043E\u0434\u0433\u0440\u0443\u043F\u043F\u0435 \u043D\u0435\u0442 \u0443\u0447\u0430\u0441\u0442\u043D\u0438\u043A\u043E\u0432 \u0441 \u0442\u0430\u043A\u043E\u0439 \u0440\u043E\u043B\u044C\u044E."));
}

/* ════════════════════════════════════════════════════════════════════════
   BEST WORKS — top prods (pixel-art punch), like the author dashboard.
   ════════════════════════════════════════════════════════════════════════ */
function GroupBestWorks({
  prods,
  onJump
}) {
  if (prods.length === 0) return null;
  const top = gUseMemo(() => {
    const featured = prods.filter(p => p.featured);
    const rest = prods.filter(p => !p.featured).sort((a, b) => b.votes - a.votes || b.year - a.year);
    return [...featured, ...rest].slice(0, 10);
  }, [prods]);
  return /*#__PURE__*/React.createElement("section", {
    className: "gp-best"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0417\u0430\u043C\u0435\u0442\u043D\u044B\u0435 \u0440\u0430\u0431\u043E\u0442\u044B"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0441\u0430\u043C\u043E\u0435 \u0441\u0438\u043B\u044C\u043D\u043E\u0435 \u0438\u0437 \u043F\u0440\u043E\u0434\u0443\u043A\u0446\u0438\u0438 \u0433\u0440\u0443\u043F\u043F\u044B"), /*#__PURE__*/React.createElement("a", {
    className: "ap-dash-col__all",
    href: "#",
    style: {
      marginLeft: "auto"
    },
    onClick: e => {
      e.preventDefault();
      onJump && onJump();
    }
  }, "\u0432\u0441\u0435 \u0440\u0430\u0431\u043E\u0442\u044B \u2192")), /*#__PURE__*/React.createElement("div", {
    className: "gp-best__grid"
  }, top.map(p => /*#__PURE__*/React.createElement("a", {
    key: p.id,
    href: "#",
    className: "gp-best__card",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("div", {
    className: "gp-best__art"
  }, /*#__PURE__*/React.createElement(PixelArtSVG, {
    seed: p.id + 999,
    palette: paletteFor(p.id)
  }), p.place ? /*#__PURE__*/React.createElement("span", {
    className: "ap-dash-pic__place"
  }, p.place) : null), /*#__PURE__*/React.createElement("div", {
    className: "gp-best__title"
  }, p.title), /*#__PURE__*/React.createElement("div", {
    className: "gp-best__meta"
  }, /*#__PURE__*/React.createElement("span", {
    className: "zx-badge zx-badge--secondary"
  }, p.kind), /*#__PURE__*/React.createElement("span", {
    className: "gp-best__year"
  }, p.year)), p.party && /*#__PURE__*/React.createElement("div", {
    className: "gp-best__party"
  }, p.party, p.place ? `, ${p.place} место` : "")))));
}

/* ════════════════════════════════════════════════════════════════════════
   CONNECTIONS — external people the group worked with + groups it published.
   Mirrors the Author page "С кем работал" two-column layout.
   ════════════════════════════════════════════════════════════════════════ */
function GroupConnections({
  connections
}) {
  if (!connections) return null;
  const people = connections.people || [];
  const pub = connections.publishedGroups || [];
  if (people.length === 0 && pub.length === 0) return null;
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-collab"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0421\u0432\u044F\u0437\u0438"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0441 \u043A\u0435\u043C \u0433\u0440\u0443\u043F\u043F\u0430 \u0440\u0430\u0431\u043E\u0442\u0430\u043B\u0430 \u0438 \u0447\u044C\u0438 \u0440\u0430\u0431\u043E\u0442\u044B \u0438\u0437\u0434\u0430\u0432\u0430\u043B\u0430 \u2014 \u043F\u043E \u0447\u0438\u0441\u043B\u0443 \u0441\u043E\u0432\u043C\u0435\u0441\u0442\u043D\u044B\u0445 \u0440\u0430\u0431\u043E\u0442")), /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__cols"
  }, people.length > 0 && /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col-h"
  }, "\u0427\u0430\u0441\u0442\u044B\u0435 \u0441\u043E\u0430\u0432\u0442\u043E\u0440\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count"
  }, people.length)), /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__people"
  }, people.map(p => /*#__PURE__*/React.createElement("a", {
    key: p.handle,
    href: "#",
    className: "ap-collab__person",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement(GpFace, {
    seed: p.handle.charCodeAt(0) * 23 + (p.handle.charCodeAt(1) || 3),
    size: 32
  }), /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__person-body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__person-name"
  }, p.handle), /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__person-groups"
  }, p.real ? p.real + " · " : "", p.role, p.via && p.via !== "—" ? /*#__PURE__*/React.createElement(React.Fragment, null, " \xB7 ", /*#__PURE__*/React.createElement("span", {
    className: "gp-conn__via"
  }, p.via)) : null)), /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__person-total"
  }, /*#__PURE__*/React.createElement("b", null, p.joint), /*#__PURE__*/React.createElement("span", null, "\u0441\u043E\u0432\u043C.")))))), pub.length > 0 ? /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col-h"
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: "publish",
    size: 14
  }), "\u041A\u043E\u0433\u043E \u0438\u0437\u0434\u0430\u0432\u0430\u043B\u0438 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count"
  }, pub.length)), /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__groups"
  }, pub.map(g => /*#__PURE__*/React.createElement("a", {
    key: g.name,
    href: "#",
    className: "ap-collab__group",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__group-head"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-collab__group-name"
  }, g.name), g.years && /*#__PURE__*/React.createElement("span", {
    className: "ap-collab__group-years"
  }, g.years)), /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__group-meta"
  }, "\u0438\u0437\u0434\u0430\u043D\u043E ", /*#__PURE__*/React.createElement("b", null, g.count), " ", pluralRuG(g.count, ["релиз", "релиза", "релизов"]), g.note ? /*#__PURE__*/React.createElement(React.Fragment, null, " \xB7 ", g.note) : null))))) : /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col-h"
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: "publish",
    size: 14
  }), "\u041A\u043E\u0433\u043E \u0438\u0437\u0434\u0430\u0432\u0430\u043B\u0438"), /*#__PURE__*/React.createElement("div", {
    className: "ap-empty"
  }, "\u0413\u0440\u0443\u043F\u043F\u0430 \u043D\u0435 \u0432\u044B\u0441\u0442\u0443\u043F\u0430\u043B\u0430 \u0438\u0437\u0434\u0430\u0442\u0435\u043B\u0435\u043C \u2014 \u0442\u043E\u043B\u044C\u043A\u043E \u0441\u0432\u043E\u044F \u043F\u0440\u043E\u0434\u0443\u043A\u0446\u0438\u044F."))));
}
Object.assign(window, {
  GP_I,
  GpMonogram,
  GpFace,
  GpRoleChip,
  GroupHeader,
  Subgroups,
  Roster,
  GroupBestWorks,
  GroupConnections
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/GroupPage.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/GroupPageWorks.jsx
try { (() => {
/* GroupPageWorks.jsx — works navigator (3 tabs: own prods / published / releases),
   scene-press mentions, activity feed, group wall, and the GroupPage shell. */

const {
  useState: gUseState2,
  useMemo: gUseMemo2
} = React;
const GP_PAGE_PROD = 12;
const GP_PAGE_REL = 14;

/* ── pagination (same look as author page) ── */
function GpPagination({
  page,
  total,
  perPage,
  onChange
}) {
  const pages = Math.max(1, Math.ceil(total / perPage));
  if (pages <= 1) return null;
  const nums = [];
  const push = n => nums.push(n);
  push(1);
  for (let p = Math.max(2, page - 2); p <= Math.min(pages - 1, page + 2); p++) push(p);
  push(pages);
  const dedup = [...new Set(nums)];
  const out = [];
  for (let i = 0; i < dedup.length; i++) {
    if (i > 0 && dedup[i] - dedup[i - 1] > 1) out.push("…");
    out.push(dedup[i]);
  }
  return /*#__PURE__*/React.createElement("nav", {
    className: "ap-pagination",
    "aria-label": "pagination"
  }, /*#__PURE__*/React.createElement("button", {
    className: "ap-page-btn",
    disabled: page === 1,
    onClick: () => onChange(page - 1)
  }, "\u2190 \u043F\u0440\u0435\u0434."), out.map((n, i) => n === "…" ? /*#__PURE__*/React.createElement("span", {
    key: i,
    className: "ap-page-gap"
  }, "\u2026") : /*#__PURE__*/React.createElement("button", {
    key: i,
    className: "ap-page-btn" + (n === page ? " ap-page-btn--active" : ""),
    onClick: () => onChange(n)
  }, n)), /*#__PURE__*/React.createElement("button", {
    className: "ap-page-btn",
    disabled: page === pages,
    onClick: () => onChange(page + 1)
  }, "\u0441\u043B\u0435\u0434. \u2192"), /*#__PURE__*/React.createElement("span", {
    className: "ap-pagination__total"
  }, "\u0438\u0437 ", pages, " \xB7 \u0432\u0441\u0435\u0433\u043E ", total));
}
function GpYearRail({
  year,
  count
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "ap-year-rail"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-year-rail__dot"
  }), /*#__PURE__*/React.createElement("div", {
    className: "ap-year-rail__year"
  }, year), /*#__PURE__*/React.createElement("div", {
    className: "ap-year-rail__count"
  }, count));
}

/* ── TAB 1: own prods (developer) ── */
function OwnProdsTab({
  prods,
  groupAbbr
}) {
  const [page, setPage] = gUseState2(1);
  const [kind, setKind] = gUseState2("all");
  const [sort, setSort] = gUseState2("year-desc");
  const kinds = gUseMemo2(() => {
    const m = {};
    prods.forEach(p => {
      m[p.kind] = (m[p.kind] || 0) + 1;
    });
    return m;
  }, [prods]);
  const filtered = gUseMemo2(() => prods.filter(p => kind === "all" || p.kind === kind), [prods, kind]);
  const sorted = gUseMemo2(() => {
    const a = [...filtered];
    if (sort === "year-desc") a.sort((x, y) => y.year - x.year || y.votes - x.votes);
    if (sort === "year-asc") a.sort((x, y) => x.year - y.year);
    if (sort === "votes") a.sort((x, y) => y.votes - x.votes);
    return a;
  }, [filtered, sort]);
  const start = (page - 1) * GP_PAGE_PROD;
  const items = sorted.slice(start, start + GP_PAGE_PROD);
  const grouped = sort === "year-desc" || sort === "year-asc";
  const groups = gUseMemo2(() => {
    if (!grouped) return [{
      year: null,
      items
    }];
    const m = new Map();
    items.forEach(p => {
      if (!m.has(p.year)) m.set(p.year, []);
      m.get(p.year).push(p);
    });
    const arr = [...m.entries()].map(([year, its]) => ({
      year,
      items: its
    }));
    if (sort === "year-asc") arr.sort((a, b) => a.year - b.year);else arr.sort((a, b) => b.year - a.year);
    return arr;
  }, [items, sort, grouped]);
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group ap-toolbar__group--cats"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-toolbar__group-title"
  }, "\u041A\u0430\u0442\u0435\u0433\u043E\u0440\u0438\u044F:"), /*#__PURE__*/React.createElement("button", {
    className: "ap-pill" + (kind === "all" ? " ap-pill--on" : ""),
    onClick: () => {
      setKind("all");
      setPage(1);
    }
  }, "\u0432\u0441\u0435 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-pill__num"
  }, prods.length)), Object.entries(kinds).map(([k, n]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    className: "ap-pill" + (kind === k ? " ap-pill--on" : ""),
    onClick: () => {
      setKind(k);
      setPage(1);
    }
  }, k, " ", /*#__PURE__*/React.createElement("span", {
    className: "ap-pill__num"
  }, n)))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__sep"
  }), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group"
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: "sort",
    size: 14
  }), /*#__PURE__*/React.createElement("span", null, "\u0421\u043E\u0440\u0442\u0438\u0440\u043E\u0432\u043A\u0430:"), [["year-desc", "новее"], ["year-asc", "старее"], ["votes", "по голосам"]].map(([k, l]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    className: "ap-pill" + (sort === k ? " ap-pill--on" : ""),
    onClick: () => {
      setSort(k);
      setPage(1);
    }
  }, l)))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__result"
  }, "\u041D\u0430\u0439\u0434\u0435\u043D\u043E ", /*#__PURE__*/React.createElement("b", null, filtered.length), " ", pluralRuG(filtered.length, ["продукт", "продукта", "продуктов"]), kind !== "all" && /*#__PURE__*/React.createElement("span", null, " \xB7 ", kind)), groups.map((g, gi) => /*#__PURE__*/React.createElement("div", {
    key: gi,
    className: "ap-year-group ap-year-group--prod"
  }, g.year != null && /*#__PURE__*/React.createElement(GpYearRail, {
    year: g.year,
    count: g.items.length
  }), /*#__PURE__*/React.createElement("div", {
    className: "ap-prod-grid"
  }, g.items.map(p => /*#__PURE__*/React.createElement("div", {
    key: p.id,
    className: "ap-prodwrap"
  }, (p.kind || p.party) && /*#__PURE__*/React.createElement("div", {
    className: "ap-prodwrap__roles"
  }, p.party && /*#__PURE__*/React.createElement("span", {
    className: "ap-prodwrap__intro"
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: "award",
    size: 12
  }), /*#__PURE__*/React.createElement("span", null, p.party, p.place ? `, ${p.place} место` : ""))), /*#__PURE__*/React.createElement(ProdCard, {
    prod: {
      id: p.id,
      title: p.title,
      palette: paletteFor(p.id),
      kind: p.kind,
      authors: [groupAbbr, ...(p.coGroups || [])],
      party: null,
      place: null,
      year: p.year,
      stars: p.stars,
      votes: p.votes
    }
  })))))), /*#__PURE__*/React.createElement(GpPagination, {
    page: page,
    total: filtered.length,
    perPage: GP_PAGE_PROD,
    onChange: setPage
  }));
}

/* ── TAB 2: published prods (publisher; authored by others) ── */
function PublishedTab({
  published,
  groupAbbr
}) {
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__result",
    style: {
      marginTop: 0
    }
  }, "\u0413\u0440\u0443\u043F\u043F\u0430 \u0432\u044B\u0441\u0442\u0443\u043F\u0438\u043B\u0430 \u0438\u0437\u0434\u0430\u0442\u0435\u043B\u0435\u043C \u0434\u043B\u044F ", /*#__PURE__*/React.createElement("b", null, published.length), " ", pluralRuG(published.length, ["программы", "программ", "программ"]), " \u2014 \u0430\u0432\u0442\u043E\u0440\u044B \u0441\u0442\u043E\u0440\u043E\u043D\u043D\u0438\u0435."), /*#__PURE__*/React.createElement("div", {
    className: "ap-prod-grid"
  }, published.map(p => /*#__PURE__*/React.createElement("div", {
    key: p.id,
    className: "ap-prodwrap"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-prodwrap__roles"
  }, /*#__PURE__*/React.createElement("span", {
    className: "gp-pubtag"
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: "publish",
    size: 11
  }), "\u0438\u0437\u0434\u0430\u043D\u043E ", groupAbbr), p.by && p.by.length > 0 && /*#__PURE__*/React.createElement("span", {
    className: "ap-prodwrap__intro"
  }, /*#__PURE__*/React.createElement("span", null, "\u0441\u043E\u0432\u043C. \u0441 ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, p.by.join(", "))))), /*#__PURE__*/React.createElement(ProdCard, {
    prod: {
      id: p.id,
      title: p.title,
      palette: paletteFor(p.id),
      kind: p.kind,
      authors: p.authors || [],
      party: null,
      place: null,
      year: p.year,
      stars: p.stars,
      votes: p.votes
    }
  })))));
}

/* ── TAB 3: releases (incl. cracks / adaptations / mods) ── */
function ReleasesTab({
  releases,
  groupAbbr
}) {
  const [page, setPage] = gUseState2(1);
  const [typeFilter, setTypeFilter] = gUseState2("all");
  const typeCounts = gUseMemo2(() => {
    const c = {
      all: releases.length
    };
    releases.forEach(r => {
      c[r.type] = (c[r.type] || 0) + 1;
    });
    return c;
  }, [releases]);
  const filtered = gUseMemo2(() => releases.filter(r => typeFilter === "all" || r.type === typeFilter), [releases, typeFilter]);
  const sorted = gUseMemo2(() => [...filtered].sort((a, b) => b.year - a.year || a.title.localeCompare(b.title)), [filtered]);
  const start = (page - 1) * GP_PAGE_REL;
  const items = sorted.slice(start, start + GP_PAGE_REL);
  const TYPE_ORDER = ["original", "rerelease", "adaptation", "mod", "demoversion", "crack", "unknown"];
  const presentTypes = TYPE_ORDER.filter(t => typeCounts[t] > 0);
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__group"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-toolbar__group-title"
  }, "\u0422\u0438\u043F \u0440\u0435\u043B\u0438\u0437\u0430:"), /*#__PURE__*/React.createElement("button", {
    className: "ap-pill" + (typeFilter === "all" ? " ap-pill--on" : ""),
    onClick: () => {
      setTypeFilter("all");
      setPage(1);
    }
  }, "\u0432\u0441\u0435 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-pill__num"
  }, releases.length)), presentTypes.map(t => /*#__PURE__*/React.createElement("button", {
    key: t,
    className: "ap-pill" + (typeFilter === t ? " ap-pill--on" : ""),
    onClick: () => {
      setTypeFilter(typeFilter === t ? "all" : t);
      setPage(1);
    }
  }, RELEASE_TYPE_LABELS[t], " ", /*#__PURE__*/React.createElement("span", {
    className: "ap-pill__num"
  }, typeCounts[t]))))), /*#__PURE__*/React.createElement("div", {
    className: "ap-toolbar__result"
  }, "\u041D\u0430\u0439\u0434\u0435\u043D\u043E ", /*#__PURE__*/React.createElement("b", null, filtered.length), " ", pluralRuG(filtered.length, ["релиз", "релиза", "релизов"]), typeFilter !== "all" && /*#__PURE__*/React.createElement("span", null, " \xB7 ", RELEASE_TYPE_LABELS[typeFilter])), /*#__PURE__*/React.createElement("div", {
    className: "gp-rel-list"
  }, items.map(r => /*#__PURE__*/React.createElement("a", {
    key: r.id,
    href: "#",
    className: "gp-rel-row",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("div", {
    className: "gp-rel-row__cover"
  }, /*#__PURE__*/React.createElement(PixelArtSVG, {
    seed: r.id + 17,
    palette: paletteFor(r.id)
  })), /*#__PURE__*/React.createElement("div", {
    className: "gp-rel-row__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "gp-rel-row__top"
  }, /*#__PURE__*/React.createElement("span", {
    className: "gp-rel-row__title"
  }, r.title), /*#__PURE__*/React.createElement("span", {
    className: "zx-release-type-badge zx-release-type-badge--" + r.type
  }, RELEASE_TYPE_LABELS[r.type])), /*#__PURE__*/React.createElement("div", {
    className: "gp-rel-row__meta"
  }, /*#__PURE__*/React.createElement("span", {
    className: "gp-rel-row__fmt"
  }, r.format), r.authors && r.authors.length > 0 && /*#__PURE__*/React.createElement("span", null, "\xB7 ", r.authors.join(", ")), r.coPub && r.coPub.length > 0 && /*#__PURE__*/React.createElement("span", null, "\xB7 \u0438\u0437\u0434\u0430\u043D\u043E \u0441 ", r.coPub.join(", ")))), /*#__PURE__*/React.createElement("span", {
    className: "gp-rel-row__year"
  }, r.year)))), /*#__PURE__*/React.createElement(GpPagination, {
    page: page,
    total: filtered.length,
    perPage: GP_PAGE_REL,
    onChange: setPage
  }));
}

/* ── works navigator shell (tabs) ── */
function GroupWorks({
  g,
  jumpTab
}) {
  const tabs = [];
  if (g.prods.length > 0) tabs.push({
    id: "prods",
    label: "Свои продукты",
    icon: "game",
    count: g.prods.length
  });
  if (g.published.length > 0) tabs.push({
    id: "published",
    label: "Издано",
    icon: "publish",
    count: g.published.length
  });
  if (g.releases.length > 0) tabs.push({
    id: "releases",
    label: "Релизы и кряки",
    icon: "disc",
    count: g.releases.length
  });
  const [tab, setTab] = gUseState2(tabs[0]?.id || "prods");
  React.useEffect(() => {
    if (jumpTab && tabs.some(t => t.id === jumpTab)) setTab(jumpTab);
  }, [jumpTab]);
  if (tabs.length === 0) return null;
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-works"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0412\u0441\u0435 \u0440\u0430\u0431\u043E\u0442\u044B \u0433\u0440\u0443\u043F\u043F\u044B"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0440\u0430\u0437\u0440\u0430\u0431\u043E\u0442\u043A\u0430, \u0438\u0437\u0434\u0430\u0442\u0435\u043B\u044C\u0441\u0442\u0432\u043E \u0438 \u0440\u0435\u043B\u0438\u0437\u044B \u2014 \u0431\u0435\u0437 \u043F\u0435\u0440\u0435\u0437\u0430\u0433\u0440\u0443\u0437\u043A\u0438")), /*#__PURE__*/React.createElement("div", {
    className: "ap-worktabs"
  }, tabs.map(t => /*#__PURE__*/React.createElement("button", {
    key: t.id,
    className: "ap-worktab" + (tab === t.id ? " ap-worktab--on" : ""),
    onClick: () => setTab(t.id)
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: t.icon,
    size: 14
  }), t.label, /*#__PURE__*/React.createElement("span", {
    className: "ap-worktab__count"
  }, t.count)))), /*#__PURE__*/React.createElement("div", {
    className: "ap-worktab-body"
  }, tab === "prods" && /*#__PURE__*/React.createElement(OwnProdsTab, {
    prods: g.prods,
    groupAbbr: g.abbr
  }), tab === "published" && /*#__PURE__*/React.createElement(PublishedTab, {
    published: g.published,
    groupAbbr: g.abbr
  }), tab === "releases" && /*#__PURE__*/React.createElement(ReleasesTab, {
    releases: g.releases,
    groupAbbr: g.abbr
  })));
}

/* ── scene-press mentions ── */
function GroupMentions({
  mentions
}) {
  if (!mentions || mentions.length === 0) return null;
  return /*#__PURE__*/React.createElement("section", {
    className: "gp-mentions"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0423\u043F\u043E\u043C\u0438\u043D\u0430\u043D\u0438\u044F \u0432 \u043F\u0440\u0435\u0441\u0441\u0435"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0441\u0442\u0430\u0442\u044C\u0438 \u0438 \u0438\u043D\u0442\u0435\u0440\u0432\u044C\u044E \u0441\u0446\u0435\u043D\u044B, \u0433\u0434\u0435 \u0437\u0430\u0441\u0432\u0435\u0442\u0438\u043B\u0430\u0441\u044C \u0433\u0440\u0443\u043F\u043F\u0430"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count",
    style: {
      marginLeft: "auto"
    }
  }, mentions.length)), /*#__PURE__*/React.createElement("div", {
    className: "gp-mentions__list"
  }, mentions.map((m, i) => /*#__PURE__*/React.createElement("a", {
    key: i,
    href: "#",
    className: "gp-mention",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("div", {
    className: "gp-mention__head"
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: "book",
    size: 13
  }), /*#__PURE__*/React.createElement("span", {
    className: "gp-mention__pub"
  }, m.pub), /*#__PURE__*/React.createElement("span", {
    className: "gp-mention__year"
  }, m.year), /*#__PURE__*/React.createElement("span", {
    className: "gp-mention__sep"
  }, "/"), /*#__PURE__*/React.createElement("span", {
    className: "gp-mention__section"
  }, m.section)), /*#__PURE__*/React.createElement("div", {
    className: "gp-mention__desc"
  }, m.desc)))));
}

/* ── activity feed (comments + votes around the group's works) ── */
function GroupFeed({
  comments,
  votes
}) {
  if ((!comments || comments.length === 0) && (!votes || votes.length === 0)) return null;
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-feed"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0410\u043A\u0442\u0438\u0432\u043D\u043E\u0441\u0442\u044C \u0432\u043E\u043A\u0440\u0443\u0433 \u0440\u0430\u0431\u043E\u0442"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0441\u0432\u0435\u0436\u0438\u0435 \u043A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0438 \u0438 \u0433\u043E\u043B\u043E\u0441\u0430 \u043F\u043E \u043F\u0440\u043E\u0434\u0443\u043A\u0446\u0438\u0438 \u0433\u0440\u0443\u043F\u043F\u044B")), /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__cols"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col-h"
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: "chat",
    size: 14
  }), "\u041A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0438 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count"
  }, comments.length)), /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__comments"
  }, comments.map(c => /*#__PURE__*/React.createElement("article", {
    key: c.id,
    className: "ap-fcomment"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-fcomment__head"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault(),
    className: "ap-fcomment__user"
  }, c.by), /*#__PURE__*/React.createElement("span", {
    className: "ap-fcomment__work"
  }, "\u043A ", c.workType === "release" ? "релизу" : "работе", " ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, "\xAB", c.workTitle, "\xBB")), /*#__PURE__*/React.createElement("span", {
    className: "ap-fcomment__date"
  }, c.date)), /*#__PURE__*/React.createElement("div", {
    className: "ap-fcomment__body"
  }, c.body))))), /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col-h"
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: "star",
    size: 14
  }), "\u0413\u043E\u043B\u043E\u0441\u0430 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count"
  }, votes.length)), /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__votes"
  }, votes.map(v => /*#__PURE__*/React.createElement("div", {
    key: v.id,
    className: "ap-fvote"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-fvote__date"
  }, v.date.slice(5)), /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault(),
    className: "ap-fvote__user"
  }, v.by), /*#__PURE__*/React.createElement("span", {
    className: "ap-fvote__stars",
    title: `${v.score}/5`
  }, Array.from({
    length: 5
  }).map((_, i) => /*#__PURE__*/React.createElement("span", {
    key: i,
    className: i < v.score ? "on" : "off"
  }, "\u2605"))), /*#__PURE__*/React.createElement("span", {
    className: "ap-fvote__work"
  }, "\u2192 ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, "\xAB", v.workTitle, "\xBB")), /*#__PURE__*/React.createElement("span", {
    className: "ap-fvote__type"
  }, v.workType === "release" ? "рел." : "прог.")))))));
}

/* ── group wall (messages to the group, not to a work) ── */
function GroupWall({
  entries,
  groupAbbr
}) {
  const [expanded, setExpanded] = gUseState2(false);
  const [text, setText] = gUseState2("");
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-wall"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0421\u043E\u043E\u0431\u0449\u0435\u043D\u0438\u044F \u0433\u0440\u0443\u043F\u043F\u0435"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0434\u043B\u044F ", /*#__PURE__*/React.createElement("b", null, groupAbbr), " \u2014 \u043D\u0435 \u043A \u043A\u043E\u043D\u043A\u0440\u0435\u0442\u043D\u043E\u0439 \u0440\u0430\u0431\u043E\u0442\u0435"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count",
    style: {
      marginLeft: "auto"
    }
  }, entries.length)), /*#__PURE__*/React.createElement("div", {
    className: "rp-add ap-wall__add" + (expanded ? " ap-wall__add--open" : "")
  }, !expanded ? /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "ap-wall__trigger",
    onClick: () => setExpanded(true)
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: "chat",
    size: 14
  }), /*#__PURE__*/React.createElement("span", null, "\u041D\u0430\u043F\u0438\u0441\u0430\u0442\u044C ", groupAbbr, "\u2026")) : /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "rp-add__h"
  }, "\u0421\u043E\u043E\u0431\u0449\u0435\u043D\u0438\u0435 \u0433\u0440\u0443\u043F\u043F\u0435"), /*#__PURE__*/React.createElement("textarea", {
    autoFocus: true,
    placeholder: "Напишите " + groupAbbr + " — вопросы, благодарности, находки материалов.",
    value: text,
    onChange: e => setText(e.target.value)
  }), /*#__PURE__*/React.createElement("div", {
    className: "rp-add__foot"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rp-add__hint"
  }, "Markdown \xB7 \u0432\u0438\u0434\u043D\u043E \u0432\u0441\u0435\u043C \u043F\u043E\u0441\u0435\u0442\u0438\u0442\u0435\u043B\u044F\u043C"), /*#__PURE__*/React.createElement("div", {
    className: "zx-button-controls zx-button-controls--align-end"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--transparent zx-button--sm",
    type: "button",
    onClick: () => {
      setExpanded(false);
      setText("");
    }
  }, "\u041E\u0442\u043C\u0435\u043D\u0430"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--primary zx-button--sm",
    type: "button",
    disabled: !text.trim(),
    onClick: () => {
      setExpanded(false);
      setText("");
    }
  }, "\u041E\u0442\u043F\u0440\u0430\u0432\u0438\u0442\u044C"))))), entries.length > 0 ? /*#__PURE__*/React.createElement("div", {
    className: "ap-wall__list"
  }, entries.map(c => /*#__PURE__*/React.createElement("div", {
    key: c.id,
    className: "rp-comment"
  }, /*#__PURE__*/React.createElement("div", {
    className: "rp-comment__head"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault(),
    className: "rp-comment__user",
    style: {
      color: "inherit",
      textDecoration: "none"
    }
  }, c.by), /*#__PURE__*/React.createElement("span", {
    className: "rp-comment__date"
  }, c.date)), /*#__PURE__*/React.createElement("div", {
    className: "rp-comment__body"
  }, c.body)))) : /*#__PURE__*/React.createElement("p", {
    className: "rp-comment--empty"
  }, "\u0411\u0443\u0434\u044C\u0442\u0435 \u043F\u0435\u0440\u0432\u044B\u043C, \u043A\u0442\u043E \u043D\u0430\u043F\u0438\u0448\u0435\u0442 ", groupAbbr, "."));
}

/* ════════════════════════════════════════════════════════════════════════
   PAGE SHELL
   ════════════════════════════════════════════════════════════════════════ */
function GroupPage({
  preset = "rush"
}) {
  const g = GROUP_PRESETS[preset];
  const letter = g.abbr[0].toUpperCase();
  const LETTERS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ#".split("");
  const [activeSub, setActiveSub] = gUseState2("all");
  const [tab, setTab] = gUseState2("overview");
  const tabsRef = React.useRef(null);

  /* reset on preset change */
  React.useEffect(() => {
    setActiveSub("all");
    setTab("overview");
  }, [preset]);
  const worksCount = g.prods.length + g.published.length + g.releases.length;
  const activityCount = (g.comments?.length || 0) + (g.votes?.length || 0);
  const connCount = (g.connections?.people?.length || 0) + (g.connections?.publishedGroups?.length || 0);
  const TABS = [{
    id: "overview",
    label: "Обзор",
    icon: "star"
  }, {
    id: "works",
    label: "Работы",
    icon: "game",
    count: worksCount
  }, {
    id: "group",
    label: "Группа",
    icon: "people",
    count: g.members.length
  }, {
    id: "links",
    label: "Связи",
    icon: "link",
    count: connCount
  }, {
    id: "media",
    label: "Медиа",
    icon: "book",
    count: g.mentions?.length || 0
  }, {
    id: "activity",
    label: "Активность",
    icon: "chat",
    count: activityCount
  }].filter(t => t.count === undefined || t.count > 0 || t.id === "group");
  const go = id => {
    setTab(id);
    requestAnimationFrame(() => {
      const top = tabsRef.current?.getBoundingClientRect().top ?? 0;
      if (top < 0) tabsRef.current?.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
    });
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "ap-root"
  }, /*#__PURE__*/React.createElement("nav", {
    className: "ap-crumbs",
    "aria-label": "breadcrumb"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0413\u043B\u0430\u0432\u043D\u0430\u044F"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0413\u0440\u0443\u043F\u043F\u044B"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, letter), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("span", {
    className: "here"
  }, g.abbr)), /*#__PURE__*/React.createElement("div", {
    className: "ap-letters"
  }, LETTERS.map(L => /*#__PURE__*/React.createElement("a", {
    key: L,
    href: "#",
    className: L === letter ? "ap-letters__on" : "",
    onClick: e => e.preventDefault()
  }, L))), /*#__PURE__*/React.createElement(GroupHeader, {
    g: g
  }), /*#__PURE__*/React.createElement("div", {
    className: "gp-tabbar",
    ref: tabsRef
  }, TABS.map(t => /*#__PURE__*/React.createElement("button", {
    key: t.id,
    className: "gp-tab" + (tab === t.id ? " gp-tab--on" : ""),
    onClick: () => go(t.id)
  }, /*#__PURE__*/React.createElement(GP_I, {
    name: t.icon,
    size: 15
  }), /*#__PURE__*/React.createElement("span", null, t.label), t.count > 0 && /*#__PURE__*/React.createElement("span", {
    className: "gp-tab__count"
  }, t.count)))), /*#__PURE__*/React.createElement("div", {
    className: "gp-tabview"
  }, tab === "overview" && /*#__PURE__*/React.createElement(GroupBestWorks, {
    prods: g.prods,
    onJump: () => go("works")
  }), tab === "works" && /*#__PURE__*/React.createElement(GroupWorks, {
    g: g
  }), tab === "group" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Subgroups, {
    subs: g.subgroups,
    activeSub: activeSub,
    onPick: setActiveSub
  }), /*#__PURE__*/React.createElement(Roster, {
    members: g.members,
    subgroups: g.subgroups,
    activeSub: activeSub,
    onPickSub: setActiveSub
  })), tab === "links" && /*#__PURE__*/React.createElement(GroupConnections, {
    connections: g.connections
  }), tab === "media" && /*#__PURE__*/React.createElement(GroupMentions, {
    mentions: g.mentions
  }), tab === "activity" && /*#__PURE__*/React.createElement(GroupFeed, {
    comments: g.comments,
    votes: g.votes
  })));
}
Object.assign(window, {
  GroupPage,
  GroupWorks,
  GroupMentions,
  GroupFeed
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/GroupPageWorks.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/Header.jsx
try { (() => {
/** Header — top nav (desktop). Mirrors ng-zxart header layout. */
const Header = ({
  active,
  onNavigate,
  theme,
  onToggleTheme
}) => {
  const items = [{
    id: "pictures",
    label: "Pictures"
  }, {
    id: "music",
    label: "Music"
  }, {
    id: "prods",
    label: "Prods"
  }, {
    id: "authors",
    label: "Authors"
  }, {
    id: "groups",
    label: "Groups"
  }, {
    id: "parties",
    label: "Parties"
  }];
  return /*#__PURE__*/React.createElement("header", {
    className: "zx-header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "zx-header__inner"
  }, /*#__PURE__*/React.createElement("a", {
    className: "zx-header__logo",
    href: "#",
    onClick: e => {
      e.preventDefault();
      onNavigate("home");
    }
  }, /*#__PURE__*/React.createElement("img", {
    src: "../../assets/logo.png",
    alt: "zx-art"
  })), /*#__PURE__*/React.createElement("nav", {
    className: "zx-header__menu"
  }, items.map(it => /*#__PURE__*/React.createElement("a", {
    key: it.id,
    href: "#",
    className: active === it.id ? "active" : "",
    onClick: e => {
      e.preventDefault();
      onNavigate(it.id);
    }
  }, it.label))), /*#__PURE__*/React.createElement("div", {
    className: "zx-header__column"
  }, /*#__PURE__*/React.createElement(ZxButton, {
    size: "sm",
    variant: "transparent",
    shape: "square",
    ariaLabel: "Search"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "search",
    size: 18
  })), /*#__PURE__*/React.createElement(ZxButton, {
    size: "sm",
    variant: "transparent",
    shape: "square",
    ariaLabel: "Toggle theme",
    onClick: onToggleTheme
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "theme",
    size: 18
  })), /*#__PURE__*/React.createElement(ZxButton, {
    size: "sm",
    variant: "outlined"
  }, "EN"), /*#__PURE__*/React.createElement(ZxButton, {
    size: "sm",
    variant: "primary"
  }, "Sign in"))));
};
window.Header = Header;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/Header.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/HomeScreen.jsx
try { (() => {
/** Home page — section grid, featured picture, top tunes, latest prods. */
const HomeScreen = ({
  onPlayTune,
  currentTuneId,
  isPlaying,
  onOpenPicture,
  onNavigate
}) => {
  const featured = SAMPLE_PICTURES.slice(0, 4);
  const topTunes = SAMPLE_TUNES.slice(0, 5);
  const latestProds = SAMPLE_PRODS.slice(0, 3);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "section-title"
  }, /*#__PURE__*/React.createElement("h2", null, "Latest pictures"), /*#__PURE__*/React.createElement("a", {
    className: "more",
    href: "#",
    onClick: e => {
      e.preventDefault();
      onNavigate("pictures");
    }
  }, "view all \u2192")), /*#__PURE__*/React.createElement("div", {
    className: "grid-pictures"
  }, featured.map(p => /*#__PURE__*/React.createElement(PictureCard, {
    key: p.id,
    picture: p,
    onOpen: onOpenPicture
  }))), /*#__PURE__*/React.createElement("div", {
    className: "layout-2col",
    style: {
      marginTop: 32
    }
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "section-title"
  }, /*#__PURE__*/React.createElement("h2", null, "Top tunes this week"), /*#__PURE__*/React.createElement("a", {
    className: "more",
    href: "#",
    onClick: e => {
      e.preventDefault();
      onNavigate("music");
    }
  }, "view all \u2192")), /*#__PURE__*/React.createElement("div", {
    style: {
      background: "var(--surface)",
      border: "1px solid var(--secondary-200)",
      boxShadow: "var(--shadow-md)",
      borderRadius: "var(--radius-lg)",
      overflow: "hidden"
    }
  }, topTunes.map(t => /*#__PURE__*/React.createElement(TuneRow, {
    key: t.id,
    tune: t,
    isCurrent: t.id === currentTuneId,
    isPlaying: t.id === currentTuneId && isPlaying,
    onPlay: onPlayTune
  })))), /*#__PURE__*/React.createElement("aside", null, /*#__PURE__*/React.createElement("div", {
    className: "section-title"
  }, /*#__PURE__*/React.createElement("h2", null, "Latest prods")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, latestProds.map(p => /*#__PURE__*/React.createElement(ProdCard, {
    key: p.id,
    prod: p
  }))))), /*#__PURE__*/React.createElement("div", {
    className: "section-title",
    style: {
      marginTop: 32
    }
  }, /*#__PURE__*/React.createElement("h2", null, "Top groups")), /*#__PURE__*/React.createElement("div", {
    className: "aside-panel"
  }, /*#__PURE__*/React.createElement("div", {
    className: "aside-list"
  }, ["4th Dimension", "DiHalt", "Outsiders", "BYTEREALMS", "RAZOR 1911", "g0blinish & friends"].map((g, i) => /*#__PURE__*/React.createElement("a", {
    href: "#",
    key: g,
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, i + 1, "."), /*#__PURE__*/React.createElement(Icon, {
    name: "game",
    size: 16
  }), /*#__PURE__*/React.createElement("span", null, g), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "auto",
      color: "var(--text-light-color)",
      fontFamily: "var(--font-mono)",
      fontSize: 11
    }
  }, 120 - i * 14, " prods"))))));
};
window.HomeScreen = HomeScreen;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/HomeScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/Icon.jsx
try { (() => {
/** ZX-art icon — small SVG pixel-style stand-ins. */
const Icon = ({
  name,
  size = 18,
  color = "currentColor"
}) => {
  const paths = {
    play: "M8 5v14l11-7z",
    pause: "M6 5h4v14H6zm8 0h4v14h-4z",
    next: "M16 6h2v12h-2zM6 18l8.5-6L6 6z",
    prev: "M6 6h2v12H6zm3.5 6l8.5 6V6z",
    shuffle: "M10.59 9.17L5.41 4 4 5.41l5.17 5.17 1.42-1.41zM14.5 4l2.04 2.04L4 18.59 5.41 20 17.96 7.46 20 9.5V4h-5.5zm.33 9.41l-1.41 1.41 3.13 3.13L14.5 20H20v-5.5l-2.04 2.04-3.13-3.13z",
    repeat: "M7 7h10v3l4-4-4-4v3H5v6h2V7zm10 10H7v-3l-4 4 4 4v-3h12v-6h-2v4z",
    search: "M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.7.7l.27.28v.79l5 5 1.5-1.5-5-5zm-6 0A4.5 4.5 0 1 1 14 9.5 4.5 4.5 0 0 1 9.5 14z",
    menu: "M3 6h18v2H3zm0 5h18v2H3zm0 5h18v2H3z",
    star: "M12 2l3.09 6.26 6.91 1-5 4.87L18.18 22 12 18.27 5.82 22l1.18-7.87-5-4.87 6.91-1z",
    heart: "M12 21s-7-4.5-9.5-9A5.5 5.5 0 0 1 12 6.5 5.5 5.5 0 0 1 21.5 12c-2.5 4.5-9.5 9-9.5 9z",
    heartO: "M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z",
    download: "M5 20h14v-2H5v2zm7-18l-5.5 5.5h3.5V14h4V7.5h3.5L12 2z",
    close: "M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z",
    chat: "M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z",
    music: "M12 3v9.55a4 4 0 1 0 2 3.45V7h4V3h-6z",
    image: "M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3 3.5-4.5L19 18H5l3.5-4.5z",
    game: "M21 6H3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2zM11 13H8v3H6v-3H3v-2h3V8h2v3h3v2zm5 1a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm4-3a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z",
    disc: "M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 14a4 4 0 1 1 4-4 4 4 0 0 1-4 4zm0-6a2 2 0 1 0 2 2 2 2 0 0 0-2-2z",
    person: "M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm0 2c-3 0-9 1.5-9 4.5V21h18v-2.5c0-3-6-4.5-9-4.5z",
    globe: "M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm6.93 6h-2.95a15.65 15.65 0 0 0-1.38-3.56A8 8 0 0 1 18.92 8zM12 4a13.7 13.7 0 0 1 1.91 4h-3.82A13.7 13.7 0 0 1 12 4zM4.26 14a7.85 7.85 0 0 1 0-4h3.38a16.55 16.55 0 0 0 0 4zm.81 2h2.95a15.65 15.65 0 0 0 1.38 3.56A8 8 0 0 1 5.07 16zM8.03 8H5.07a8 8 0 0 1 4.34-3.56A15.65 15.65 0 0 0 8.03 8zM12 20a13.7 13.7 0 0 1-1.91-4h3.82A13.7 13.7 0 0 1 12 20zm2.34-6H9.66a14.45 14.45 0 0 1 0-4h4.68a14.45 14.45 0 0 1 0 4zm.25 5.56A15.65 15.65 0 0 0 15.97 16h2.95a8 8 0 0 1-4.33 3.56zM16.36 14a16.55 16.55 0 0 0 0-4h3.38a7.85 7.85 0 0 1 0 4z",
    theme: "M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.39 5.39 0 0 1-4.4 2.26 5.4 5.4 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z",
    plus: "M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6z",
    expand: "M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"
  };
  const d = paths[name] || paths.image;
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: "0 0 24 24",
    width: size,
    height: size,
    fill: color,
    "aria-hidden": "true"
  }, /*#__PURE__*/React.createElement("path", {
    d: d
  }));
};
window.Icon = Icon;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/Icon.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/MusicScreen.jsx
try { (() => {
/** Music page — tabs (Tunes / Authors / Top), letter, list */
const MusicScreen = ({
  onPlayTune,
  currentTuneId,
  isPlaying
}) => {
  const [tab, setTab] = React.useState("tunes");
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "crumbs"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Home"), /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "\u203A"), " ", /*#__PURE__*/React.createElement("span", null, "Music")), /*#__PURE__*/React.createElement("div", {
    className: "tab-bar"
  }, /*#__PURE__*/React.createElement("button", {
    className: tab === "tunes" ? "active" : "",
    onClick: () => setTab("tunes")
  }, "All tunes"), /*#__PURE__*/React.createElement("button", {
    className: tab === "authors" ? "active" : "",
    onClick: () => setTab("authors")
  }, "Authors"), /*#__PURE__*/React.createElement("button", {
    className: tab === "top" ? "active" : "",
    onClick: () => setTab("top")
  }, "Top rated"), /*#__PURE__*/React.createElement("button", {
    className: tab === "radio" ? "active" : "",
    onClick: () => setTab("radio")
  }, "Radio")), tab === "radio" ? /*#__PURE__*/React.createElement("div", {
    className: "empty-screen"
  }, "\uD83D\uDCFB Radio is broadcasting in the player \u2014 keep listening.") : /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "zx-letters",
    style: {
      marginBottom: 12
    }
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    className: "active"
  }, "all"), "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("").map(L => /*#__PURE__*/React.createElement("a", {
    href: "#",
    key: L,
    onClick: e => e.preventDefault()
  }, L))), /*#__PURE__*/React.createElement("div", {
    className: "toolbar",
    style: {
      marginBottom: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 6,
      alignItems: "center",
      fontSize: 13,
      color: "var(--text-light-color)"
    }
  }, "Chip:", /*#__PURE__*/React.createElement(ZxButton, {
    size: "xs",
    variant: "primary"
  }, "AY"), /*#__PURE__*/React.createElement(ZxButton, {
    size: "xs",
    variant: "outlined"
  }, "Beeper"), /*#__PURE__*/React.createElement(ZxButton, {
    size: "xs",
    variant: "outlined"
  }, "Turbosound")), /*#__PURE__*/React.createElement(ZxButton, {
    size: "sm",
    variant: "outlined"
  }, "Sort: newest")), /*#__PURE__*/React.createElement("div", {
    style: {
      background: "var(--surface)",
      border: "1px solid var(--secondary-200)",
      boxShadow: "var(--shadow-md)",
      borderRadius: "var(--radius-lg)",
      overflow: "hidden"
    }
  }, SAMPLE_TUNES.map(t => /*#__PURE__*/React.createElement(TuneRow, {
    key: t.id,
    tune: t,
    isCurrent: t.id === currentTuneId,
    isPlaying: t.id === currentTuneId && isPlaying,
    onPlay: onPlayTune
  })))));
};
window.MusicScreen = MusicScreen;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/MusicScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/Oscilloscope.jsx
try { (() => {
/* Oscilloscope.jsx — animated AY 3-channel oscilloscope for the tune hero.
   Purely visual (no audio): synthesises three deterministic square-wave traces
   (channels A / B / C, matching the AY "ABC" layout) and animates them when
   `playing` is true. When paused it holds a calm, low-amplitude frame so the
   hero still reads as alive without implying playback. Renders to a <canvas>
   sized to its container with devicePixelRatio scaling for crisp lines. */

const OSC_CHANNELS = [{
  key: "A",
  color: "#2a8fe0",
  base: 0.0145,
  duty: 0.5
},
// blue  — bass / arp
{
  key: "B",
  color: "#23b58a",
  base: 0.0320,
  duty: 0.5
},
// green — chords
{
  key: "C",
  color: "#d8a72e",
  base: 0.0560,
  duty: 0.32
} // amber — lead
];
function Oscilloscope({
  playing
}) {
  const canvasRef = React.useRef(null);
  const rafRef = React.useRef(0);
  const tRef = React.useRef(0);
  const playingRef = React.useRef(playing);
  const ampRef = React.useRef(0.18);
  React.useEffect(() => {
    playingRef.current = playing;
  }, [playing]);
  React.useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext("2d");
    let last = performance.now();
    function resize() {
      const dpr = Math.min(window.devicePixelRatio || 1, 2);
      const rect = canvas.getBoundingClientRect();
      canvas.width = Math.round(rect.width * dpr);
      canvas.height = Math.round(rect.height * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }
    resize();
    const ro = new ResizeObserver(resize);
    ro.observe(canvas);
    function wave(ch, x, t) {
      // sum of the fundamental square wave + a slow vibrato + a touch of detune,
      // so each channel has its own character. Returns -1..1.
      const f = ch.base * (1 + 0.04 * Math.sin(t * 0.7 + x * 0.002));
      const phase = x * f + t * (0.6 + ch.base * 6);
      const frac = phase - Math.floor(phase);
      const sq = frac < ch.duty ? 1 : -1;
      const soften = 0.82 + 0.18 * Math.sin(phase * Math.PI * 2);
      return sq * soften;
    }
    function draw(now) {
      const dt = Math.min((now - last) / 1000, 0.05);
      last = now;
      const target = playingRef.current ? 1 : 0.28;
      ampRef.current += (target - ampRef.current) * Math.min(dt * 3.5, 1);
      tRef.current += dt * (playingRef.current ? 1 : 0.35);
      const t = tRef.current;
      const rect = canvas.getBoundingClientRect();
      const W = rect.width,
        H = rect.height;
      ctx.clearRect(0, 0, W, H);

      // faint baseline grid (one lane per channel)
      const lanes = OSC_CHANNELS.length;
      const laneH = H / lanes;
      ctx.strokeStyle = "rgba(255,255,255,0.06)";
      ctx.lineWidth = 1;
      for (let i = 0; i < lanes; i++) {
        const y = Math.round(laneH * (i + 0.5)) + 0.5;
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(W, y);
        ctx.stroke();
      }
      OSC_CHANNELS.forEach((ch, i) => {
        const midY = laneH * (i + 0.5);
        const amp = laneH * 0.34 * ampRef.current;
        ctx.beginPath();
        for (let x = 0; x <= W; x += 1) {
          const y = midY - wave(ch, x, t) * amp;
          if (x === 0) ctx.moveTo(x, y);else ctx.lineTo(x, y);
        }
        ctx.strokeStyle = ch.color;
        ctx.lineWidth = 1.6;
        ctx.shadowColor = ch.color;
        ctx.shadowBlur = playingRef.current ? 5 : 2;
        ctx.stroke();
        ctx.shadowBlur = 0;
      });
      rafRef.current = requestAnimationFrame(draw);
    }
    rafRef.current = requestAnimationFrame(draw);
    return () => {
      cancelAnimationFrame(rafRef.current);
      ro.disconnect();
    };
  }, []);
  return /*#__PURE__*/React.createElement("div", {
    className: "tp-osc"
  }, /*#__PURE__*/React.createElement("canvas", {
    ref: canvasRef,
    className: "tp-osc__canvas",
    "aria-hidden": "true"
  }), /*#__PURE__*/React.createElement("div", {
    className: "tp-osc__legend"
  }, OSC_CHANNELS.map(ch => /*#__PURE__*/React.createElement("span", {
    className: "tp-osc__chan",
    key: ch.key
  }, /*#__PURE__*/React.createElement("span", {
    className: "tp-osc__dot",
    style: {
      background: ch.color
    }
  }), ch.key)), /*#__PURE__*/React.createElement("span", {
    className: "tp-osc__tag"
  }, "AY \xB7 ", OSC_CHANNELS.length, " \u043A\u0430\u043D\u0430\u043B\u0430")));
}
window.Oscilloscope = Oscilloscope;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/Oscilloscope.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/PartyPage.jsx
try { (() => {
/* PartyPage.jsx — Party (competition / demoparty / event) page for ZXArt.
   Part 1: icon helper, party logo, header, overview (winners reel), and the
   compo renderers — each entry uses our STANDARD prod / music / picture
   component, with per-compo sorting (place / views / launches / plays).
   The compo tabs, results table, activity feed and shell live in
   PartyPageWorks.jsx. Visually consistent with the Author & Group pages.
*/

const {
  useState: pUseState,
  useMemo: pUseMemo
} = React;

/* ── inline svg icons (24x24, design-system paths) ── */
function PP_I({
  name,
  size = 16
}) {
  const p = {
    demo: "M18 4l2 4h-3l-2-4h-2l2 4h-3l-2-4H8l2 4H7L5 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V4h-4z",
    intro: "M11 21h-1l1-7H7.5c-.58 0-.39-.39-.37-.42C8.48 10.94 10.42 7.54 13 3h1l-1 7h3.5c.49 0 .56.33.47.51l-.07.15C12.96 17.55 11 21 11 21z",
    music: "M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6z",
    beeper: "M3 9v6h4l5 5V4L7 9H3zm13.5 3a4.5 4.5 0 0 0-2.5-4.03v8.05A4.5 4.5 0 0 0 16.5 12z",
    gfx: "M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm0 13l4-4 3 3 5-5 3 3V5H5v12z",
    game: "M21 6H3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2zM11 13H8v3H6v-3H3v-2h3V8h2v3h3v2zm4.5 2a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm4-3a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z",
    wild: "M12 2l2.39 4.84L19.78 8l-3.89 3.79.92 5.4L12 14.77 7.19 17.19l.92-5.4L4.22 8l5.39-1.16L12 2z",
    location: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z",
    calendar: "M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z",
    link: "M3.9 12a4.1 4.1 0 0 1 4.1-4.1h4V6H8a6 6 0 1 0 0 12h4v-1.9H8A4.1 4.1 0 0 1 3.9 12zm5.1 1h6v-2H9v2zm7-7h-4v1.9h4a4.1 4.1 0 0 1 0 8.2h-4V18h4a6 6 0 0 0 0-12z",
    chat: "M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h11c.55 0 1-.45 1-1z",
    star: "M12 2l3.09 6.26 6.91 1-5 4.87L18.18 22 12 18.27 5.82 22l1.18-7.87-5-4.87 6.91-1z",
    external: "M14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7zM19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7h-2v7z",
    download: "M5 20h14v-2H5v2zM19 9h-4V3H9v6H5l7 7 7-7z",
    trophy: "M18 4V2H6v2H3v3a4 4 0 0 0 4 4h.3A5 5 0 0 0 11 14.9V17H8v2h8v-2h-3v-2.1A5 5 0 0 0 16.7 11H17a4 4 0 0 0 4-4V4h-3zM5 7V6h1v3a2 2 0 0 1-1-2zm14 0a2 2 0 0 1-1 2V6h1v1z",
    play: "M8 5v14l11-7z",
    eye: "M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5C21.3 7.6 17 4.5 12 4.5zm0 12.5a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z",
    run: "M13 3h-2v10h2V3zm4.83 2.17l-1.42 1.42A6.92 6.92 0 0 1 19 12a7 7 0 1 1-12.41-4.42L5.17 6.17A9 9 0 1 0 21 12a8.94 8.94 0 0 0-3.17-6.83z",
    grid: "M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8z",
    list: "M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"
  };
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: "0 0 24 24",
    width: size,
    height: size,
    fill: "currentColor",
    "aria-hidden": "true",
    style: {
      flexShrink: 0
    }
  }, /*#__PURE__*/React.createElement("path", {
    d: p[name] || ""
  }));
}

/* ── party logo: pixel banner tinted by id + abbreviation overlay ── */
function PartyLogo({
  abbr,
  seed = 7,
  size = 96
}) {
  const cols = 16,
    rows = 12;
  let s = seed * 9301 + 49297;
  const rand = () => {
    s = (s * 9301 + 49297) % 233280;
    return s / 233280;
  };
  const hue = seed * 53 % 360;
  const cells = [];
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      const v = rand();
      const band = y < 3 ? 22 : y > rows - 3 ? 16 : 19;
      const l = band + Math.floor(v * 12);
      cells.push(/*#__PURE__*/React.createElement("rect", {
        key: `${x}-${y}`,
        x: x,
        y: y,
        width: 1,
        height: 1,
        fill: `oklch(${l}% 0.07 ${hue})`
      }));
    }
  }
  const label = (abbr || "?").slice(0, 8);
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: `0 0 ${cols} ${rows}`,
    width: size,
    height: size * rows / cols,
    style: {
      imageRendering: "pixelated",
      display: "block",
      borderRadius: 3,
      background: "#111"
    },
    shapeRendering: "crispEdges"
  }, cells, /*#__PURE__*/React.createElement("text", {
    x: cols / 2,
    y: rows / 2,
    fill: "#fff",
    fontSize: label.length > 6 ? 1.9 : 2.6,
    fontFamily: "monospace",
    fontWeight: "700",
    textAnchor: "middle",
    dominantBaseline: "central",
    style: {
      letterSpacing: "-0.03em"
    }
  }, label));
}

/* derive flat stats from compos */
function partyStats(party) {
  let entries = 0;
  const authorSet = new Set();
  party.compos.forEach(c => {
    entries += c.entries.length;
    c.entries.forEach(e => (e.by || []).forEach(a => authorSet.add(a)));
  });
  return {
    compos: party.compos.length,
    entries,
    authors: authorSet.size
  };
}

/* ════════════════════════════════════════════════════════════════════════
   HEADER — logo + identity (name / abbr / type / place / year),
   summary sentence, external links, ZIP download.
   ════════════════════════════════════════════════════════════════════════ */
function PartyHeader({
  party
}) {
  const st = partyStats(party);
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-header pp-header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-header__left"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-header__avatar pp-header__logo"
  }, /*#__PURE__*/React.createElement(PartyLogo, {
    abbr: party.abbr,
    seed: party.id % 997
  }))), /*#__PURE__*/React.createElement("div", {
    className: "ap-header__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-header__title-row"
  }, /*#__PURE__*/React.createElement("h1", {
    className: "ap-header__name pp-header__name"
  }, party.name), /*#__PURE__*/React.createElement("span", {
    className: "pp-type-tag"
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: "trophy",
    size: 12
  }), party.type)), /*#__PURE__*/React.createElement("div", {
    className: "ap-header__bio pp-header__bio"
  }, /*#__PURE__*/React.createElement("code", {
    className: "pp-header__abbr"
  }, party.abbr), /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__sep"
  }, "\xB7"), /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__loc"
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: "location",
    size: 12
  }), /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, party.country), party.city && /*#__PURE__*/React.createElement(React.Fragment, null, ",\xA0", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, party.city))), /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__sep"
  }, "\xB7"), /*#__PURE__*/React.createElement("span", {
    className: "ap-bio__joined"
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: "calendar",
    size: 12
  }), "\xA0", party.year)), /*#__PURE__*/React.createElement("p", {
    className: "ap-stats-sentence"
  }, /*#__PURE__*/React.createElement("b", null, st.compos), " ", pluralRuP(st.compos, ["конкурс", "конкурса", "конкурсов"]), ",", " ", /*#__PURE__*/React.createElement("b", null, st.entries), " ", pluralRuP(st.entries, ["работа", "работы", "работ"]), " \u043E\u0442", " ", /*#__PURE__*/React.createElement("b", null, st.authors), " ", pluralRuP(st.authors, ["автора", "авторов", "авторов"]), ".", " ", party.summary), /*#__PURE__*/React.createElement("div", {
    className: "pp-header__foot"
  }, party.links.length > 0 && /*#__PURE__*/React.createElement("div", {
    className: "ap-ext-links pp-header__links"
  }, party.links.map((l, i) => /*#__PURE__*/React.createElement("a", {
    key: i,
    href: "#",
    onClick: e => e.preventDefault(),
    title: l.label
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-ext-icon"
  }, l.icon), l.label))), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--secondary zx-button--sm pp-zip",
    type: "button",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: "download",
    size: 14
  }), "\u0421\u043A\u0430\u0447\u0430\u0442\u044C ZIP-\u0430\u0440\u0445\u0438\u0432"))));
}

/* ════════════════════════════════════════════════════════════════════════
   OVERVIEW — "Победители конкурсов". The #1 of every compo, but GROUPED BY
   MEDIUM and rendered with our STANDARD components (ProdCard / PictureCard /
   tune row) so nothing is mixed in one row and nothing is reinvented. Each
   winner is captioned with its compo (click → jump to that compo's tab).
   ════════════════════════════════════════════════════════════════════════ */
function PartyOverview({
  party,
  onJumpCompo
}) {
  const groups = pUseMemo(() => {
    const out = {
      prod: [],
      picture: [],
      music: []
    };
    party.compos.forEach(c => {
      const w = [...c.entries].sort((a, b) => (a.place || 99) - (b.place || 99))[0];
      if (w) out[COMPO_MEDIA[c.type].medium].push({
        compo: c,
        entry: w
      });
    });
    return out;
  }, [party]);
  const SECTIONS = [{
    medium: "prod",
    title: "Программы",
    icon: "demo"
  }, {
    medium: "picture",
    title: "Графика",
    icon: "gfx"
  }, {
    medium: "music",
    title: "Музыка",
    icon: "music"
  }].filter(s => groups[s.medium].length > 0);
  return /*#__PURE__*/React.createElement("section", {
    className: "pp-winners"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u041F\u043E\u0431\u0435\u0434\u0438\u0442\u0435\u043B\u0438 \u043A\u043E\u043D\u043A\u0443\u0440\u0441\u043E\u0432"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u043F\u0435\u0440\u0432\u043E\u0435 \u043C\u0435\u0441\u0442\u043E \u043A\u0430\u0436\u0434\u043E\u0433\u043E \u043A\u043E\u043D\u043A\u0443\u0440\u0441\u0430 \u2014 \u0442\u0435\u043C\u0438 \u0436\u0435 \u043A\u0430\u0440\u0442\u043E\u0447\u043A\u0430\u043C\u0438 \u043F\u0440\u043E\u0434\u0430, \u0433\u0440\u0430\u0444\u0438\u043A\u0438 \u0438 \u043C\u0443\u0437\u044B\u043A\u0438"), /*#__PURE__*/React.createElement("a", {
    className: "ap-dash-col__all",
    href: "#",
    style: {
      marginLeft: "auto"
    },
    onClick: e => {
      e.preventDefault();
      onJumpCompo();
    }
  }, "\u0432\u0441\u0435 \u043A\u043E\u043D\u043A\u0443\u0440\u0441\u044B \u2192")), SECTIONS.map(s => /*#__PURE__*/React.createElement("div", {
    key: s.medium,
    className: "pp-wgroup"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-wgroup__h"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pp-wgroup__icon pp-wgroup__icon--" + s.medium
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: s.icon,
    size: 14
  })), s.title, /*#__PURE__*/React.createElement("span", {
    className: "pp-wgroup__n"
  }, groups[s.medium].length, " ", pluralRuP(groups[s.medium].length, ["конкурс", "конкурса", "конкурсов"]))), s.medium === "music" ? /*#__PURE__*/React.createElement("div", {
    className: "ap-tune-list pp-tune-list"
  }, groups.music.map(({
    compo,
    entry
  }) => /*#__PURE__*/React.createElement("div", {
    key: compo.id,
    className: "zx-tune-row pp-tune-row pp-wtune"
  }, /*#__PURE__*/React.createElement(ZxMedal, {
    place: 1
  }), /*#__PURE__*/React.createElement("button", {
    className: "zx-tune-row__play",
    "aria-label": "play",
    onClick: () => onJumpCompo(compo.id)
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: "play",
    size: 14
  })), /*#__PURE__*/React.createElement("span", {
    className: "zx-tune-row__title"
  }, entry.title), /*#__PURE__*/React.createElement("span", {
    className: "zx-tune-row__author"
  }, (entry.by || []).join(", ")), /*#__PURE__*/React.createElement("a", {
    className: "pp-wtune__compo",
    href: "#",
    onClick: e => {
      e.preventDefault();
      onJumpCompo(compo.id);
    }
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: COMPO_MEDIA[compo.type].icon,
    size: 11
  }), compo.name), /*#__PURE__*/React.createElement("span", {
    className: "pp-tune-row__rating"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-feat__star"
  }, "\u2605 ", entry.stars))))) : /*#__PURE__*/React.createElement("div", {
    className: s.medium === "prod" ? "ap-prod-grid" : "ap-pic-grid"
  }, groups[s.medium].map(({
    compo,
    entry
  }) => /*#__PURE__*/React.createElement("div", {
    key: compo.id,
    className: "pp-wcell"
  }, /*#__PURE__*/React.createElement("a", {
    className: "pp-wcell__compo",
    href: "#",
    onClick: e => {
      e.preventDefault();
      onJumpCompo(compo.id);
    }
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: COMPO_MEDIA[compo.type].icon,
    size: 12
  }), compo.name, /*#__PURE__*/React.createElement("span", {
    className: "pp-wcell__go"
  }, "\u2192")), s.medium === "prod" ? /*#__PURE__*/React.createElement(ProdCard, {
    prod: {
      id: entry.id,
      title: entry.title,
      palette: paletteForP(entry.id),
      kind: COMPO_MEDIA[compo.type].label,
      authors: entry.by || [],
      party: null,
      place: 1,
      year: party.year,
      stars: entry.stars,
      votes: entry.votes
    }
  }) : /*#__PURE__*/React.createElement(PictureCard, {
    picture: {
      id: entry.id,
      title: entry.title,
      palette: paletteForP(entry.id),
      authors: entry.by || [],
      format: entry.format,
      party: party.name,
      place: 1,
      stars: entry.stars,
      votes: entry.votes,
      year: party.year
    }
  })))))));
}

/* ════════════════════════════════════════════════════════════════════════
   SORT + METRICS helpers
   ════════════════════════════════════════════════════════════════════════ */
const SORT_LABELS = {
  place: "по месту",
  views: "по просмотрам",
  launches: "по запускам",
  plays: "по проигрываниям"
};
function sortOptionsFor(medium) {
  if (medium === "music") return ["place", "plays"];
  if (medium === "picture") return ["place", "views"];
  return ["place", "views", "launches"]; /* prod */
}
function applySort(entries, sort) {
  const a = [...entries];
  if (sort === "place") a.sort((x, y) => (x.place || 99) - (y.place || 99));else a.sort((x, y) => entryMetric(y, sort) - entryMetric(x, sort));
  return a;
}

/* secondary engagement chips under a prod / picture card */
function MetricChips({
  e,
  medium,
  sort
}) {
  const items = medium === "picture" ? [["views", "eye"]] : medium === "prod" ? [["views", "eye"], ["launches", "run"]] : [];
  if (items.length === 0) return null;
  return /*#__PURE__*/React.createElement("div", {
    className: "pp-metrics"
  }, items.map(([k, icon]) => /*#__PURE__*/React.createElement("span", {
    key: k,
    className: "pp-metric" + (sort === k ? " pp-metric--on" : "")
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: icon,
    size: 12
  }), fmtCount(entryMetric(e, k)))));
}
function SortBar({
  medium,
  sort,
  onSort
}) {
  const opts = sortOptionsFor(medium);
  if (opts.length <= 1) return null;
  return /*#__PURE__*/React.createElement("div", {
    className: "pp-sortbar"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pp-sortbar__label"
  }, "\u0421\u043E\u0440\u0442\u0438\u0440\u043E\u0432\u043A\u0430:"), opts.map(o => /*#__PURE__*/React.createElement("button", {
    key: o,
    className: "ap-pill" + (sort === o ? " ap-pill--on" : ""),
    onClick: () => onSort(o)
  }, SORT_LABELS[o])));
}

/* ════════════════════════════════════════════════════════════════════════
   COMPO RENDERERS — native components (ProdCard / picture card / tune row).
   ════════════════════════════════════════════════════════════════════════ */
function CompoProds({
  entries,
  compo,
  party,
  sort
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "ap-prod-grid"
  }, entries.map(e => /*#__PURE__*/React.createElement("div", {
    key: e.id,
    className: "ap-prodwrap pp-entrywrap"
  }, /*#__PURE__*/React.createElement(ProdCard, {
    prod: {
      id: e.id,
      title: e.title,
      palette: paletteForP(e.id),
      kind: COMPO_MEDIA[compo.type].label,
      authors: e.by || [],
      party: null,
      place: e.place,
      year: party.year,
      stars: e.stars,
      votes: e.votes
    }
  }), /*#__PURE__*/React.createElement(MetricChips, {
    e: e,
    medium: "prod",
    sort: sort
  }))));
}
function CompoPictures({
  entries,
  party,
  sort
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "ap-pic-grid"
  }, entries.map(e => /*#__PURE__*/React.createElement("div", {
    key: e.id,
    className: "ap-pic-wrap pp-entrywrap"
  }, /*#__PURE__*/React.createElement(PictureCard, {
    picture: {
      id: e.id,
      title: e.title,
      palette: paletteForP(e.id),
      authors: e.by || [],
      format: e.format,
      party: party.name,
      place: e.place,
      stars: e.stars,
      votes: e.votes,
      year: party.year
    }
  }), /*#__PURE__*/React.createElement(MetricChips, {
    e: e,
    medium: "picture",
    sort: sort
  }))));
}
function CompoMusic({
  entries,
  sort
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "ap-tune-list pp-tune-list"
  }, entries.map(e => /*#__PURE__*/React.createElement("div", {
    key: e.id,
    className: "zx-tune-row pp-tune-row"
  }, /*#__PURE__*/React.createElement(ZxMedal, {
    place: e.place
  }), /*#__PURE__*/React.createElement("button", {
    className: "zx-tune-row__play",
    "aria-label": "play"
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: "play",
    size: 14
  })), /*#__PURE__*/React.createElement("span", {
    className: "zx-tune-row__title"
  }, e.title), /*#__PURE__*/React.createElement("span", {
    className: "zx-tune-row__author"
  }, (e.by || []).join(", ")), /*#__PURE__*/React.createElement("span", {
    className: "pp-tune-row__chip"
  }, e.chip, " \xB7 ", e.duration), /*#__PURE__*/React.createElement("span", {
    className: "pp-tune-row__rating"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-feat__star"
  }, "\u2605 ", e.stars), /*#__PURE__*/React.createElement("span", {
    className: "pp-tune-row__votes"
  }, "(", e.votes, ")")), /*#__PURE__*/React.createElement("span", {
    className: "pp-tune-row__plays" + (sort === "plays" ? " pp-tune-row__plays--on" : "")
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: "play",
    size: 11
  }), fmtCount(entryMetric(e, "plays"))))));
}

/* one compo's content: header + sort bar + native grid */
function CompoView({
  compo,
  party
}) {
  const media = COMPO_MEDIA[compo.type];
  const [sort, setSort] = pUseState("place");
  const entries = pUseMemo(() => applySort(compo.entries, sort), [compo, sort]);
  return /*#__PURE__*/React.createElement("div", {
    className: "pp-compo"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-compo__h"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pp-compo__icon pp-compo__icon--" + media.medium
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: media.icon,
    size: 16
  })), /*#__PURE__*/React.createElement("h3", {
    className: "pp-compo__name"
  }, compo.name), /*#__PURE__*/React.createElement("span", {
    className: "pp-compo__count"
  }, compo.entries.length, " ", pluralRuP(compo.entries.length, ["работа", "работы", "работ"]))), /*#__PURE__*/React.createElement(SortBar, {
    medium: media.medium,
    sort: sort,
    onSort: setSort
  }), media.medium === "prod" && /*#__PURE__*/React.createElement(CompoProds, {
    entries: entries,
    compo: compo,
    party: party,
    sort: sort
  }), media.medium === "picture" && /*#__PURE__*/React.createElement(CompoPictures, {
    entries: entries,
    party: party,
    sort: sort
  }), media.medium === "music" && /*#__PURE__*/React.createElement(CompoMusic, {
    entries: entries,
    sort: sort
  }));
}
Object.assign(window, {
  PP_I,
  PartyLogo,
  partyStats,
  PartyHeader,
  PartyOverview,
  CompoView,
  CompoProds,
  CompoPictures,
  CompoMusic,
  SortBar,
  MetricChips,
  applySort,
  sortOptionsFor,
  SORT_LABELS
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/PartyPage.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/PartyPageWorks.jsx
try { (() => {
/* PartyPageWorks.jsx — compo navigator (one TAB per compo), results.txt-style
   table, activity feed (votes left, comments right), and the PartyPage shell. */

const {
  useState: pUseState2,
  useMemo: pUseMemo2,
  useRef: pUseRef2,
  useEffect: pUseEffect2
} = React;

/* ════════════════════════════════════════════════════════════════════════
   COMPOS TAB — each compo is a sub-tab; the selected compo shows its entries
   with our standard prod / music / picture components + per-compo sorting.
   ════════════════════════════════════════════════════════════════════════ */
function PartyCompos({
  party,
  focusCompo
}) {
  const [active, setActive] = pUseState2(party.compos[0]?.id);
  pUseEffect2(() => {
    if (focusCompo && party.compos.some(c => c.id === focusCompo)) setActive(focusCompo);
  }, [focusCompo]);
  const compo = party.compos.find(c => c.id === active) || party.compos[0];
  const total = party.compos.reduce((n, c) => n + c.entries.length, 0);
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-works"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u041A\u043E\u043D\u043A\u0443\u0440\u0441\u044B \u0438 \u0440\u0430\u0431\u043E\u0442\u044B"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, party.compos.length, " \u043A\u043E\u043C\u043F\u043E \xB7 ", total, " \u0440\u0430\u0431\u043E\u0442 \u2014 \u0432\u044B\u0431\u0438\u0440\u0430\u0439\u0442\u0435 \u043A\u043E\u043D\u043A\u0443\u0440\u0441 \u0432\u043A\u043B\u0430\u0434\u043A\u043E\u0439")), /*#__PURE__*/React.createElement("div", {
    className: "ap-worktabs pp-compotabs"
  }, party.compos.map(c => /*#__PURE__*/React.createElement("button", {
    key: c.id,
    className: "ap-worktab" + (active === c.id ? " ap-worktab--on" : ""),
    onClick: () => setActive(c.id)
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: COMPO_MEDIA[c.type].icon,
    size: 14
  }), c.name, /*#__PURE__*/React.createElement("span", {
    className: "ap-worktab__count"
  }, c.entries.length)))), /*#__PURE__*/React.createElement("div", {
    className: "ap-worktab-body"
  }, /*#__PURE__*/React.createElement(CompoView, {
    compo: compo,
    party: party
  })));
}

/* ════════════════════════════════════════════════════════════════════════
   RESULTS — compact results.txt-style ranked table across all compos.
   ════════════════════════════════════════════════════════════════════════ */
function PartyResults({
  party
}) {
  return /*#__PURE__*/React.createElement("section", {
    className: "pp-results"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0418\u0442\u043E\u0433\u043E\u0432\u0430\u044F \u0442\u0430\u0431\u043B\u0438\u0446\u0430"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0440\u0435\u0437\u0443\u043B\u044C\u0442\u0430\u0442\u044B \u0433\u043E\u043B\u043E\u0441\u043E\u0432\u0430\u043D\u0438\u044F \u043F\u043E \u0432\u0441\u0435\u043C \u043A\u043E\u043D\u043A\u0443\u0440\u0441\u0430\u043C \u2014 \u043A\u0430\u043A \u0432 results.txt")), /*#__PURE__*/React.createElement("div", {
    className: "pp-results__sheet"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-results__filehead"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pp-results__dot pp-results__dot--r"
  }), /*#__PURE__*/React.createElement("span", {
    className: "pp-results__dot pp-results__dot--y"
  }), /*#__PURE__*/React.createElement("span", {
    className: "pp-results__dot pp-results__dot--g"
  }), /*#__PURE__*/React.createElement("span", {
    className: "pp-results__filename"
  }, party.abbr, "_results.txt")), /*#__PURE__*/React.createElement("div", {
    className: "pp-results__body"
  }, party.compos.map(c => {
    const media = COMPO_MEDIA[c.type];
    return /*#__PURE__*/React.createElement("div", {
      key: c.id,
      className: "pp-rtable"
    }, /*#__PURE__*/React.createElement("div", {
      className: "pp-rtable__h"
    }, /*#__PURE__*/React.createElement(PP_I, {
      name: media.icon,
      size: 13
    }), /*#__PURE__*/React.createElement("span", {
      className: "pp-rtable__title"
    }, c.name.toUpperCase()), /*#__PURE__*/React.createElement("span", {
      className: "pp-rtable__n"
    }, c.entries.length, " ", pluralRuP(c.entries.length, ["работа", "работы", "работ"]))), applySort(c.entries, "place").map(e => /*#__PURE__*/React.createElement("div", {
      key: e.id,
      className: "pp-rrow" + (e.place === 1 ? " pp-rrow--win" : "")
    }, /*#__PURE__*/React.createElement("span", {
      className: "pp-rrow__place pp-rrow__place--" + (e.place === 1 ? "gold" : e.place === 2 ? "silver" : e.place === 3 ? "bronze" : "rest")
    }, e.place ? String(e.place).padStart(2, "0") : "--"), /*#__PURE__*/React.createElement("span", {
      className: "pp-rrow__title"
    }, e.title), /*#__PURE__*/React.createElement("span", {
      className: "pp-rrow__by"
    }, (e.by || []).join(", ")), /*#__PURE__*/React.createElement("span", {
      className: "pp-rrow__score"
    }, /*#__PURE__*/React.createElement("span", {
      className: "ap-feat__star"
    }, "\u2605"), " ", e.stars.toFixed(1), " ", /*#__PURE__*/React.createElement("span", {
      className: "pp-rrow__votes"
    }, "/", e.votes)))));
  }))));
}

/* ════════════════════════════════════════════════════════════════════════
   ACTIVITY — votes (left) + comments (right) around the party's entries.
   ════════════════════════════════════════════════════════════════════════ */
function PartyFeed({
  comments,
  votes
}) {
  const hasC = comments && comments.length > 0;
  const hasV = votes && votes.length > 0;
  if (!hasC && !hasV) return null;
  return /*#__PURE__*/React.createElement("section", {
    className: "ap-feed"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0410\u043A\u0442\u0438\u0432\u043D\u043E\u0441\u0442\u044C \u0432\u043E\u043A\u0440\u0443\u0433 \u043F\u0430\u0442\u0438"), /*#__PURE__*/React.createElement("span", {
    className: "ap-section__hint"
  }, "\u0441\u0432\u0435\u0436\u0438\u0435 \u0433\u043E\u043B\u043E\u0441\u0430 \u0438 \u043A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0438 \u043F\u043E \u0440\u0430\u0431\u043E\u0442\u0430\u043C \u043A\u043E\u043D\u043A\u0443\u0440\u0441\u0430")), /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__cols pp-feed"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col-h"
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: "star",
    size: 14
  }), "\u0413\u043E\u043B\u043E\u0441\u0430 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count"
  }, votes.length)), /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__votes"
  }, votes.map(v => /*#__PURE__*/React.createElement("div", {
    key: v.id,
    className: "ap-fvote"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ap-fvote__date"
  }, v.date.slice(5)), /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault(),
    className: "ap-fvote__user"
  }, v.by), /*#__PURE__*/React.createElement("span", {
    className: "ap-fvote__stars",
    title: `${v.score}/5`
  }, Array.from({
    length: 5
  }).map((_, i) => /*#__PURE__*/React.createElement("span", {
    key: i,
    className: i < v.score ? "on" : "off"
  }, "\u2605"))), /*#__PURE__*/React.createElement("span", {
    className: "ap-fvote__work"
  }, "\u2192 ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, "\xAB", v.workTitle, "\xBB")), /*#__PURE__*/React.createElement("span", {
    className: "ap-fvote__type"
  }, v.compo))))), /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-collab__col-h"
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: "chat",
    size: 14
  }), "\u041A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0438 ", /*#__PURE__*/React.createElement("span", {
    className: "ap-section__count"
  }, comments.length)), /*#__PURE__*/React.createElement("div", {
    className: "ap-feed__comments"
  }, comments.map(c => /*#__PURE__*/React.createElement("article", {
    key: c.id,
    className: "ap-fcomment"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ap-fcomment__head"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault(),
    className: "ap-fcomment__user"
  }, c.by), /*#__PURE__*/React.createElement("span", {
    className: "ap-fcomment__work"
  }, "\u043A \u0440\u0430\u0431\u043E\u0442\u0435 ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, "\xAB", c.workTitle, "\xBB"), " \xB7 ", c.compo), /*#__PURE__*/React.createElement("span", {
    className: "ap-fcomment__date"
  }, c.date)), /*#__PURE__*/React.createElement("div", {
    className: "ap-fcomment__body"
  }, c.body)))))));
}

/* ════════════════════════════════════════════════════════════════════════
   PAGE SHELL
   ════════════════════════════════════════════════════════════════════════ */
function PartyPage({
  preset = "dihalt"
}) {
  const party = PARTY_PRESETS[preset];
  const year = party.year;
  const YEARS = pUseMemo2(() => {
    const arr = [];
    for (let y = year - 4; y <= year + 2; y++) arr.push(y);
    return arr;
  }, [year]);
  const [tab, setTab] = pUseState2("overview");
  const [focusCompo, setFocusCompo] = pUseState2(null);
  const tabsRef = pUseRef2(null);
  pUseEffect2(() => {
    setTab("overview");
    setFocusCompo(null);
  }, [preset]);
  const activityCount = (party.comments?.length || 0) + (party.votes?.length || 0);
  const entryCount = party.compos.reduce((n, c) => n + c.entries.length, 0);
  const TABS = [{
    id: "overview",
    label: "Обзор",
    icon: "trophy"
  }, {
    id: "compos",
    label: "Конкурсы",
    icon: "grid",
    count: party.compos.length
  }, {
    id: "results",
    label: "Результаты",
    icon: "list",
    count: entryCount
  }, {
    id: "activity",
    label: "Активность",
    icon: "chat",
    count: activityCount
  }].filter(t => t.count === undefined || t.count > 0);
  const go = id => {
    setTab(id);
    requestAnimationFrame(() => {
      const top = tabsRef.current?.getBoundingClientRect().top ?? 0;
      if (top < 0) tabsRef.current?.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
    });
  };
  const openCompo = compoId => {
    setFocusCompo(compoId || null);
    go("compos");
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "ap-root"
  }, /*#__PURE__*/React.createElement("nav", {
    className: "ap-crumbs",
    "aria-label": "breadcrumb"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0413\u043B\u0430\u0432\u043D\u0430\u044F"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u041F\u0430\u0442\u0438"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, year), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("span", {
    className: "here"
  }, party.name)), /*#__PURE__*/React.createElement("div", {
    className: "ap-letters pp-years"
  }, YEARS.map(y => /*#__PURE__*/React.createElement("a", {
    key: y,
    href: "#",
    className: y === year ? "ap-letters__on" : "",
    onClick: e => e.preventDefault()
  }, y))), /*#__PURE__*/React.createElement(PartyHeader, {
    party: party
  }), /*#__PURE__*/React.createElement("div", {
    className: "gp-tabbar",
    ref: tabsRef
  }, TABS.map(t => /*#__PURE__*/React.createElement("button", {
    key: t.id,
    className: "gp-tab" + (tab === t.id ? " gp-tab--on" : ""),
    onClick: () => go(t.id)
  }, /*#__PURE__*/React.createElement(PP_I, {
    name: t.icon,
    size: 15
  }), /*#__PURE__*/React.createElement("span", null, t.label), t.count > 0 && /*#__PURE__*/React.createElement("span", {
    className: "gp-tab__count"
  }, t.count)))), /*#__PURE__*/React.createElement("div", {
    className: "gp-tabview"
  }, tab === "overview" && /*#__PURE__*/React.createElement(PartyOverview, {
    party: party,
    onJumpCompo: openCompo
  }), tab === "compos" && /*#__PURE__*/React.createElement(PartyCompos, {
    party: party,
    focusCompo: focusCompo
  }), tab === "results" && /*#__PURE__*/React.createElement(PartyResults, {
    party: party
  }), tab === "activity" && /*#__PURE__*/React.createElement(PartyFeed, {
    comments: party.comments,
    votes: party.votes
  })));
}
Object.assign(window, {
  PartyPage,
  PartyCompos,
  PartyResults,
  PartyFeed
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/PartyPageWorks.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/PictureCard.jsx
try { (() => {
/** Picture card — derived from ng-zxart/src/app/entities/picture/ui/picture-card */
const PictureCard = ({
  picture,
  onOpen
}) => {
  return /*#__PURE__*/React.createElement("article", {
    className: "zx-picture-card"
  }, /*#__PURE__*/React.createElement("div", {
    className: "zx-picture-card__panel"
  }, /*#__PURE__*/React.createElement("a", {
    className: "zx-picture-card__image",
    href: "#",
    onClick: e => {
      e.preventDefault();
      onOpen?.(picture);
    }
  }, /*#__PURE__*/React.createElement(PixelArtSVG, {
    seed: picture.id,
    palette: picture.palette
  }), /*#__PURE__*/React.createElement("div", {
    className: "zx-picture-card__badges"
  }, picture.format && /*#__PURE__*/React.createElement(ZxBadge, null, picture.format), picture.realtime && /*#__PURE__*/React.createElement(ZxBadge, null, "realtime"), picture.flickering && /*#__PURE__*/React.createElement(ZxBadge, null, "flickering"))), /*#__PURE__*/React.createElement("div", {
    className: "zx-picture-card__info"
  }, /*#__PURE__*/React.createElement("a", {
    className: "zx-picture-card__title",
    href: "#",
    onClick: e => {
      e.preventDefault();
      onOpen?.(picture);
    }
  }, picture.title), /*#__PURE__*/React.createElement("div", {
    className: "zx-picture-card__authors"
  }, picture.authors.map((a, i) => /*#__PURE__*/React.createElement("span", {
    key: i
  }, i > 0 && ", ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, a)))), picture.party && /*#__PURE__*/React.createElement("div", {
    className: "zx-picture-card__party"
  }, picture.place && /*#__PURE__*/React.createElement(ZxMedal, {
    place: picture.place
  }), /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, picture.party)), /*#__PURE__*/React.createElement("div", {
    className: "zx-picture-card__bottom"
  }, /*#__PURE__*/React.createElement(ZxStars, {
    value: picture.stars,
    count: picture.votes
  }), /*#__PURE__*/React.createElement("span", {
    className: "zx-picture-card__year"
  }, picture.year)))));
};

/** Procedurally generated pixel-art placeholder. Stable per `seed`. */
const PixelArtSVG = ({
  seed = 0,
  palette = "default"
}) => {
  // small deterministic PRNG
  let s = seed * 9301 + 49297;
  const rand = () => {
    s = (s * 9301 + 49297) % 233280;
    return s / 233280;
  };
  const palettes = {
    default: ["#000033", "#aa0000", "#ffaa00", "#ffffff", "#0066cc"],
    sunset: ["#1a0033", "#cc3300", "#ff9933", "#ffcc66", "#330011"],
    cool: ["#001133", "#003366", "#3399cc", "#66ccff", "#ffffff"],
    forest: ["#001100", "#114411", "#226622", "#88aa44", "#ddee99"],
    night: ["#000022", "#221144", "#553388", "#aa66cc", "#ffeebb"]
  };
  const colors = palettes[palette] || palettes.default;
  const cols = 32,
    rows = 24;
  const cells = [];
  // sky gradient
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      const v = rand();
      let c;
      if (y > rows - 6) c = colors[1]; // ground
      else if (y > rows - 9 && v > 0.7) c = colors[2]; // mid foliage
      else if (y < 4 && v > 0.94) c = colors[3]; // stars
      else if (y > 4 && y < rows - 9 && v > 0.97) c = colors[4]; // distant
      else c = colors[0];
      cells.push(/*#__PURE__*/React.createElement("rect", {
        key: `${x}-${y}`,
        x: x,
        y: y,
        width: 1,
        height: 1,
        fill: c
      }));
    }
  }
  // a few "buildings" silhouettes
  for (let i = 0; i < 5; i++) {
    const bx = Math.floor(rand() * cols);
    const bw = 1 + Math.floor(rand() * 3);
    const bh = 2 + Math.floor(rand() * 6);
    cells.push(/*#__PURE__*/React.createElement("rect", {
      key: `b${i}`,
      x: bx,
      y: rows - 6 - bh,
      width: bw,
      height: bh,
      fill: colors[0]
    }));
  }
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: "0 0 32 24",
    preserveAspectRatio: "xMidYMid meet",
    xmlns: "http://www.w3.org/2000/svg",
    style: {
      width: "100%",
      height: "100%",
      display: "block",
      imageRendering: "pixelated"
    },
    shapeRendering: "crispEdges"
  }, cells);
};
window.PictureCard = PictureCard;
window.PixelArtSVG = PixelArtSVG;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/PictureCard.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/PictureDetail.jsx
try { (() => {
/** Picture detail — hero, metadata, comments, "more by author" aside */
const PictureDetail = ({
  picture,
  onBack
}) => {
  if (!picture) return null;
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "crumbs"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => {
      e.preventDefault();
      onBack();
    }
  }, "Home"), /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "\u203A"), /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => {
      e.preventDefault();
      onBack();
    }
  }, "Pictures"), /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "\u203A"), /*#__PURE__*/React.createElement("span", null, picture.title)), /*#__PURE__*/React.createElement("div", {
    className: "detail-hero"
  }, /*#__PURE__*/React.createElement("div", {
    className: "detail-hero__image"
  }, /*#__PURE__*/React.createElement(PixelArtSVG, {
    seed: picture.id,
    palette: picture.palette
  })), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h1", {
    className: "detail-hero__title"
  }, picture.title), /*#__PURE__*/React.createElement("div", {
    className: "detail-hero__authors"
  }, picture.authors.join(", ")), /*#__PURE__*/React.createElement(ZxStars, {
    value: picture.stars,
    count: picture.votes
  }), /*#__PURE__*/React.createElement("dl", {
    className: "detail-hero__meta",
    style: {
      marginTop: 16
    }
  }, /*#__PURE__*/React.createElement("dt", null, "Year"), /*#__PURE__*/React.createElement("dd", null, picture.year), /*#__PURE__*/React.createElement("dt", null, "Format"), /*#__PURE__*/React.createElement("dd", null, picture.format || "—"), /*#__PURE__*/React.createElement("dt", null, "Party"), /*#__PURE__*/React.createElement("dd", null, picture.party || "—"), /*#__PURE__*/React.createElement("dt", null, "Place"), /*#__PURE__*/React.createElement("dd", null, picture.place ? /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(ZxMedal, {
    place: picture.place
  }), " ", ordinal(picture.place)) : "—"), /*#__PURE__*/React.createElement("dt", null, "Border"), /*#__PURE__*/React.createElement("dd", null, "black"), /*#__PURE__*/React.createElement("dt", null, "Realtime"), /*#__PURE__*/React.createElement("dd", null, picture.realtime ? "yes" : "no")), /*#__PURE__*/React.createElement("div", {
    className: "detail-hero__actions"
  }, /*#__PURE__*/React.createElement(ZxButton, {
    variant: "primary"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "download",
    size: 16
  }), " Download .scr"), /*#__PURE__*/React.createElement(ZxButton, {
    variant: "outlined"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "heartO",
    size: 16
  }), " Favourite"), /*#__PURE__*/React.createElement(ZxButton, {
    variant: "outlined"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "chat",
    size: 16
  }), " Comment"), /*#__PURE__*/React.createElement(ZxButton, {
    variant: "transparent",
    shape: "square",
    ariaLabel: "Share"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "globe",
    size: 16
  }))))), /*#__PURE__*/React.createElement("div", {
    className: "layout-2col"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "section-title"
  }, /*#__PURE__*/React.createElement("h2", null, "Comments")), /*#__PURE__*/React.createElement("div", {
    className: "comments"
  }, SAMPLE_COMMENTS.map((c, i) => /*#__PURE__*/React.createElement("div", {
    className: "comment",
    key: i
  }, /*#__PURE__*/React.createElement("div", {
    className: "comment__head"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "person",
    size: 14
  }), /*#__PURE__*/React.createElement("span", {
    className: "comment__author"
  }, c.author), /*#__PURE__*/React.createElement("span", {
    className: "comment__date"
  }, c.date)), /*#__PURE__*/React.createElement("div", {
    className: "comment__body"
  }, c.body))), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 8,
      display: "flex",
      gap: 8
    }
  }, /*#__PURE__*/React.createElement("input", {
    className: "zx-input",
    style: {
      flex: 1
    },
    placeholder: "Add a comment..."
  }), /*#__PURE__*/React.createElement(ZxButton, {
    variant: "primary"
  }, "Post")))), /*#__PURE__*/React.createElement("aside", null, /*#__PURE__*/React.createElement("div", {
    className: "aside-panel",
    style: {
      marginBottom: 16
    }
  }, /*#__PURE__*/React.createElement("h3", null, "More by ", picture.authors[0]), /*#__PURE__*/React.createElement("div", {
    className: "aside-list"
  }, SAMPLE_PICTURES.filter(p => p.id !== picture.id).slice(0, 5).map(p => /*#__PURE__*/React.createElement("a", {
    href: "#",
    key: p.id,
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "image",
    size: 14
  }), /*#__PURE__*/React.createElement("span", {
    style: {
      overflow: "hidden",
      textOverflow: "ellipsis",
      whiteSpace: "nowrap"
    }
  }, p.title), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "auto",
      color: "var(--text-light-color)",
      fontFamily: "var(--font-mono)",
      fontSize: 11
    }
  }, p.year))))), /*#__PURE__*/React.createElement("div", {
    className: "aside-panel"
  }, /*#__PURE__*/React.createElement("h3", null, "From the same party"), /*#__PURE__*/React.createElement("div", {
    className: "aside-list"
  }, SAMPLE_PICTURES.slice(0, 4).map(p => /*#__PURE__*/React.createElement("a", {
    href: "#",
    key: p.id,
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, p.place || "—"), /*#__PURE__*/React.createElement("span", {
    style: {
      overflow: "hidden",
      textOverflow: "ellipsis",
      whiteSpace: "nowrap"
    }
  }, p.title))))))));
};
const SAMPLE_COMMENTS = [{
  author: "Diver/4D",
  date: "12 Apr 2003",
  body: "Reuploaded with a fixed colour-clash on the third row. Thanks for the catches."
}, {
  author: "Andy/CFM",
  date: "13 Apr 2003",
  body: "That horizon! How did you do the dithering on the sky — Beta line by line?"
}, {
  author: "g0blinish",
  date: "01 Feb 2014",
  body: "Still one of my favourite SCRs of the era. Aged like wine."
}];
function ordinal(n) {
  const s = ["th", "st", "nd", "rd"],
    v = n % 100;
  return n + (s[(v - 20) % 10] || s[v] || s[0]) + " place";
}
window.PictureDetail = PictureDetail;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/PictureDetail.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/PicturePage.jsx
try { (() => {
/* PicturePage.jsx — ZX Spectrum picture detail page (v2).
   • Hero: comfortable fit view + scale toggle (1×/2×/3×) + fullscreen
     lightbox with pan/zoom + hover magnifier for regions.
   • Prod ("из какой программы") raised to a prominent banner up top.
   • Tags promoted to their own prominent band.
   • Votes + comments shown 50/50 in two columns.
   • Drawing-stages: nameless scrubber, supports 5–50 frames.
   `minimal` strips the page back to image + meta + downloads (the common case). */

const {
  useState,
  useRef,
  useMemo,
  useEffect,
  useCallback
} = React;

/* ── local icon set (kept inline to avoid global scope collisions) ── */
function PIcon({
  name,
  size = 16
}) {
  const p = {
    download: "M5 20h14v-2H5v2zm7-18l-5.5 5.5h3.5V14h4V7.5h3.5L12 2z",
    zoom: "M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.7.7l.27.28v.79l5 5 1.5-1.5-5-5zm-6 0A4.5 4.5 0 1 1 14 9.5 4.5 4.5 0 0 1 9.5 14zM9 9.5h-2v-2H6v2H4v1h2v2h1v-2h2z",
    expand: "M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z",
    play: "M8 5v14l11-7z",
    pause: "M6 5h4v14H6zm8 0h4v14h-4z",
    plus: "M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6z",
    chevron: "M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z",
    close: "M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z",
    film: "M4 4h16v16H4V4zm2 2v2h2V6H6zm10 0v2h2V6h-2zM6 10v4h12v-4H6zm0 6v2h2v-2H6zm10 0v2h2v-2h-2z",
    prod: "M4 5h16v11H4V5zm2 2v7h12V7H6zm-2 11h16v2H4v-2z"
  };
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: "0 0 24 24",
    width: size,
    height: size,
    fill: "currentColor",
    "aria-hidden": "true",
    style: {
      flexShrink: 0
    }
  }, /*#__PURE__*/React.createElement("path", {
    d: p[name]
  }));
}
function Medal({
  place
}) {
  if (!place || place > 3) return null;
  const m = ["gold", "silver", "bronze"][place - 1];
  return /*#__PURE__*/React.createElement("span", {
    className: "zx-medal zx-medal--" + m
  }, place);
}
const clamp = (v, lo, hi) => Math.max(lo, Math.min(hi, v));

/* per-picture render-mode segmented control. First option ("g") = inherit the
   site-wide setting; an explicit pick overrides just this picture. */
function RenderSeg({
  label,
  value,
  onSet,
  options
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "pix-render__group"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-render__label"
  }, label), /*#__PURE__*/React.createElement("div", {
    className: "pix-seg"
  }, options.map(([k, l]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    className: "pix-seg__btn" + (k === "g" ? " global" : "") + (value === k ? " active" : ""),
    onClick: () => onSet(k),
    title: k === "g" ? "Наследовать глобальную настройку сайта" : l
  }, l))));
}

/* ─────────────────────── Fullscreen lightbox ───────────────────────
   Big view + pan/zoom. Scale presets 1×/2×/3× are native-pixel multiples
   (pixelated); drag to pan when the image overflows the viewport. */
function Lightbox({
  src,
  w,
  h,
  onClose
}) {
  // integer-only zoom (pixel art must scale by whole multiples). Default = the
  // largest whole multiple that fits the viewport, so it opens big AND crisp.
  const fitMult = () => clamp(Math.floor(Math.min(window.innerHeight * 0.78 / h, window.innerWidth * 0.92 / w)), 1, 8);
  const [scale, setScale] = useState(fitMult);
  const [off, setOff] = useState({
    x: 0,
    y: 0
  });
  const drag = useRef(null);
  const wrapRef = useRef(null);
  useEffect(() => {
    const onKey = e => {
      if (e.key === "Escape") onClose();
      if (e.key === "+" || e.key === "=") setZoom(s => clamp(s + 1, 1, 8));
      if (e.key === "-") setZoom(s => clamp(s - 1, 1, 8));
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [onClose]);
  const setZoom = s => {
    setScale(s);
    setOff({
      x: 0,
      y: 0
    });
  };
  const onWheel = e => {
    e.preventDefault();
    setScale(s => clamp(s + (e.deltaY < 0 ? 1 : -1), 1, 8));
  };
  function onDown(e) {
    if (!scale) return;
    drag.current = {
      sx: e.clientX,
      sy: e.clientY,
      ox: off.x,
      oy: off.y
    };
  }
  function onMove(e) {
    if (!drag.current) return;
    setOff({
      x: drag.current.ox + (e.clientX - drag.current.sx),
      y: drag.current.oy + (e.clientY - drag.current.sy)
    });
  }
  const endDrag = () => {
    drag.current = null;
  };
  const imgStyle = {
    width: w * scale,
    height: h * scale,
    transform: `translate(${off.x}px, ${off.y}px)`,
    cursor: drag.current ? "grabbing" : "grab"
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "pix-lb",
    onMouseDown: e => {
      if (e.target === e.currentTarget) onClose();
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-lb__bar",
    onMouseDown: e => e.stopPropagation()
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-lb__dim"
  }, w, "\xD7", h, " px"), /*#__PURE__*/React.createElement("div", {
    className: "pix-lb__zoom"
  }, [1, 2, 3, 4, 6, 8].map(s => /*#__PURE__*/React.createElement("button", {
    key: s,
    className: "pix-lb__chip" + (scale === s ? " active" : ""),
    onClick: () => setZoom(s)
  }, s, "\xD7"))), /*#__PURE__*/React.createElement("button", {
    className: "pix-lb__close",
    onClick: onClose,
    title: "\u0417\u0430\u043A\u0440\u044B\u0442\u044C (Esc)"
  }, /*#__PURE__*/React.createElement(PIcon, {
    name: "close",
    size: 20
  }))), /*#__PURE__*/React.createElement("div", {
    className: "pix-lb__stage",
    ref: wrapRef,
    onWheel: onWheel,
    onMouseDown: onDown,
    onMouseMove: onMove,
    onMouseUp: endDrag,
    onMouseLeave: endDrag
  }, /*#__PURE__*/React.createElement("img", {
    src: src,
    alt: "\u041A\u0430\u0440\u0442\u0438\u043D\u043A\u0430 \u043A\u0440\u0443\u043F\u043D\u043E",
    style: imgStyle,
    draggable: "false"
  })), /*#__PURE__*/React.createElement("div", {
    className: "pix-lb__hint"
  }, "\u041A\u043E\u043B\u0435\u0441\u043E \u0438\u043B\u0438 \u043A\u043D\u043E\u043F\u043A\u0438 \u2014 \u043C\u0430\u0441\u0448\u0442\u0430\u0431 \xB7 \u0442\u044F\u043D\u0438\u0442\u0435 \u2014 \u043F\u0435\u0440\u0435\u043C\u0435\u0449\u0430\u0442\u044C\u0441\u044F \xB7 Esc \u2014 \u0437\u0430\u043A\u0440\u044B\u0442\u044C"));
}

/* ─────────────────────── Hero image viewer ─────────────────────────
   Default "fit" view with a hover magnifier for quick region peeks,
   a scale toggle (1×/2×/3× exact pixels — scrollable when larger than
   the frame) and a fullscreen button. */
function HeroImage({
  src,
  w,
  h
}) {
  const ref = useRef(null);
  const [scale, setScale] = useState("wide");
  const [lens, setLens] = useState(null);
  const [lb, setLb] = useState(false);
  // per-picture render overrides — "g" = inherit the global/site setting (default)
  const [border, setBorder] = useState("g");
  const [giga, setGiga] = useState("g");
  const [hidden, setHidden] = useState("g");
  const Z = 3,
    VIEW_W = 384,
    VIEW_H = Math.round(VIEW_W * h / w);
  function move(e) {
    if (scale !== "wide") return;
    const img = ref.current;
    if (!img) return;
    const r = img.getBoundingClientRect();
    setLens({
      x: clamp((e.clientX - r.left) / r.width, 0, 1),
      y: clamp((e.clientY - r.top) / r.height, 0, 1),
      w: r.width,
      h: r.height
    });
  }
  let lensBox = null,
    zoomBox = null;
  if (lens && scale === "wide") {
    const lw = lens.w / Z,
      lh = lens.h / Z;
    lensBox = {
      width: lw,
      height: lh,
      left: clamp(lens.x * lens.w - lw / 2, 0, lens.w - lw),
      top: clamp(lens.y * lens.h - lh / 2, 0, lens.h - lh)
    };
    zoomBox = {
      bgX: clamp(VIEW_W / 2 - lens.x * VIEW_W * Z, VIEW_W * (1 - Z), 0),
      bgY: clamp(VIEW_H / 2 - lens.y * VIEW_H * Z, VIEW_H * (1 - Z), 0)
    };
  }
  const mult = scale === "wide" ? 0 : Number(scale);
  const scaled = mult > 0;

  // render-effect classes derived from the overrides
  const imgFx = (giga === "flicker" ? " pix-fx-img-flicker" : "") + (giga === "mix" ? " pix-fx-img-mix" : "");
  const scanClass = giga === "interlace" ? "pix-fx pix-fx--scan" : giga === "interlace2x" ? "pix-fx pix-fx--scan2" : "";
  return /*#__PURE__*/React.createElement("div", {
    className: "pix-viewer"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-stage" + (scaled ? " pix-stage--scroll" : "") + (border === "on" ? " pix-stage--bordered" : ""),
    onMouseMove: move,
    onMouseLeave: () => setLens(null),
    onClick: () => {
      if (!scaled) setLb(true);
    },
    style: scaled ? {
      cursor: "default"
    } : null
  }, /*#__PURE__*/React.createElement("img", {
    ref: ref,
    className: "pix-stage__img" + imgFx,
    src: src,
    alt: "\u041A\u0430\u0440\u0442\u0438\u043D\u043A\u0430",
    draggable: "false",
    style: scaled ? {
      width: w * mult,
      height: h * mult,
      maxWidth: "none"
    } : null
  }), scanClass && /*#__PURE__*/React.createElement("div", {
    className: scanClass
  }), hidden === "on" && /*#__PURE__*/React.createElement("div", {
    className: "pix-fx pix-fx--hidden"
  }), !scaled && /*#__PURE__*/React.createElement("span", {
    className: "pix-stage__hint",
    style: {
      opacity: lens ? 0 : 1
    }
  }, /*#__PURE__*/React.createElement(PIcon, {
    name: "zoom",
    size: 13
  }), " \u043D\u0430\u0432\u0435\u0434\u0438\u0442\u0435 \u2014 \u043B\u0443\u043F\u0430 \xB7 \u043A\u043B\u0438\u043A \u2014 \u043A\u0440\u0443\u043F\u043D\u043E"), lensBox && /*#__PURE__*/React.createElement("div", {
    className: "pix-stage__lens",
    style: {
      left: lensBox.left,
      top: lensBox.top,
      width: lensBox.width,
      height: lensBox.height
    }
  }), zoomBox && /*#__PURE__*/React.createElement("div", {
    className: "pix-zoom"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-zoom__view",
    style: {
      height: VIEW_H,
      backgroundImage: `url(${src})`,
      backgroundSize: `${VIEW_W * Z}px ${VIEW_H * Z}px`,
      backgroundPosition: `${zoomBox.bgX}px ${zoomBox.bgY}px`
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "pix-zoom__bar"
  }, /*#__PURE__*/React.createElement("span", null, "\u043B\u0443\u043F\u0430 \xB7 ", Z, "\xD7"), /*#__PURE__*/React.createElement("span", null, "\u043F\u0438\u043A\u0441\u0435\u043B\u044C \u0432 \u043F\u0438\u043A\u0441\u0435\u043B\u044C")))), /*#__PURE__*/React.createElement("div", {
    className: "pix-viewer__bar"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-viewer__dim"
  }, w, "\xD7", h), /*#__PURE__*/React.createElement("div", {
    className: "pix-viewer__scales"
  }, [["1×", "1"], ["2×", "2"], ["3×", "3"], ["Широкий", "wide"]].map(([lbl, v]) => /*#__PURE__*/React.createElement("button", {
    key: v,
    className: "pix-viewer__chip" + (scale === v ? " active" : ""),
    onClick: () => setScale(v)
  }, lbl))), /*#__PURE__*/React.createElement("button", {
    className: "pix-viewer__big",
    onClick: () => setLb(true)
  }, /*#__PURE__*/React.createElement(PIcon, {
    name: "expand",
    size: 15
  }), " \u041A\u0440\u0443\u043F\u043D\u043E")), /*#__PURE__*/React.createElement("div", {
    className: "pix-render"
  }, /*#__PURE__*/React.createElement(RenderSeg, {
    label: "\u0411\u043E\u0440\u0434\u044E\u0440",
    value: border,
    onSet: setBorder,
    options: [["g", "Глоб."], ["on", "Вкл"], ["off", "Выкл"]]
  }), /*#__PURE__*/React.createElement(RenderSeg, {
    label: "GigaScreen",
    value: giga,
    onSet: setGiga,
    options: [["g", "Глоб."], ["mix", "Mix"], ["flicker", "Flicker"], ["interlace", "Interlace"], ["interlace2x", "×2"]]
  }), /*#__PURE__*/React.createElement(RenderSeg, {
    label: "\u0421\u043A\u0440\u044B\u0442\u044B\u0435 \u043F\u0438\u043A\u0441\u0435\u043B\u0438",
    value: hidden,
    onSet: setHidden,
    options: [["g", "Глоб."], ["on", "Вкл"], ["off", "Выкл"]]
  })), lb && /*#__PURE__*/React.createElement(Lightbox, {
    src: src,
    w: w,
    h: h,
    onClose: () => setLb(false)
  }));
}

/* ─────────────────────── Drawing-stages scrubber ───────────────────
   Scales to 5–50 frames without spamming the page: one big frame, a play
   button and a scrubber drive ALL frames; only up to 5 evenly-spaced
   milestone thumbnails are shown as quick-jump markers. */
function StagesPlayer({
  srcs,
  w,
  h
}) {
  const N = srcs.length;
  const [i, setI] = useState(N - 1);
  const [playing, setPlaying] = useState(false);
  const [lb, setLb] = useState(false);
  useEffect(() => {
    if (!playing) return;
    const id = setInterval(() => setI(p => (p + 1) % N), 380);
    return () => clearInterval(id);
  }, [playing, N]);

  // up to 5 evenly-spaced milestones (always incl. first & last)
  const milestones = useMemo(() => {
    const want = Math.min(5, N);
    const set = new Set();
    for (let k = 0; k < want; k++) set.add(Math.round(k * (N - 1) / (want - 1)));
    return [...set].sort((a, b) => a - b);
  }, [N]);
  return /*#__PURE__*/React.createElement("div", {
    className: "pix-stages"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-stages__view",
    onClick: () => setLb(true),
    title: "\u041E\u0442\u043A\u0440\u044B\u0442\u044C \u043A\u0440\u0443\u043F\u043D\u043E"
  }, /*#__PURE__*/React.createElement("img", {
    src: srcs[i],
    alt: "Стадия " + (i + 1)
  }), /*#__PURE__*/React.createElement("span", {
    className: "pix-stages__caption"
  }, i + 1, " / ", N), /*#__PURE__*/React.createElement("button", {
    className: "pix-stages__expand",
    onClick: e => {
      e.stopPropagation();
      setLb(true);
    },
    title: "\u041A\u0440\u0443\u043F\u043D\u043E"
  }, /*#__PURE__*/React.createElement(PIcon, {
    name: "expand",
    size: 15
  }))), /*#__PURE__*/React.createElement("div", {
    className: "pix-stages__side"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-stages__controls"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined zx-button--sm",
    type: "button",
    onClick: () => setPlaying(p => !p)
  }, /*#__PURE__*/React.createElement(PIcon, {
    name: playing ? "pause" : "play",
    size: 14
  }), playing ? "Пауза" : "Проиграть"), /*#__PURE__*/React.createElement("input", {
    className: "pix-stages__range",
    type: "range",
    min: "0",
    max: N - 1,
    value: i,
    onChange: e => {
      setI(Number(e.target.value));
      setPlaying(false);
    }
  }), /*#__PURE__*/React.createElement("span", {
    className: "pix-stages__num"
  }, /*#__PURE__*/React.createElement(PIcon, {
    name: "film",
    size: 13
  }), " ", i + 1, "/", N)), /*#__PURE__*/React.createElement("div", {
    className: "pix-stages__milestones"
  }, milestones.map(idx => /*#__PURE__*/React.createElement("button", {
    key: idx,
    className: "pix-stages__ms" + (idx === i ? " active" : ""),
    onClick: () => {
      setI(idx);
      setPlaying(false);
    },
    title: "Стадия " + (idx + 1)
  }, /*#__PURE__*/React.createElement("span", {
    className: "thumb"
  }, /*#__PURE__*/React.createElement("img", {
    src: srcs[idx],
    alt: ""
  })), /*#__PURE__*/React.createElement("span", {
    className: "n"
  }, idx + 1)))), /*#__PURE__*/React.createElement("p", {
    className: "pix-stages__hint"
  }, N, " \u043A\u0430\u0434\u0440\u043E\u0432 \xB7 \u0442\u044F\u043D\u0438\u0442\u0435 \u043F\u043E\u043B\u0437\u0443\u043D\u043E\u043A \u0438\u043B\u0438 \u043D\u0430\u0436\u043C\u0438\u0442\u0435 \xAB\u041F\u0440\u043E\u0438\u0433\u0440\u0430\u0442\u044C\xBB. \u041A\u043B\u0438\u043A \u043F\u043E \u043A\u0430\u0434\u0440\u0443 \u2014 \u043A\u0440\u0443\u043F\u043D\u043E.")), lb && /*#__PURE__*/React.createElement(Lightbox, {
    src: srcs[i],
    w: w,
    h: h,
    onClose: () => setLb(false)
  }));
}

/* ── Related rail ── */
function Rail({
  title,
  kicker,
  items
}) {
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "pix-rail__h"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, title), kicker && /*#__PURE__*/React.createElement("span", {
    className: "kicker"
  }, kicker)), /*#__PURE__*/React.createElement("div", {
    className: "pix-rail"
  }, items.map(it => /*#__PURE__*/React.createElement("a", {
    className: "pix-mini",
    key: it.id,
    href: "#",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-mini__thumb"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: it.id,
    palette: it.palette
  }), it.place && it.place <= 3 && /*#__PURE__*/React.createElement("span", {
    className: "pix-mini__medal"
  }, /*#__PURE__*/React.createElement(Medal, {
    place: it.place
  }))), /*#__PURE__*/React.createElement("span", {
    className: "pix-mini__body"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-mini__title"
  }, it.title), /*#__PURE__*/React.createElement("span", {
    className: "pix-mini__sub"
  }, it.authors)), /*#__PURE__*/React.createElement("span", {
    className: "pix-mini__year"
  }, it.year)))));
}

/* ── Votes + comments panels (used side by side) ── */
function VotesPanel({
  votes,
  rating
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "pix-col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-col__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0413\u043E\u043B\u043E\u0441\u0430 ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, votes.length)), votes.length > 0 && /*#__PURE__*/React.createElement("span", {
    className: "right"
  }, "\u0441\u0440\u0435\u0434\u043D\u044F\u044F ", /*#__PURE__*/React.createElement("b", null, "\u2605 ", rating))), votes.length > 0 ? /*#__PURE__*/React.createElement("div", {
    className: "pix-list"
  }, votes.map((v, i) => /*#__PURE__*/React.createElement("div", {
    className: "pix-list__row",
    key: i
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-list__date"
  }, v.date), /*#__PURE__*/React.createElement("span", {
    className: "pix-list__user"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, v.user)), /*#__PURE__*/React.createElement("span", {
    className: "pix-list__score"
  }, "★".repeat(v.score), "☆".repeat(5 - v.score))))) : /*#__PURE__*/React.createElement("p", {
    className: "pix-empty"
  }, "\u042D\u0442\u0443 \u0440\u0430\u0431\u043E\u0442\u0443 \u0435\u0449\u0451 \u043D\u0438\u043A\u0442\u043E \u043D\u0435 \u043E\u0446\u0435\u043D\u0438\u0432\u0430\u043B."));
}
function CommentsPanel({
  comments
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "pix-col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-col__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u041A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0438 ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, comments.length))), comments.length > 0 ? /*#__PURE__*/React.createElement("div", {
    className: "pix-comments"
  }, comments.map(c => /*#__PURE__*/React.createElement("div", {
    className: "pix-comment",
    key: c.id
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-comment__head"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-comment__user"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, c.user)), /*#__PURE__*/React.createElement("span", {
    className: "pix-comment__date"
  }, c.date)), /*#__PURE__*/React.createElement("div", {
    className: "pix-comment__body"
  }, c.body)))) : /*#__PURE__*/React.createElement("p", {
    className: "pix-empty"
  }, "\u0411\u0443\u0434\u044C\u0442\u0435 \u043F\u0435\u0440\u0432\u044B\u043C, \u043A\u0442\u043E \u043E\u0441\u0442\u0430\u0432\u0438\u0442 \u043A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0439."), /*#__PURE__*/React.createElement("div", {
    className: "pix-add"
  }, /*#__PURE__*/React.createElement("textarea", {
    placeholder: "\u041F\u043E\u0434\u0435\u043B\u0438\u0442\u044C\u0441\u044F \u043D\u0430\u0431\u043B\u044E\u0434\u0435\u043D\u0438\u0435\u043C \u043E \u0440\u0430\u0431\u043E\u0442\u0435\u2026"
  }), /*#__PURE__*/React.createElement("div", {
    className: "pix-add__foot"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-add__hint"
  }, "Markdown \u043F\u043E\u0434\u0434\u0435\u0440\u0436\u0438\u0432\u0430\u0435\u0442\u0441\u044F"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--primary zx-button--sm",
    type: "button"
  }, "\u041E\u0442\u043F\u0440\u0430\u0432\u0438\u0442\u044C"))));
}
function PicturePage({
  minimal = false
}) {
  const p = PICTURE;
  const finalSrc = useMemo(() => makePixelArt(3), []);
  const stageSrcs = useMemo(() => makeStageFrames(STAGE_COUNT), []);
  const [imgW, imgH] = p.resolution.split("×").map(Number);
  const hasProd = !minimal;
  const hasParty = !minimal;
  const materials = minimal ? [] : MATERIALS;
  const showStages = !minimal;
  const tags = minimal ? [] : p.tags;
  const votes = minimal ? [] : VOTES;
  const comments = minimal ? [] : COMMENTS;
  const showRails = !minimal;
  const rating = minimal ? null : {
    score: p.rating,
    count: p.votes
  };
  const views = minimal ? 47 : p.views;
  return /*#__PURE__*/React.createElement("div", {
    className: "pix-root"
  }, /*#__PURE__*/React.createElement("nav", {
    className: "zx-breadcrumbs",
    "aria-label": "breadcrumb"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0413\u043B\u0430\u0432\u043D\u0430\u044F"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u041A\u0430\u0440\u0442\u0438\u043D\u043A\u0438"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, p.authors[0].name), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("span", {
    className: "here"
  }, p.title)), /*#__PURE__*/React.createElement("div", {
    className: "pix-hero"
  }, /*#__PURE__*/React.createElement(HeroImage, {
    src: finalSrc,
    w: imgW,
    h: imgH
  }), /*#__PURE__*/React.createElement("div", {
    className: "pix-head"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-head__top"
  }, /*#__PURE__*/React.createElement("h1", {
    className: "pix-head__title"
  }, p.title), /*#__PURE__*/React.createElement("span", {
    className: "pix-head__id"
  }, "#", p.id)), /*#__PURE__*/React.createElement("div", {
    className: "pix-head__authors"
  }, p.authors.map((a, i) => /*#__PURE__*/React.createElement(React.Fragment, {
    key: a.id
  }, i > 0 && ", ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, a.name))), /*#__PURE__*/React.createElement("span", {
    className: "pix-head__year"
  }, " \xB7 ", p.year)), (hasParty || hasProd) && /*#__PURE__*/React.createElement("div", {
    className: "pix-context"
  }, hasParty && /*#__PURE__*/React.createElement("div", {
    className: "pix-context__row"
  }, /*#__PURE__*/React.createElement(Medal, {
    place: p.party.place
  }), /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, p.party.name), /*#__PURE__*/React.createElement("span", {
    className: "dim"
  }, " \xB7 ", p.party.compo, " \xB7 ", p.party.place, " \u043C\u0435\u0441\u0442\u043E"))), hasProd && /*#__PURE__*/React.createElement("div", {
    className: "pix-context__row"
  }, /*#__PURE__*/React.createElement(PIcon, {
    name: "prod",
    size: 15
  }), /*#__PURE__*/React.createElement("span", null, "\u0418\u0437 ", p.prod.kind, " ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("b", null, "\xAB", p.prod.title, "\xBB")), " \xB7 ", p.prod.year))), /*#__PURE__*/React.createElement("div", {
    className: "pix-rate"
  }, rating ? /*#__PURE__*/React.createElement("div", {
    className: "pix-rate__score"
  }, /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, rating.score), /*#__PURE__*/React.createElement("span", {
    className: "of"
  }, "/ 5")) : /*#__PURE__*/React.createElement("div", {
    className: "pix-rate__score"
  }, /*#__PURE__*/React.createElement("span", {
    className: "of"
  }, "\u0413\u043E\u043B\u043E\u0441\u043E\u0432 \u043F\u043E\u043A\u0430 \u043D\u0435\u0442")), /*#__PURE__*/React.createElement(VoteWidget, {
    myVote: minimal ? 0 : p.myVote,
    fav: p.fav
  }), /*#__PURE__*/React.createElement("div", {
    className: "pix-rate__counts"
  }, rating && /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", null, rating.count), " \u0433\u043E\u043B\u043E\u0441\u043E\u0432"), /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", null, views.toLocaleString("ru-RU")), " \u043F\u0440\u043E\u0441\u043C\u043E\u0442\u0440\u043E\u0432"))), /*#__PURE__*/React.createElement("div", {
    className: "pix-added"
  }, "\u0414\u043E\u0431\u0430\u0432\u0438\u043B ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, p.addedBy.name), " \xB7 ", p.addedAt))), tags.length > 0 && /*#__PURE__*/React.createElement("div", {
    className: "pix-tagband"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-tagband__label"
  }, "\u0422\u0435\u0433\u0438"), /*#__PURE__*/React.createElement("span", {
    className: "pix-tags"
  }, tags.map(t => /*#__PURE__*/React.createElement("a", {
    key: t,
    href: "#",
    onClick: e => e.preventDefault()
  }, t)))), /*#__PURE__*/React.createElement("div", {
    className: "pix-cols"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-panel"
  }, /*#__PURE__*/React.createElement("h2", {
    className: "pix-panel__h"
  }, "\u0421\u0432\u0435\u0434\u0435\u043D\u0438\u044F"), /*#__PURE__*/React.createElement("dl", {
    className: "pix-meta"
  }, /*#__PURE__*/React.createElement("dt", null, "\u0413\u043E\u0434"), /*#__PURE__*/React.createElement("dd", null, p.year), /*#__PURE__*/React.createElement("dt", null, "\u0424\u043E\u0440\u043C\u0430\u0442"), /*#__PURE__*/React.createElement("dd", null, p.format, " \xB7 ", /*#__PURE__*/React.createElement("span", {
    className: "mono"
  }, p.resolution)), /*#__PURE__*/React.createElement("dt", null, "\u041F\u0430\u043B\u0438\u0442\u0440\u0430"), /*#__PURE__*/React.createElement("dd", null, p.palette), /*#__PURE__*/React.createElement("dt", null, "\u0411\u043E\u0440\u0434\u044E\u0440"), /*#__PURE__*/React.createElement("dd", null, p.border), /*#__PURE__*/React.createElement("dt", null, "\u0420\u0435\u0430\u043B\u0442\u0430\u0439\u043C"), /*#__PURE__*/React.createElement("dd", {
    className: p.realtime ? "pix-flag--on" : "pix-flag"
  }, p.realtime ? "да" : "нет"), hasParty && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("dt", null, "\u041F\u0430\u0442\u0438/\u043A\u043E\u043D\u043A\u0443\u0440\u0441"), /*#__PURE__*/React.createElement("dd", null, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, p.party.name)))), /*#__PURE__*/React.createElement("details", {
    className: "pix-tech"
  }, /*#__PURE__*/React.createElement("summary", null, /*#__PURE__*/React.createElement("span", {
    className: "chev"
  }, /*#__PURE__*/React.createElement(PIcon, {
    name: "chevron",
    size: 14
  })), "\u0422\u0435\u0445\u043D\u0438\u0447\u0435\u0441\u043A\u0438\u0435 \u0434\u0430\u043D\u043D\u044B\u0435"), /*#__PURE__*/React.createElement("dl", {
    className: "pix-tech__grid"
  }, /*#__PURE__*/React.createElement("dt", null, "\u0418\u043C\u044F \u0444\u0430\u0439\u043B\u0430"), /*#__PURE__*/React.createElement("dd", null, p.file.name), /*#__PURE__*/React.createElement("dt", null, "\u0420\u0430\u0437\u043C\u0435\u0440"), /*#__PURE__*/React.createElement("dd", null, p.file.bytes.toLocaleString("ru-RU"), " \u0431\u0430\u0439\u0442"), /*#__PURE__*/React.createElement("dt", null, "\u0420\u0430\u0437\u0440\u0435\u0448\u0435\u043D\u0438\u0435"), /*#__PURE__*/React.createElement("dd", null, p.resolution, " px"), /*#__PURE__*/React.createElement("dt", null, "\u0410\u0442\u0440\u0438\u0431\u0443\u0442\u044B"), /*#__PURE__*/React.createElement("dd", null, p.file.attrW, "\xD7", p.file.attrH, " \u0437\u043D\u0430\u043A\u043E\u043C\u0435\u0441\u0442"), /*#__PURE__*/React.createElement("dt", null, "\u0413\u043B\u0443\u0431\u0438\u043D\u0430"), /*#__PURE__*/React.createElement("dd", null, p.file.depth)))), /*#__PURE__*/React.createElement("div", {
    className: "pix-panel"
  }, /*#__PURE__*/React.createElement("h2", {
    className: "pix-panel__h"
  }, "\u0421\u043A\u0430\u0447\u0430\u0442\u044C ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, DOWNLOADS.length, " \u0444\u043E\u0440\u043C\u0430\u0442\u0430")), /*#__PURE__*/React.createElement("div", {
    className: "pix-dl"
  }, DOWNLOADS.map(d => /*#__PURE__*/React.createElement("a", {
    className: "pix-dl__row",
    key: d.id,
    href: "#",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-dl__badge pix-dl__badge--" + d.kind
  }, d.ext), /*#__PURE__*/React.createElement("span", {
    className: "pix-dl__body"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-dl__label"
  }, d.label), /*#__PURE__*/React.createElement("span", {
    className: "pix-dl__sub"
  }, d.sub)), /*#__PURE__*/React.createElement("span", {
    className: "pix-dl__size"
  }, d.size)))))), materials.length > 0 && /*#__PURE__*/React.createElement("section", {
    className: "pix-section"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u041C\u0430\u0442\u0435\u0440\u0438\u0430\u043B\u044B \u0438 \u0440\u0435\u0444\u0435\u0440\u0435\u043D\u0441\u044B"), /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, materials.length)), /*#__PURE__*/React.createElement("div", {
    className: "pix-tiles"
  }, materials.map(m => /*#__PURE__*/React.createElement("a", {
    className: "pix-tile",
    key: m.id,
    href: "#",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-tile__img"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-tile__stripe"
  }, /*#__PURE__*/React.createElement("span", null, m.kind === "photo" ? "фото-референс" : "набросок"))), /*#__PURE__*/React.createElement("span", {
    className: "pix-tile__meta"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pix-tile__label"
  }, m.label), /*#__PURE__*/React.createElement("span", {
    className: "pix-tile__sub"
  }, m.sub)))))), showStages && /*#__PURE__*/React.createElement("section", {
    className: "pix-section"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0421\u0442\u0430\u0434\u0438\u0438 \u0440\u0438\u0441\u043E\u0432\u0430\u043D\u0438\u044F"), /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, stageSrcs.length, " \u043A\u0430\u0434\u0440\u043E\u0432 \xB7 GIF")), /*#__PURE__*/React.createElement(StagesPlayer, {
    srcs: stageSrcs,
    w: imgW,
    h: imgH
  })), /*#__PURE__*/React.createElement("section", {
    className: "pix-section"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-two"
  }, /*#__PURE__*/React.createElement(VotesPanel, {
    votes: votes,
    rating: p.rating
  }), /*#__PURE__*/React.createElement(CommentsPanel, {
    comments: comments
  }))), minimal && /*#__PURE__*/React.createElement("div", {
    className: "pix-contribute"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "pix-contribute__title"
  }, "\u041F\u043E\u043C\u043E\u0433\u0438\u0442\u0435 \u0434\u043E\u043F\u043E\u043B\u043D\u0438\u0442\u044C \u0440\u0430\u0431\u043E\u0442\u0443"), /*#__PURE__*/React.createElement("div", {
    className: "pix-contribute__hint"
  }, "\u0423 \u044D\u0442\u043E\u0439 \u043A\u0430\u0440\u0442\u0438\u043D\u043A\u0438 \u043F\u043E\u043A\u0430 \u043D\u0435\u0442 \u0442\u0435\u0433\u043E\u0432, \u0440\u0435\u0444\u0435\u0440\u0435\u043D\u0441\u043E\u0432 \u0438 \u0437\u0430\u043F\u0438\u0441\u0438 \u0441\u0442\u0430\u0434\u0438\u0439 \u0440\u0438\u0441\u043E\u0432\u0430\u043D\u0438\u044F. \u0415\u0441\u043B\u0438 \u0437\u043D\u0430\u0435\u0442\u0435 \u0430\u0432\u0442\u043E\u0440\u0430, \u043F\u0430\u0442\u0438 \u0438\u043B\u0438 \u043A\u043E\u043D\u0442\u0435\u043A\u0441\u0442 \u2014 \u043F\u043E\u0434\u0435\u043B\u0438\u0442\u0435\u0441\u044C \u0441 \u0430\u0440\u0445\u0438\u0432\u043E\u043C.")), /*#__PURE__*/React.createElement("div", {
    className: "pix-contribute__actions"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined zx-button--sm",
    type: "button"
  }, /*#__PURE__*/React.createElement(PIcon, {
    name: "plus",
    size: 14
  }), "\u0422\u0435\u0433\u0438"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined zx-button--sm",
    type: "button"
  }, /*#__PURE__*/React.createElement(PIcon, {
    name: "plus",
    size: 14
  }), "\u041F\u0430\u0442\u0438"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined zx-button--sm",
    type: "button"
  }, /*#__PURE__*/React.createElement(PIcon, {
    name: "plus",
    size: 14
  }), "\u041C\u0430\u0442\u0435\u0440\u0438\u0430\u043B\u044B"))), showRails && /*#__PURE__*/React.createElement("div", {
    className: "pix-related"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pix-rails"
  }, /*#__PURE__*/React.createElement(Rail, {
    title: "Из " + p.prod.kind + " «" + p.prod.title + "»",
    kicker: p.prod.year,
    items: FROM_PROD
  }), /*#__PURE__*/React.createElement(Rail, {
    title: "Ещё от " + p.authors[0].name,
    kicker: "\u0430\u0432\u0442\u043E\u0440",
    items: BY_AUTHOR
  }), /*#__PURE__*/React.createElement(Rail, {
    title: "\u041F\u043E\u0445\u043E\u0436\u0438\u0435 \u043F\u043E \u0442\u0435\u0433\u0430\u043C",
    kicker: "\u0433\u043E\u0440\u043E\u0434 \xB7 \u043D\u043E\u0447\u044C \xB7 \u043D\u0435\u043E\u043D",
    items: BY_TAGS
  }))));
}
window.PicturePage = PicturePage;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/PicturePage.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/PicturesScreen.jsx
try { (() => {
/** Pictures browser — letter selector, sort, filter chips, grid. */
const PicturesScreen = ({
  onOpenPicture
}) => {
  const [letter, setLetter] = React.useState("E");
  const [sort, setSort] = React.useState("newest");
  const filtered = SAMPLE_PICTURES; // illustrative — not actually filtered
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "crumbs"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Home"), /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "\u203A"), " ", /*#__PURE__*/React.createElement("span", null, "Pictures")), /*#__PURE__*/React.createElement("div", {
    className: "zx-letters",
    style: {
      marginBottom: 12
    }
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    className: letter === "all" ? "active" : "",
    onClick: e => {
      e.preventDefault();
      setLetter("all");
    }
  }, "all"), /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => {
      e.preventDefault();
      setLetter("#");
    }
  }, "#"), "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("").map(L => /*#__PURE__*/React.createElement("a", {
    href: "#",
    key: L,
    className: letter === L ? "active" : "",
    onClick: e => {
      e.preventDefault();
      setLetter(L);
    }
  }, L))), /*#__PURE__*/React.createElement("div", {
    className: "toolbar",
    style: {
      marginBottom: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 6,
      alignItems: "center",
      fontSize: 13,
      color: "var(--text-light-color)"
    }
  }, "Sort:", /*#__PURE__*/React.createElement(ZxButton, {
    size: "xs",
    variant: sort === "newest" ? "primary" : "outlined",
    onClick: () => setSort("newest")
  }, "Newest"), /*#__PURE__*/React.createElement(ZxButton, {
    size: "xs",
    variant: sort === "rated" ? "primary" : "outlined",
    onClick: () => setSort("rated")
  }, "Top rated"), /*#__PURE__*/React.createElement(ZxButton, {
    size: "xs",
    variant: sort === "az" ? "primary" : "outlined",
    onClick: () => setSort("az")
  }, "A \u2192 Z")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 6,
      alignItems: "center"
    }
  }, /*#__PURE__*/React.createElement(ZxBadge, null, ".SCR"), /*#__PURE__*/React.createElement(ZxBadge, null, ".MC"), /*#__PURE__*/React.createElement(ZxBadge, null, "realtime"), /*#__PURE__*/React.createElement(ZxButton, {
    size: "xs",
    variant: "outlined"
  }, "+ filter"))), /*#__PURE__*/React.createElement("div", {
    className: "grid-pictures"
  }, filtered.map(p => /*#__PURE__*/React.createElement(PictureCard, {
    key: p.id,
    picture: p,
    onOpen: onOpenPicture
  }))));
};
window.PicturesScreen = PicturesScreen;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/PicturesScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/Player.jsx
try { (() => {
/** Tune row + sticky bottom player. Mirrors ng-zxart/src/app/features/player/ */
const TuneRow = ({
  tune,
  isPlaying,
  isCurrent,
  onPlay
}) => {
  return /*#__PURE__*/React.createElement("div", {
    className: "zx-tune-row",
    style: isCurrent ? {
      background: "var(--secondary-100)"
    } : null
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-tune-row__play",
    "aria-label": isPlaying ? "Pause" : "Play",
    onClick: () => onPlay(tune)
  }, /*#__PURE__*/React.createElement(Icon, {
    name: isPlaying ? "pause" : "play",
    size: 14
  })), /*#__PURE__*/React.createElement("span", {
    className: "zx-tune-row__title"
  }, tune.title), /*#__PURE__*/React.createElement("span", {
    className: "zx-tune-row__author"
  }, tune.author), /*#__PURE__*/React.createElement("span", {
    className: "zx-tune-row__chip"
  }, tune.chip, " \xB7 ", tune.duration), /*#__PURE__*/React.createElement(ZxButton, {
    size: "xs",
    variant: "transparent",
    shape: "square",
    ariaLabel: "Add to favourites"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "heartO",
    size: 14
  })), /*#__PURE__*/React.createElement(ZxButton, {
    size: "xs",
    variant: "transparent",
    shape: "square",
    ariaLabel: "Download"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "download",
    size: 14
  })));
};
const Player = ({
  tune,
  isPlaying,
  onTogglePlay,
  onNext,
  onPrev,
  progress = 0.42
}) => {
  if (!tune) return null;
  const totalSec = parseDuration(tune.duration);
  const curSec = Math.floor(totalSec * progress);
  return /*#__PURE__*/React.createElement("div", {
    className: "zx-player"
  }, /*#__PURE__*/React.createElement("div", {
    className: "zx-player__controls"
  }, /*#__PURE__*/React.createElement(ZxButton, {
    size: "sm",
    variant: "transparent",
    shape: "round",
    ariaLabel: "Previous",
    onClick: onPrev
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "prev",
    size: 16
  })), /*#__PURE__*/React.createElement(ZxButton, {
    size: "md",
    variant: "primary",
    shape: "round",
    ariaLabel: isPlaying ? "Pause" : "Play",
    onClick: onTogglePlay
  }, /*#__PURE__*/React.createElement(Icon, {
    name: isPlaying ? "pause" : "play",
    size: 18
  })), /*#__PURE__*/React.createElement(ZxButton, {
    size: "sm",
    variant: "transparent",
    shape: "round",
    ariaLabel: "Next",
    onClick: onNext
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "next",
    size: 16
  }))), /*#__PURE__*/React.createElement("div", {
    className: "zx-player__progress"
  }, /*#__PURE__*/React.createElement("div", {
    className: "zx-player__fill",
    style: {
      width: `${progress * 100}%`
    }
  }), /*#__PURE__*/React.createElement("span", {
    className: "zx-player__title"
  }, "\"", tune.title, "\" \u2014 ", tune.author), /*#__PURE__*/React.createElement("span", {
    className: "zx-player__time"
  }, formatTime(curSec), " / ", tune.duration)), /*#__PURE__*/React.createElement(ZxButton, {
    size: "sm",
    variant: "transparent",
    shape: "round",
    ariaLabel: "Shuffle"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "shuffle",
    size: 16
  })), /*#__PURE__*/React.createElement(ZxButton, {
    size: "sm",
    variant: "transparent",
    shape: "round",
    ariaLabel: "Repeat"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "repeat",
    size: 16
  })));
};
function parseDuration(d) {
  const [m, s] = d.split(":").map(n => parseInt(n, 10));
  return m * 60 + s;
}
function formatTime(sec) {
  const m = Math.floor(sec / 60);
  const s = sec % 60;
  return `${m}:${String(s).padStart(2, "0")}`;
}
Object.assign(window, {
  TuneRow,
  Player
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/Player.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/Primitives.jsx
try { (() => {
/** zx-button — port of ng-zxart/src/app/shared/ui/zx-button.component */
const ZxButton = ({
  children,
  size = "md",
  variant = "primary",
  shape,
  // "square" | "round" | undefined
  disabled = false,
  href,
  onClick,
  ariaLabel,
  style
}) => {
  const classes = ["zx-button", `zx-button--${size}`, `zx-button--${variant}`, shape ? `zx-button--${shape}` : ""].filter(Boolean).join(" ");
  const Tag = href ? "a" : "button";
  return /*#__PURE__*/React.createElement(Tag, {
    className: classes,
    href: href,
    onClick: e => {
      if (!href) e.preventDefault?.();
      onClick && onClick(e);
    },
    disabled: disabled,
    "aria-label": ariaLabel,
    style: style
  }, children);
};
const ZxBadge = ({
  children,
  variant = "secondary"
}) => /*#__PURE__*/React.createElement("span", {
  className: `zx-badge zx-badge--${variant}`
}, children);
const ZxMedal = ({
  place
}) => {
  const klass = place === 1 ? "gold" : place === 2 ? "silver" : "bronze";
  return /*#__PURE__*/React.createElement("span", {
    className: `zx-medal zx-medal--${klass}`
  }, place);
};
const ZxStars = ({
  value = 0,
  count = 0
}) => /*#__PURE__*/React.createElement("span", {
  className: "zx-vote"
}, [1, 2, 3, 4, 5].map(i => /*#__PURE__*/React.createElement("svg", {
  key: i,
  className: `star ${i > value ? "star--off" : ""}`,
  viewBox: "0 0 24 24",
  width: "14",
  height: "14",
  fill: "currentColor"
}, /*#__PURE__*/React.createElement("path", {
  d: "M12 2l3.09 6.26 6.91 1-5 4.87L18.18 22 12 18.27 5.82 22l1.18-7.87-5-4.87 6.91-1z"
}))), count > 0 && /*#__PURE__*/React.createElement("span", {
  className: "count"
}, count));
Object.assign(window, {
  ZxButton,
  ZxBadge,
  ZxMedal,
  ZxStars
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/Primitives.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/ProdCard.jsx
try { (() => {
/** Prod card — software production tile. */
const ProdCard = ({
  prod,
  onOpen
}) => {
  return /*#__PURE__*/React.createElement("div", {
    className: "zx-prod"
  }, /*#__PURE__*/React.createElement("a", {
    className: "zx-prod__cover",
    href: "#",
    onClick: e => {
      e.preventDefault();
      onOpen?.(prod);
    }
  }, /*#__PURE__*/React.createElement(PixelArtSVG, {
    seed: prod.id + 999,
    palette: prod.palette || "night"
  })), /*#__PURE__*/React.createElement("div", {
    className: "zx-prod__info"
  }, /*#__PURE__*/React.createElement("div", {
    className: "zx-prod__title"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => {
      e.preventDefault();
      onOpen?.(prod);
    }
  }, prod.title)), /*#__PURE__*/React.createElement("div", {
    className: "zx-prod__authors"
  }, prod.authors.map((a, i) => /*#__PURE__*/React.createElement("span", {
    key: i
  }, i > 0 && ", ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, a)))), /*#__PURE__*/React.createElement("div", {
    className: "zx-prod__meta"
  }, /*#__PURE__*/React.createElement(ZxBadge, null, prod.kind), prod.party && /*#__PURE__*/React.createElement("span", null, prod.party), prod.place && /*#__PURE__*/React.createElement(ZxMedal, {
    place: prod.place
  }), /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--pseudo-link-color)",
      fontWeight: 700,
      marginLeft: "auto"
    }
  }, prod.year)), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 6
    }
  }, /*#__PURE__*/React.createElement(ZxStars, {
    value: prod.stars,
    count: prod.votes
  }))));
};
window.ProdCard = ProdCard;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/ProdCard.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/ProdPage.jsx
try { (() => {
/* Merged prod page — based on user feedback:
   - Hero/info block from Variant A (rich, scannable)
   - Releases table from Variant B (full-width, sortable, votable)
   - NO right sidebar (site already has one)
   - NO "recommended" badge (subjective — solved by sortable votes column instead)
*/
const {
  useState
} = React;
function ProdPage() {
  const [tab, setTab] = useState("releases");
  const [mediaSub, setMediaSub] = useState("articles"); // articles | covers | music | graphics
  const [linksSub, setLinksSub] = useState("series"); // series | compilations
  const [view, setView] = useState("table");
  const [filterLang, setFilterLang] = useState("all");
  const [filterType, setFilterType] = useState("all");
  const [sortBy, setSortBy] = useState("votes"); // votes | year | downloads | plays
  const [sortDir, setSortDir] = useState("desc");
  const filtered = RELEASES.filter(r => (filterLang === "all" || r.lang === filterLang) && (filterType === "all" || r.type === filterType));
  const sorted = [...filtered].sort((a, b) => {
    const va = a[sortBy] ?? 0,
      vb = b[sortBy] ?? 0;
    return sortDir === "desc" ? vb - va : va - vb;
  });
  function clickHeader(key) {
    if (sortBy === key) setSortDir(sortDir === "desc" ? "asc" : "desc");else {
      setSortBy(key);
      setSortDir("desc");
    }
  }
  const arrow = k => sortBy === k ? sortDir === "desc" ? " ↓" : " ↑" : "";
  return /*#__PURE__*/React.createElement("div", {
    style: {
      padding: 24,
      maxWidth: 1280,
      margin: "0 auto",
      fontFamily: "var(--font-sans)",
      color: "var(--text-color)",
      background: "var(--background-page)",
      minHeight: 1700
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)",
      marginBottom: 12,
      fontFamily: "var(--font-mono)"
    }
  }, "\u0413\u043B\u0430\u0432\u043D\u0430\u044F / \u0421\u043E\u0444\u0442 / \u0418\u0433\u0440\u044B / \u041F\u0440\u0438\u043A\u043B\u044E\u0447\u0435\u043D\u0438\u044F / \u041A\u0432\u0435\u0441\u0442\u044B-\u0433\u043E\u043B\u043E\u0432\u043E\u043B\u043E\u043C\u043A\u0438 / ", /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--text-color)"
    }
  }, "Crystal Kingdom Dizzy")), /*#__PURE__*/React.createElement("div", {
    className: "va-hero"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-hero__cover va-hero__cover--video",
    role: "button",
    tabIndex: 0,
    onClick: e => {
      const wrap = e.currentTarget;
      if (wrap.dataset.playing) return;
      wrap.dataset.playing = "1";
      wrap.innerHTML = '<iframe width="100%" height="100%" src="https://www.youtube-nocookie.com/embed/8a4DjcKdpHU?autoplay=1&rel=0" title="Crystal Kingdom Dizzy — longplay" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen style="border:0;width:100%;height:100%;display:block"></iframe>';
    }
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: 42,
    palette: "forest"
  }), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__yt-grad"
  }), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__yt-play",
    "aria-label": "\u0421\u043C\u043E\u0442\u0440\u0435\u0442\u044C \u043D\u0430 YouTube"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "48",
    height: "34",
    viewBox: "0 0 68 48",
    "aria-hidden": "true"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M66.5 7.7c-.8-2.9-3-5.2-5.9-6C55.4.3 34 0 34 0S12.6.3 7.4 1.7C4.5 2.5 2.3 4.8 1.5 7.7 0 13 0 24 0 24s0 11 1.5 16.3c.8 2.9 3 5.2 5.9 6C12.6 47.7 34 48 34 48s21.4-.3 26.6-1.7c2.9-.8 5.1-3.1 5.9-6C68 35 68 24 68 24s0-11-1.5-16.3z",
    fill: "#f00"
  }), /*#__PURE__*/React.createElement("path", {
    d: "M27 34l18-10-18-10v20z",
    fill: "#fff"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__yt-meta"
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-hero__yt-badge"
  }, "YouTube"), /*#__PURE__*/React.createElement("span", null, "Longplay \xB7 38:24")), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__shots"
  }, "\uD83D\uDCF7 ", SCREENS.length, " \u0441\u043A\u0440\u0438\u043D\u043E\u0432")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "va-hero__title-row"
  }, /*#__PURE__*/React.createElement("h1", {
    className: "va-hero__title"
  }, PROD.title), /*#__PURE__*/React.createElement("span", {
    className: "va-hero__year"
  }, "\xB7 ", PROD.year)), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__alias"
  }, "\u0442\u0430\u043A\u0436\u0435 \u0438\u0437\u0432\u0435\u0441\u0442\u043D\u0430 \u043A\u0430\u043A ", /*#__PURE__*/React.createElement("i", null, PROD.alsoKnownAs)), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__chips"
  }, PROD.category.map(c => /*#__PURE__*/React.createElement("span", {
    key: c,
    className: "chip chip--cat"
  }, c)), /*#__PURE__*/React.createElement("span", {
    className: "chip"
  }, "\uD83C\uDDEC\uD83C\uDDE7 English"), /*#__PURE__*/React.createElement("span", {
    className: "chip",
    title: PROD.status
  }, "\u26A0 \u0420\u0430\u0441\u043F\u0440\u043E\u0441\u0442\u0440\u0430\u043D\u0435\u043D\u0438\u0435 \u0437\u0430\u043F\u0440\u0435\u0449\u0435\u043D\u043E")), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__rating-row"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-hero__rating"
  }, /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, PROD.rating.score), /*#__PURE__*/React.createElement("span", {
    className: "of"
  }, "/ ", PROD.rating.ofFive)), /*#__PURE__*/React.createElement(VoteWidget, {
    myVote: 4,
    fav: false
  }), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, PROD.rating.votes, " \u0433\u043E\u043B\u043E\u0441\u043E\u0432 \xB7 \u0432 \u0438\u0437\u0431\u0440\u0430\u043D\u043D\u043E\u043C \u0443 18"), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "auto",
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, "\u0414\u043E\u0431\u0430\u0432\u043B\u0435\u043D\u0430 ", PROD.added)), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__people"
  }, /*#__PURE__*/React.createElement("b", null, "\u0410\u0432\u0442\u043E\u0440\u044B:"), " ", PROD.authors.join(", "), " \xB7 ", /*#__PURE__*/React.createElement("b", null, "\u041C\u0443\u0437\u044B\u043A\u0430:"), " ", PROD.music, " \xB7 ", /*#__PURE__*/React.createElement("b", null, "\u0418\u0437\u0434\u0430\u0442\u0435\u043B\u044C:"), " ", PROD.publisher, " \xB7 ", /*#__PURE__*/React.createElement("b", null, "\u0420\u0430\u0437\u0440\u0430\u0431\u043E\u0442\u0447\u0438\u043A:"), " ", PROD.developer), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 10,
      fontSize: "var(--font-xs)",
      display: "flex",
      gap: 12,
      flexWrap: "wrap"
    }
  }, PROD.links.map((l, i) => /*#__PURE__*/React.createElement("a", {
    key: i,
    href: "#",
    style: {
      color: "var(--primary-600)",
      textDecoration: "none"
    }
  }, "\u2197 ", l.label))))), /*#__PURE__*/React.createElement("div", {
    className: "pp-card",
    style: {
      marginBottom: 16,
      padding: 12
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      marginBottom: 10
    }
  }, "\u0421\u043A\u0440\u0438\u043D\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, SCREENS.length)), /*#__PURE__*/React.createElement("div", {
    className: "va-screens"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-screens__cell va-screens__cell--big"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: SCREENS[0].id,
    palette: SCREENS[0].palette
  })), SCREENS.slice(1, 6).map(s => /*#__PURE__*/React.createElement("div", {
    key: s.id,
    className: "va-screens__cell"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: s.id,
    palette: s.palette
  }))), /*#__PURE__*/React.createElement("a", {
    href: "#",
    className: "va-screens__cell va-screens__more"
  }, "\u041F\u043E\u0441\u043C\u043E\u0442\u0440\u0435\u0442\u044C \u0435\u0449\u0451", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--font-xs)",
      fontWeight: 400,
      opacity: 0.7
    }
  }, "+", SCREENS.length - 6, " \u0441\u043A\u0440\u0438\u043D\u043E\u0432")))), /*#__PURE__*/React.createElement("div", {
    className: "pp-card",
    style: {
      marginBottom: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h"
  }, "\u041E \u043F\u0440\u043E\u0433\u0440\u0430\u043C\u043C\u0435"), /*#__PURE__*/React.createElement("p", {
    style: {
      margin: 0,
      lineHeight: 1.65,
      fontSize: "var(--font-md)"
    }
  }, PROD.story), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 14,
      paddingTop: 12,
      borderTop: "1px dashed var(--secondary-200)",
      display: "flex",
      gap: 6,
      flexWrap: "wrap",
      alignItems: "center"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)",
      textTransform: "uppercase",
      letterSpacing: "0.04em",
      marginRight: 4
    }
  }, "\u0422\u0435\u0433\u0438:"), PROD.tags.map(t => /*#__PURE__*/React.createElement("a", {
    key: t,
    href: "#",
    style: {
      fontSize: "var(--font-xs)",
      padding: "2px 8px",
      background: "var(--secondary-100)",
      border: "1px solid var(--secondary-200)",
      borderRadius: 999,
      color: "var(--text-light-color)",
      textDecoration: "none"
    }
  }, t)))), /*#__PURE__*/React.createElement("div", {
    className: "va-tabs"
  }, /*#__PURE__*/React.createElement("button", {
    className: tab === "releases" ? "active" : "",
    onClick: () => setTab("releases")
  }, "\u0420\u0435\u043B\u0438\u0437\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, RELEASES.length)), /*#__PURE__*/React.createElement("button", {
    className: tab === "media" ? "active" : "",
    onClick: () => setTab("media")
  }, "\u041C\u0435\u0434\u0438\u0430 ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, MENTIONS.length + MAPS.length + PROD_TUNES.length)), /*#__PURE__*/React.createElement("button", {
    className: tab === "links" ? "active" : "",
    onClick: () => setTab("links")
  }, "\u0421\u0432\u044F\u0437\u0438 ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, COMPILATIONS.length + SAME_SERIES.length)), /*#__PURE__*/React.createElement("button", {
    className: tab === "discussion" ? "active" : "",
    onClick: () => setTab("discussion")
  }, "\u041E\u0431\u0441\u0443\u0436\u0434\u0435\u043D\u0438\u0435 ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, VOTES.length))), tab === "releases" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar"
  }, /*#__PURE__*/React.createElement("span", {
    className: "vb-filter-bar__label"
  }, "\u042F\u0437\u044B\u043A:"), /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar__group"
  }, [["all", "все"], ["en", "🇬🇧 EN"], ["ru", "🇷🇺 RU"]].map(([k, l]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    onClick: () => setFilterLang(k),
    className: "zx-button zx-button--sm " + (filterLang === k ? "zx-button--secondary" : "zx-button--outlined")
  }, l))), /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar__sep"
  }), /*#__PURE__*/React.createElement("span", {
    className: "vb-filter-bar__label"
  }, "\u0422\u0438\u043F:"), /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar__group"
  }, [["all", "все"], ...Object.entries(RELEASE_TYPES).map(([k, v]) => [k, v.label])].map(([k, l]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    onClick: () => setFilterType(k),
    className: "zx-button zx-button--sm " + (filterType === k ? "zx-button--secondary" : "zx-button--outlined")
  }, l))), /*#__PURE__*/React.createElement("div", {
    className: "vb-toggle"
  }, /*#__PURE__*/React.createElement("button", {
    className: view === "table" ? "active" : "",
    onClick: () => setView("table")
  }, "\u2630 \u0442\u0430\u0431\u043B\u0438\u0446\u0430"), /*#__PURE__*/React.createElement("button", {
    className: view === "cards" ? "active" : "",
    onClick: () => setView("cards")
  }, "\u25A6 \u043A\u0430\u0440\u0442\u043E\u0447\u043A\u0438"))), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)",
      margin: "0 0 6px",
      fontStyle: "italic"
    }
  }, "\u041B\u0443\u0447\u0448\u0438\u0439 \u0440\u0435\u043B\u0438\u0437 \u2014 \u0442\u043E\u0442, \u0443 \u043A\u043E\u0442\u043E\u0440\u043E\u0433\u043E \u0432\u044B\u0448\u0435 \u0440\u0435\u0439\u0442\u0438\u043D\u0433 \u0441\u043E\u043E\u0431\u0449\u0435\u0441\u0442\u0432\u0430. \u0421\u043E\u0440\u0442\u0438\u0440\u0443\u0439\u0442\u0435 \u043F\u043E \xAB\u0420\u0435\u0439\u0442\u0438\u043D\u0433\xBB, \u2B07 \u0438\u043B\u0438 \u25B6, \u0447\u0442\u043E\u0431\u044B \u043D\u0430\u0439\u0442\u0438 \u043F\u043E\u0434\u0445\u043E\u0434\u044F\u0449\u0438\u0439."), view === "table" && /*#__PURE__*/React.createElement("table", {
    className: "vb-rel-table"
  }, /*#__PURE__*/React.createElement("thead", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null), /*#__PURE__*/React.createElement("th", null, "\u041D\u0430\u0437\u0432\u0430\u043D\u0438\u0435 \xB7 \u0430\u0432\u0442\u043E\u0440"), /*#__PURE__*/React.createElement("th", {
    onClick: () => clickHeader("year"),
    style: {
      cursor: "pointer"
    }
  }, "\u0413\u043E\u0434", arrow("year")), /*#__PURE__*/React.createElement("th", null, "\u0422\u0438\u043F"), /*#__PURE__*/React.createElement("th", null, "\u042F\u0437."), /*#__PURE__*/React.createElement("th", null, "\u041F\u043B\u0430\u0442\u0444\u043E\u0440\u043C\u0430 \xB7 \u0444\u043E\u0440\u043C\u0430\u0442"), /*#__PURE__*/React.createElement("th", {
    onClick: () => clickHeader("votes"),
    style: {
      cursor: "pointer",
      textAlign: "right"
    },
    title: "\u0420\u0435\u0439\u0442\u0438\u043D\u0433 \u0440\u0435\u043B\u0438\u0437\u0430 \u043F\u043E \u0433\u043E\u043B\u043E\u0441\u0430\u043C \u0441\u043E\u043E\u0431\u0449\u0435\u0441\u0442\u0432\u0430"
  }, "\u0420\u0435\u0439\u0442\u0438\u043D\u0433", arrow("votes")), /*#__PURE__*/React.createElement("th", {
    onClick: () => clickHeader("plays"),
    style: {
      cursor: "pointer",
      textAlign: "right"
    }
  }, "\u25B6", arrow("plays")), /*#__PURE__*/React.createElement("th", {
    onClick: () => clickHeader("downloads"),
    style: {
      cursor: "pointer",
      textAlign: "right"
    }
  }, "\u2B07", arrow("downloads")), /*#__PURE__*/React.createElement("th", null))), /*#__PURE__*/React.createElement("tbody", null, sorted.map(r => /*#__PURE__*/React.createElement("tr", {
    key: r.id
  }, /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("div", {
    className: "vb-rel-table__shot"
  }, r.screens.length ? /*#__PURE__*/React.createElement(ZxScreen, {
    seed: r.id * 13,
    palette: ["sunset", "cool", "forest", "night", "default"][r.id % 5]
  }) : null)), /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("div", {
    className: "vb-rel-table__title"
  }, r.title), /*#__PURE__*/React.createElement("div", {
    className: "vb-rel-table__by"
  }, r.releasedBy || "—", r.note ? " · " + r.note : "")), /*#__PURE__*/React.createElement("td", {
    style: {
      fontFamily: "var(--font-mono)"
    }
  }, r.year || "—"), /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("span", {
    className: "rel-type-pill rel-type-pill--" + r.type
  }, RELEASE_TYPES[r.type].label)), /*#__PURE__*/React.createElement("td", null, r.lang === "ru" ? "🇷🇺" : "🇬🇧"), /*#__PURE__*/React.createElement("td", {
    style: {
      fontSize: "var(--font-xs)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "tag-row"
  }, r.format && /*#__PURE__*/React.createElement("span", {
    className: "tag-glyph",
    title: r.format
  }, r.format.includes("SCL") ? "💾" : "📼"), r.hardware.slice(0, 3).map(h => /*#__PURE__*/React.createElement("span", {
    key: h,
    className: "tag-glyph",
    title: h
  }, h.includes("AY") ? "🔊" : h.includes("джойстик") ? "🕹" : "🖥")))), /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "right",
      fontFamily: "var(--font-mono)"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      display: "inline-flex",
      alignItems: "center",
      gap: 3,
      color: "var(--warning-700)",
      fontWeight: 700
    },
    title: `${r.votes || 0} голосов`
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--warning-500)"
    }
  }, "\u2605"), r.votes || "—")), /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "right",
      fontFamily: "var(--font-mono)",
      color: "var(--text-light-color)"
    }
  }, r.plays), /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "right",
      fontFamily: "var(--font-mono)"
    }
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    title: `Скачать (${r.downloads})`,
    style: {
      color: "var(--primary-600)",
      textDecoration: "none",
      fontWeight: 600
    }
  }, "\u2B07 ", r.downloads)), /*#__PURE__*/React.createElement("td", null, r.playOnline && /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--secondary",
    title: "\u0418\u0433\u0440\u0430\u0442\u044C \u043E\u043D\u043B\u0430\u0439\u043D"
  }, "\u25B6")))))), view === "cards" && /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fill, minmax(260px, 1fr))",
      gap: 10
    }
  }, sorted.map(r => /*#__PURE__*/React.createElement("div", {
    key: r.id,
    className: "va-rel-card"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__cover"
  }, r.screens.length ? /*#__PURE__*/React.createElement(ZxScreen, {
    seed: r.id * 13,
    palette: ["sunset", "cool", "forest", "night", "default"][r.id % 5]
  }) : null), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      minWidth: 0
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__title"
  }, r.title), /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__meta"
  }, r.releasedBy || "—", r.year ? " · " + r.year : ""), /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__chips"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rel-type-pill rel-type-pill--" + r.type
  }, RELEASE_TYPES[r.type].label), /*#__PURE__*/React.createElement("span", {
    className: "va-rel-card__chip va-rel-card__chip--lang"
  }, r.lang === "ru" ? "🇷🇺" : "🇬🇧"), r.format && /*#__PURE__*/React.createElement("span", {
    className: "va-rel-card__chip"
  }, r.format), r.note && /*#__PURE__*/React.createElement("span", {
    className: "va-rel-card__chip va-rel-card__chip--cheats"
  }, "\u2605 ", r.note)), /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__bottom"
  }, r.playOnline && /*#__PURE__*/React.createElement("a", {
    className: "play-link",
    href: "#"
  }, "\u25B6 \u0418\u0433\u0440\u0430\u0442\u044C"), /*#__PURE__*/React.createElement("span", null, "\u2B07", r.downloads), /*#__PURE__*/React.createElement("span", null, "\xB7 \u25B6", r.plays), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "auto",
      color: "var(--warning-500)"
    }
  }, "\u2605", r.votes))))))), tab === "media" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "vb-toggle",
    style: {
      marginBottom: 12
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: mediaSub === "articles" ? "active" : "",
    onClick: () => setMediaSub("articles")
  }, "\uD83D\uDCF0 \u0421\u0442\u0430\u0442\u044C\u0438 \u0438 \u043A\u0430\u0440\u0442\u044B ", /*#__PURE__*/React.createElement("span", {
    style: {
      opacity: 0.6
    }
  }, MENTIONS.length + MAPS.length)), /*#__PURE__*/React.createElement("button", {
    className: mediaSub === "covers" ? "active" : "",
    onClick: () => setMediaSub("covers")
  }, "\uD83D\uDCFC \u041E\u0431\u043B\u043E\u0436\u043A\u0438 \u043A\u0430\u0441\u0441\u0435\u0442"), /*#__PURE__*/React.createElement("button", {
    className: mediaSub === "music" ? "active" : "",
    onClick: () => setMediaSub("music")
  }, "\uD83C\uDFB5 \u041C\u0443\u0437\u044B\u043A\u0430 ", /*#__PURE__*/React.createElement("span", {
    style: {
      opacity: 0.6
    }
  }, PROD_TUNES.length)), /*#__PURE__*/React.createElement("button", {
    className: mediaSub === "graphics" ? "active" : "",
    onClick: () => setMediaSub("graphics")
  }, "\uD83C\uDFA8 \u0413\u0440\u0430\u0444\u0438\u043A\u0430")), mediaSub === "articles" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      fontSize: "var(--font-md)"
    }
  }, "\u041A\u0430\u0440\u0442\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, MAPS.length)), /*#__PURE__*/React.createElement("div", {
    className: "va-mini"
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-mini__icon"
  }, "\uD83D\uDDFA"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-mini__title"
  }, "\u041A\u0430\u0440\u0442\u0430 \u043F\u0440\u043E\u0445\u043E\u0436\u0434\u0435\u043D\u0438\u044F"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__sub"
  }, "by ", MAPS[0].author)), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--outlined"
  }, "\u041E\u0442\u043A\u0440\u044B\u0442\u044C")), /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      fontSize: "var(--font-md)",
      marginTop: 16
    }
  }, "\u0423\u043F\u043E\u043C\u0438\u043D\u0430\u043D\u0438\u044F ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, MENTIONS.length)), MENTIONS.map((m, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    className: "va-mini"
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-mini__icon"
  }, "\uD83D\uDCF0"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-mini__title"
  }, m.mag, " #", String(m.issue).padStart(2, "0"), " (", m.year, ") \xB7 ", m.section), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__sub"
  }, m.body))))), mediaSub === "covers" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fill, minmax(180px, 1fr))",
      gap: 12
    }
  }, [0, 1, 2, 3, 4, 5].map(i => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      aspectRatio: "3/4",
      border: "1px solid var(--secondary-200)",
      borderRadius: "var(--radius-sm)",
      background: "var(--background-deep)",
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      color: "var(--text-light-color)",
      fontSize: "var(--font-xs)"
    }
  }, "\uD83D\uDCFC \u043E\u0431\u043B\u043E\u0436\u043A\u0430 ", i + 1)))), mediaSub === "music" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, PROD_TUNES.map(t => /*#__PURE__*/React.createElement("div", {
    key: t.id,
    style: {
      display: "flex",
      alignItems: "center",
      gap: 12,
      padding: "8px 4px",
      borderBottom: "1px solid var(--secondary-200)"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-mono)",
      color: "var(--text-light-color)",
      width: 24,
      fontSize: "var(--font-sm)"
    }
  }, t.idx), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--secondary zx-button--round"
  }, "\u25B6"), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 700,
      fontSize: "var(--font-sm)"
    }
  }, t.title), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, t.author, " \xB7 ", t.chip, " \xB7 ", t.year)), /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-mono)",
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, t.duration), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)",
      width: 60,
      textAlign: "right"
    }
  }, "\u25B6 ", t.plays), /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--warning-500)"
    }
  }, "★".repeat(t.stars))))), mediaSub === "graphics" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fill, minmax(160px, 1fr))",
      gap: 8
    }
  }, SCREENS.slice(0, 12).map(s => /*#__PURE__*/React.createElement("div", {
    key: s.id,
    style: {
      aspectRatio: "4/3",
      borderRadius: "var(--radius-sm)",
      overflow: "hidden",
      background: "var(--background-deep)"
    }
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: s.id,
    palette: s.palette
  })))))), tab === "links" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "vb-toggle",
    style: {
      marginBottom: 12
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: linksSub === "series" ? "active" : "",
    onClick: () => setLinksSub("series")
  }, "\uD83D\uDD17 \u0421\u0435\u0440\u0438\u044F Dizzy ", /*#__PURE__*/React.createElement("span", {
    style: {
      opacity: 0.6
    }
  }, SAME_SERIES.length)), /*#__PURE__*/React.createElement("button", {
    className: linksSub === "compilations" ? "active" : "",
    onClick: () => setLinksSub("compilations")
  }, "\uD83D\uDCE6 \u0412 \u0441\u0431\u043E\u0440\u043D\u0438\u043A\u0430\u0445 ", /*#__PURE__*/React.createElement("span", {
    style: {
      opacity: 0.6
    }
  }, COMPILATIONS.length))), linksSub === "series" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)",
      marginBottom: 10,
      fontStyle: "italic"
    }
  }, "\u0412\u0441\u0435 \u043F\u0440\u043E\u0433\u0440\u0430\u043C\u043C\u044B \u0441\u0435\u0440\u0438\u0438 \xABDizzy\xBB"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fill, minmax(220px, 1fr))",
      gap: 10
    }
  }, SAME_SERIES.map((s, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      padding: 10,
      border: s.title === PROD.title ? "2px solid var(--primary-500)" : "1px solid var(--secondary-200)",
      borderRadius: "var(--radius-md)",
      background: s.title === PROD.title ? "var(--primary-50)" : "var(--surface)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      aspectRatio: "4/3",
      background: "var(--background-deep)",
      borderRadius: "var(--radius-sm)",
      overflow: "hidden",
      marginBottom: 8
    }
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: i * 1000 + 7,
    palette: ["sunset", "cool", "forest", "night", "default"][i % 5]
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 700,
      fontSize: "var(--font-sm)"
    }
  }, s.title), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, s.by, " \xB7 ", s.year || "—"))))), linksSub === "compilations" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fill, minmax(280px, 1fr))",
      gap: 10
    }
  }, COMPILATIONS.map((c, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      padding: 12,
      border: "1px solid var(--secondary-200)",
      borderRadius: "var(--radius-md)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 700
    }
  }, c.title), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)",
      marginTop: 2
    }
  }, c.by || "—", c.year ? " · " + c.year : "", c.count ? " · " + c.count + " программ" : ""), c.format && /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 8
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-rel-card__chip"
  }, c.format))))))), tab === "discussion" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: 12
    }
  }, /*#__PURE__*/React.createElement("textarea", {
    className: "zx-input",
    style: {
      height: 80,
      width: "100%",
      padding: 10
    },
    placeholder: "\u041E\u0441\u0442\u0430\u0432\u0438\u0442\u044C \u043E\u0442\u0437\u044B\u0432 \u0438\u043B\u0438 \u043A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0439\u2026"
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      justifyContent: "flex-end",
      marginTop: 8
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--secondary"
  }, "\u041E\u0442\u043F\u0440\u0430\u0432\u0438\u0442\u044C"))), /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      fontSize: "var(--font-md)"
    }
  }, "\u0418\u0441\u0442\u043E\u0440\u0438\u044F \u0433\u043E\u043B\u043E\u0441\u043E\u0432 ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, VOTES.length)), VOTES.map((v, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      padding: "8px 0",
      borderBottom: "1px dashed var(--secondary-200)",
      display: "flex",
      gap: 12,
      fontSize: "var(--font-sm)"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-mono)",
      color: "var(--text-light-color)",
      fontSize: "var(--font-xs)",
      width: 60
    }
  }, v.year), /*#__PURE__*/React.createElement("span", {
    style: {
      flex: 1
    }
  }, /*#__PURE__*/React.createElement("b", null, v.user), " \u0433\u043E\u043B\u043E\u0441\u043E\u0432\u0430\u043B \u0437\u0430 ", /*#__PURE__*/React.createElement("i", null, v.target)), /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--warning-500)"
    }
  }, v.score ? "★".repeat(v.score) : "—")))));
}
window.ProdPage = ProdPage;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/ProdPage.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/ProdPageMobile.jsx
try { (() => {
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
const {
  useState: useStateM
} = React;
function ProdPageMobile() {
  const [tab, setTab] = useStateM("releases");
  const [mediaSub, setMediaSub] = useStateM("articles");
  const [linksSub, setLinksSub] = useStateM("series");
  const [filterLang, setFilterLang] = useStateM("all");
  const [filterType, setFilterType] = useStateM("all");
  const [sortBy, setSortBy] = useStateM("votes");
  const [sortDir, setSortDir] = useStateM("desc");
  const filtered = RELEASES.filter(r => (filterLang === "all" || r.lang === filterLang) && (filterType === "all" || r.type === filterType));
  const sorted = [...filtered].sort((a, b) => {
    const va = a[sortBy] ?? 0,
      vb = b[sortBy] ?? 0;
    return sortDir === "desc" ? vb - va : va - vb;
  });
  return /*#__PURE__*/React.createElement("div", {
    className: "mob",
    style: {
      padding: "12px 12px 24px",
      maxWidth: 480,
      margin: "0 auto"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: 10,
      color: "var(--text-light-color)",
      marginBottom: 10,
      fontFamily: "var(--font-mono)",
      overflow: "hidden",
      textOverflow: "ellipsis",
      whiteSpace: "nowrap"
    }
  }, "\u0413\u043B\u0430\u0432\u043D\u0430\u044F / \u0421\u043E\u0444\u0442 / \u0418\u0433\u0440\u044B / \u2026 / ", /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--text-color)"
    }
  }, "Crystal Kingdom Dizzy")), /*#__PURE__*/React.createElement("div", {
    className: "mob-hero-block"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-hero__cover mob-hero__cover"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: 42,
    palette: "forest"
  }), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__shots"
  }, "\uD83D\uDCF7 ", SCREENS.length, " \u0441\u043A\u0440\u0438\u043D\u043E\u0432")), /*#__PURE__*/React.createElement("div", {
    className: "mob-hero__info"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-hero__title-row"
  }, /*#__PURE__*/React.createElement("h1", {
    className: "va-hero__title"
  }, PROD.title), /*#__PURE__*/React.createElement("span", {
    className: "va-hero__year"
  }, "\xB7 ", PROD.year)), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__alias"
  }, "\u0442\u0430\u043A\u0436\u0435 \u0438\u0437\u0432\u0435\u0441\u0442\u043D\u0430 \u043A\u0430\u043A ", /*#__PURE__*/React.createElement("i", null, PROD.alsoKnownAs)), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__chips mob-chips-scroll"
  }, PROD.category.map(c => /*#__PURE__*/React.createElement("span", {
    key: c,
    className: "chip chip--cat"
  }, c)), /*#__PURE__*/React.createElement("span", {
    className: "chip"
  }, "\uD83C\uDDEC\uD83C\uDDE7 English"), /*#__PURE__*/React.createElement("span", {
    className: "chip",
    title: PROD.status
  }, "\u26A0 \u0420\u0430\u0441\u043F\u0440\u043E\u0441\u0442\u0440\u0430\u043D\u0435\u043D\u0438\u0435 \u0437\u0430\u043F\u0440\u0435\u0449\u0435\u043D\u043E")), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__rating-row"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-hero__rating"
  }, /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, PROD.rating.score), /*#__PURE__*/React.createElement("span", {
    className: "of"
  }, "/ ", PROD.rating.ofFive)), /*#__PURE__*/React.createElement(VoteWidget, {
    myVote: 4,
    fav: false
  }), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 11,
      color: "var(--text-light-color)"
    }
  }, PROD.rating.votes, " \u0433\u043E\u043B\u043E\u0441\u043E\u0432 \xB7 \u0432 \u0438\u0437\u0431\u0440\u0430\u043D\u043D\u043E\u043C \u0443 18")), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__people"
  }, /*#__PURE__*/React.createElement("b", null, "\u0410\u0432\u0442\u043E\u0440\u044B:"), " ", PROD.authors.join(", "), " \xB7 ", /*#__PURE__*/React.createElement("b", null, "\u041C\u0443\u0437\u044B\u043A\u0430:"), " ", PROD.music, " \xB7 ", /*#__PURE__*/React.createElement("b", null, "\u0418\u0437\u0434\u0430\u0442\u0435\u043B\u044C:"), " ", PROD.publisher, " \xB7 ", /*#__PURE__*/React.createElement("b", null, "\u0420\u0430\u0437\u0440\u0430\u0431\u043E\u0442\u0447\u0438\u043A:"), " ", PROD.developer, " \xB7 ", /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--text-light-color)"
    }
  }, "\u0414\u043E\u0431\u0430\u0432\u043B\u0435\u043D\u0430 ", PROD.added)), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 10,
      fontSize: 11,
      display: "flex",
      gap: 10,
      flexWrap: "wrap"
    }
  }, PROD.links.map((l, i) => /*#__PURE__*/React.createElement("a", {
    key: i,
    href: "#",
    style: {
      color: "var(--primary-600)",
      textDecoration: "none"
    }
  }, "\u2197 ", l.label))), /*#__PURE__*/React.createElement("div", {
    className: "mob-hero__cta"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--secondary",
    style: {
      flex: 1
    }
  }, "\u25B6 \u0418\u0433\u0440\u0430\u0442\u044C \u043E\u043D\u043B\u0430\u0439\u043D"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined",
    style: {
      flex: 1
    }
  }, "\u2B07 \u0421\u043A\u0430\u0447\u0430\u0442\u044C")))), /*#__PURE__*/React.createElement("div", {
    className: "pp-card",
    style: {
      marginBottom: 12,
      padding: 10
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      marginBottom: 8,
      fontSize: "var(--font-md)"
    }
  }, "\u0421\u043A\u0440\u0438\u043D\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, SCREENS.length)), /*#__PURE__*/React.createElement("div", {
    className: "va-screens mob-screens-grid"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-screens__cell va-screens__cell--big"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: SCREENS[0].id,
    palette: SCREENS[0].palette
  })), SCREENS.slice(1, 5).map(s => /*#__PURE__*/React.createElement("div", {
    key: s.id,
    className: "va-screens__cell"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: s.id,
    palette: s.palette
  }))), /*#__PURE__*/React.createElement("a", {
    href: "#",
    className: "va-screens__cell va-screens__more",
    style: {
      fontSize: 11,
      textAlign: "center",
      padding: 4,
      lineHeight: 1.2
    }
  }, "\u0435\u0449\u0451", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 10,
      fontWeight: 400,
      opacity: 0.7
    }
  }, "+", SCREENS.length - 5)))), /*#__PURE__*/React.createElement("div", {
    className: "pp-card",
    style: {
      marginBottom: 12
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      fontSize: "var(--font-md)"
    }
  }, "\u041E \u043F\u0440\u043E\u0433\u0440\u0430\u043C\u043C\u0435"), /*#__PURE__*/React.createElement("p", {
    style: {
      margin: 0,
      lineHeight: 1.55,
      fontSize: "var(--font-sm)"
    }
  }, PROD.story), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 12,
      paddingTop: 10,
      borderTop: "1px dashed var(--secondary-200)",
      display: "flex",
      gap: 4,
      flexWrap: "wrap",
      alignItems: "center"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 10,
      color: "var(--text-light-color)",
      textTransform: "uppercase",
      letterSpacing: "0.04em",
      marginRight: 4
    }
  }, "\u0422\u0435\u0433\u0438:"), PROD.tags.map(t => /*#__PURE__*/React.createElement("a", {
    key: t,
    href: "#",
    style: {
      fontSize: 11,
      padding: "2px 7px",
      background: "var(--secondary-100)",
      border: "1px solid var(--secondary-200)",
      borderRadius: 999,
      color: "var(--text-light-color)",
      textDecoration: "none"
    }
  }, t)))), /*#__PURE__*/React.createElement("div", {
    className: "va-tabs mob-tabs-scroll"
  }, /*#__PURE__*/React.createElement("button", {
    className: tab === "releases" ? "active" : "",
    onClick: () => setTab("releases")
  }, "\u0420\u0435\u043B\u0438\u0437\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, RELEASES.length)), /*#__PURE__*/React.createElement("button", {
    className: tab === "media" ? "active" : "",
    onClick: () => setTab("media")
  }, "\u041C\u0435\u0434\u0438\u0430 ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, MENTIONS.length + MAPS.length + PROD_TUNES.length)), /*#__PURE__*/React.createElement("button", {
    className: tab === "links" ? "active" : "",
    onClick: () => setTab("links")
  }, "\u0421\u0432\u044F\u0437\u0438 ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, COMPILATIONS.length + SAME_SERIES.length)), /*#__PURE__*/React.createElement("button", {
    className: tab === "discussion" ? "active" : "",
    onClick: () => setTab("discussion")
  }, "\u041E\u0431\u0441\u0443\u0436\u0434\u0435\u043D\u0438\u0435 ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, VOTES.length))), tab === "releases" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar mob-filter-bar"
  }, /*#__PURE__*/React.createElement("span", {
    className: "vb-filter-bar__label"
  }, "\u042F\u0437\u044B\u043A:"), /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar__group"
  }, [["all", "все"], ["en", "🇬🇧 EN"], ["ru", "🇷🇺 RU"]].map(([k, l]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    onClick: () => setFilterLang(k),
    className: "zx-button zx-button--sm " + (filterLang === k ? "zx-button--secondary" : "zx-button--outlined")
  }, l))), /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar__sep"
  }), /*#__PURE__*/React.createElement("span", {
    className: "vb-filter-bar__label"
  }, "\u0422\u0438\u043F:"), /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar__group"
  }, [["all", "все"], ...Object.entries(RELEASE_TYPES).map(([k, v]) => [k, v.label])].map(([k, l]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    onClick: () => setFilterType(k),
    className: "zx-button zx-button--sm " + (filterType === k ? "zx-button--secondary" : "zx-button--outlined")
  }, l)))), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: 11,
      color: "var(--text-light-color)",
      margin: "4px 0 8px",
      fontStyle: "italic"
    }
  }, "\u041B\u0443\u0447\u0448\u0438\u0439 \u0440\u0435\u043B\u0438\u0437 \u2014 \u0442\u043E\u0442, \u0443 \u043A\u043E\u0442\u043E\u0440\u043E\u0433\u043E \u0432\u044B\u0448\u0435 \u0440\u0435\u0439\u0442\u0438\u043D\u0433 \u0441\u043E\u043E\u0431\u0449\u0435\u0441\u0442\u0432\u0430."), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 8
    }
  }, sorted.map(r => /*#__PURE__*/React.createElement("div", {
    key: r.id,
    className: "va-rel-card"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__cover"
  }, r.screens.length ? /*#__PURE__*/React.createElement(ZxScreen, {
    seed: r.id * 13,
    palette: ["sunset", "cool", "forest", "night", "default"][r.id % 5]
  }) : null), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      minWidth: 0
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__title"
  }, r.title), /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__meta"
  }, r.releasedBy || "—", r.year ? " · " + r.year : ""), /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__chips"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rel-type-pill rel-type-pill--" + r.type
  }, RELEASE_TYPES[r.type].label), /*#__PURE__*/React.createElement("span", {
    className: "va-rel-card__chip va-rel-card__chip--lang"
  }, r.lang === "ru" ? "🇷🇺" : "🇬🇧"), r.format && /*#__PURE__*/React.createElement("span", {
    className: "va-rel-card__chip"
  }, r.format), r.note && /*#__PURE__*/React.createElement("span", {
    className: "va-rel-card__chip va-rel-card__chip--cheats"
  }, "\u2605 ", r.note)), /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__bottom"
  }, r.playOnline && /*#__PURE__*/React.createElement("a", {
    className: "play-link",
    href: "#"
  }, "\u25B6 \u0418\u0433\u0440\u0430\u0442\u044C"), /*#__PURE__*/React.createElement("span", null, "\u2B07", r.downloads), /*#__PURE__*/React.createElement("span", null, "\xB7 \u25B6", r.plays), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "auto",
      color: "var(--warning-500)"
    }
  }, "\u2605", r.votes))))))), tab === "media" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "vb-toggle mob-tabs-scroll",
    style: {
      marginBottom: 10,
      marginLeft: 0
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: mediaSub === "articles" ? "active" : "",
    onClick: () => setMediaSub("articles")
  }, "\uD83D\uDCF0 \u0421\u0442\u0430\u0442\u044C\u0438 \u0438 \u043A\u0430\u0440\u0442\u044B ", /*#__PURE__*/React.createElement("span", {
    style: {
      opacity: 0.6
    }
  }, MENTIONS.length + MAPS.length)), /*#__PURE__*/React.createElement("button", {
    className: mediaSub === "covers" ? "active" : "",
    onClick: () => setMediaSub("covers")
  }, "\uD83D\uDCFC \u041E\u0431\u043B\u043E\u0436\u043A\u0438"), /*#__PURE__*/React.createElement("button", {
    className: mediaSub === "music" ? "active" : "",
    onClick: () => setMediaSub("music")
  }, "\uD83C\uDFB5 \u041C\u0443\u0437\u044B\u043A\u0430 ", /*#__PURE__*/React.createElement("span", {
    style: {
      opacity: 0.6
    }
  }, PROD_TUNES.length)), /*#__PURE__*/React.createElement("button", {
    className: mediaSub === "graphics" ? "active" : "",
    onClick: () => setMediaSub("graphics")
  }, "\uD83C\uDFA8 \u0413\u0440\u0430\u0444\u0438\u043A\u0430")), mediaSub === "articles" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      fontSize: "var(--font-md)"
    }
  }, "\u041A\u0430\u0440\u0442\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, MAPS.length)), /*#__PURE__*/React.createElement("div", {
    className: "va-mini"
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-mini__icon"
  }, "\uD83D\uDDFA"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-mini__title"
  }, "\u041A\u0430\u0440\u0442\u0430 \u043F\u0440\u043E\u0445\u043E\u0436\u0434\u0435\u043D\u0438\u044F"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__sub"
  }, "by ", MAPS[0].author)), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--outlined"
  }, "\u041E\u0442\u043A\u0440\u044B\u0442\u044C")), /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      fontSize: "var(--font-md)",
      marginTop: 14
    }
  }, "\u0423\u043F\u043E\u043C\u0438\u043D\u0430\u043D\u0438\u044F ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, MENTIONS.length)), MENTIONS.map((m, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    className: "va-mini"
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-mini__icon"
  }, "\uD83D\uDCF0"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-mini__title"
  }, m.mag, " #", String(m.issue).padStart(2, "0"), " (", m.year, ") \xB7 ", m.section), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__sub"
  }, m.body))))), mediaSub === "covers" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "1fr 1fr",
      gap: 8
    }
  }, [0, 1, 2, 3, 4, 5].map(i => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      aspectRatio: "3/4",
      border: "1px solid var(--secondary-200)",
      borderRadius: "var(--radius-sm)",
      background: "var(--background-deep)",
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      color: "var(--text-light-color)",
      fontSize: 11
    }
  }, "\uD83D\uDCFC \u043E\u0431\u043B\u043E\u0436\u043A\u0430 ", i + 1)))), mediaSub === "music" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card",
    style: {
      padding: 8
    }
  }, PROD_TUNES.map(t => /*#__PURE__*/React.createElement("div", {
    key: t.id,
    style: {
      display: "flex",
      alignItems: "center",
      gap: 8,
      padding: "8px 4px",
      borderBottom: "1px solid var(--secondary-200)"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-mono)",
      color: "var(--text-light-color)",
      width: 18,
      fontSize: 11
    }
  }, t.idx), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--secondary zx-button--round",
    style: {
      flexShrink: 0
    }
  }, "\u25B6"), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      minWidth: 0
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 700,
      fontSize: "var(--font-sm)",
      whiteSpace: "nowrap",
      overflow: "hidden",
      textOverflow: "ellipsis"
    }
  }, t.title), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: 10,
      color: "var(--text-light-color)"
    }
  }, t.author, " \xB7 ", t.chip, " \xB7 ", t.duration)), /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--warning-500)",
      fontSize: 11
    }
  }, "★".repeat(t.stars))))), mediaSub === "graphics" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "1fr 1fr 1fr",
      gap: 6
    }
  }, SCREENS.slice(0, 12).map(s => /*#__PURE__*/React.createElement("div", {
    key: s.id,
    style: {
      aspectRatio: "4/3",
      borderRadius: "var(--radius-sm)",
      overflow: "hidden",
      background: "var(--background-deep)"
    }
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: s.id,
    palette: s.palette
  })))))), tab === "links" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "vb-toggle mob-tabs-scroll",
    style: {
      marginBottom: 10,
      marginLeft: 0
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: linksSub === "series" ? "active" : "",
    onClick: () => setLinksSub("series")
  }, "\uD83D\uDD17 \u0421\u0435\u0440\u0438\u044F Dizzy ", /*#__PURE__*/React.createElement("span", {
    style: {
      opacity: 0.6
    }
  }, SAME_SERIES.length)), /*#__PURE__*/React.createElement("button", {
    className: linksSub === "compilations" ? "active" : "",
    onClick: () => setLinksSub("compilations")
  }, "\uD83D\uDCE6 \u0412 \u0441\u0431\u043E\u0440\u043D\u0438\u043A\u0430\u0445 ", /*#__PURE__*/React.createElement("span", {
    style: {
      opacity: 0.6
    }
  }, COMPILATIONS.length))), linksSub === "series" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: 11,
      color: "var(--text-light-color)",
      marginBottom: 10,
      fontStyle: "italic"
    }
  }, "\u0412\u0441\u0435 \u043F\u0440\u043E\u0433\u0440\u0430\u043C\u043C\u044B \u0441\u0435\u0440\u0438\u0438 \xABDizzy\xBB"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "1fr 1fr",
      gap: 8
    }
  }, SAME_SERIES.map((s, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      padding: 8,
      border: s.title === PROD.title ? "2px solid var(--primary-500)" : "1px solid var(--secondary-200)",
      borderRadius: "var(--radius-md)",
      background: s.title === PROD.title ? "var(--primary-50)" : "var(--surface)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      aspectRatio: "4/3",
      background: "var(--background-deep)",
      borderRadius: "var(--radius-sm)",
      overflow: "hidden",
      marginBottom: 6
    }
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: i * 1000 + 7,
    palette: ["sunset", "cool", "forest", "night", "default"][i % 5]
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 700,
      fontSize: 12,
      lineHeight: 1.25
    }
  }, s.title), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: 10,
      color: "var(--text-light-color)",
      fontFamily: "var(--font-mono)",
      marginTop: 2
    }
  }, s.year || "—"))))), linksSub === "compilations" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 8
    }
  }, COMPILATIONS.map((c, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      padding: 10,
      border: "1px solid var(--secondary-200)",
      borderRadius: "var(--radius-md)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 700,
      fontSize: "var(--font-sm)"
    }
  }, c.title), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: 11,
      color: "var(--text-light-color)",
      marginTop: 2
    }
  }, c.by || "—", c.year ? " · " + c.year : "", c.count ? " · " + c.count + " программ" : ""), c.format && /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 6
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-rel-card__chip"
  }, c.format))))))), tab === "discussion" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: 12
    }
  }, /*#__PURE__*/React.createElement("textarea", {
    className: "zx-input",
    style: {
      height: 72,
      width: "100%",
      padding: 10,
      boxSizing: "border-box",
      fontSize: "var(--font-sm)"
    },
    placeholder: "\u041E\u0441\u0442\u0430\u0432\u0438\u0442\u044C \u043E\u0442\u0437\u044B\u0432 \u0438\u043B\u0438 \u043A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0439\u2026"
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      justifyContent: "flex-end",
      marginTop: 8
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--secondary"
  }, "\u041E\u0442\u043F\u0440\u0430\u0432\u0438\u0442\u044C"))), /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      fontSize: "var(--font-md)"
    }
  }, "\u0418\u0441\u0442\u043E\u0440\u0438\u044F \u0433\u043E\u043B\u043E\u0441\u043E\u0432 ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, VOTES.length)), VOTES.map((v, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      padding: "8px 0",
      borderBottom: "1px dashed var(--secondary-200)",
      fontSize: "var(--font-sm)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 8,
      alignItems: "baseline"
    }
  }, /*#__PURE__*/React.createElement("b", null, v.user), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "auto",
      color: "var(--warning-500)"
    }
  }, v.score ? "★".repeat(v.score) : "—")), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: 10,
      color: "var(--text-light-color)",
      fontFamily: "var(--font-mono)"
    }
  }, v.year, " \xB7 ", v.target)))));
}
window.ProdPageMobile = ProdPageMobile;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/ProdPageMobile.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/ReleasePage.jsx
try { (() => {
/* ReleasePage.jsx — v2.
   • Inline prod-style meta strings (no dl sidebar).
   • Reworded parent anchor — short "К программе" arrow.
   • File-tree section, always present.
   • Instructions open in a modal preview.
   • Graceful minimum-case rendering (no description / no screens / no
     covers / no instructions). */

const {
  useState
} = React;
function I({
  name,
  size = 16
}) {
  const p = {
    play: "M8 5v14l11-7z",
    fullscreen: "M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z",
    download: "M5 20h14v-2H5v2zm7-18l-5.5 5.5h3.5V14h4V7.5h3.5L12 2z",
    warn: "M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z",
    eye: "M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5C21.3 7.6 17 4.5 12 4.5zm0 12.5a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z",
    close: "M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z",
    folder: "M10 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-8l-2-2z",
    file: "M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z",
    zip: "M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-3 17h-2v-2h2v2zm0-4h-2v-2h2v2zm0-4h-2V9h2v2zm0-4h-2V5h2v2z",
    plus: "M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6z"
  };
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: "0 0 24 24",
    width: size,
    height: size,
    fill: "currentColor",
    "aria-hidden": "true",
    style: {
      flexShrink: 0
    }
  }, /*#__PURE__*/React.createElement("path", {
    d: p[name]
  }));
}
function CassetteCover({
  cover
}) {
  const palettes = {
    sunset: {
      top: "#3d0000",
      bot: "#1a0000",
      band: "#bb0000"
    },
    cool: {
      top: "#001d38",
      bot: "#000f1f",
      band: "#1a90ff"
    },
    forest: {
      top: "#0d3b66",
      bot: "#000",
      band: "#2d8659"
    },
    night: {
      top: "#262626",
      bot: "#000",
      band: "#404040"
    }
  };
  const p = palettes[cover.palette] || palettes.cool;
  const isLabel = cover.kind === "label";
  return /*#__PURE__*/React.createElement("div", {
    className: "rp-cassette" + (isLabel ? " rp-cassette--label" : ""),
    style: isLabel ? {} : {
      "--cassette-bg-top": p.top,
      "--cassette-bg-bot": p.bot,
      "--cassette-band": p.band
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "rp-cassette__band"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rp-cassette__title"
  }, "CRYSTAL KINGDOM DIZZY")), /*#__PURE__*/React.createElement("div", {
    className: "rp-cassette__art"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: cover.id * 71 + 9,
    palette: cover.palette
  })), /*#__PURE__*/React.createElement("div", {
    className: "rp-cassette__foot"
  }, /*#__PURE__*/React.createElement("span", null, "SCORPION SOFT"), /*#__PURE__*/React.createElement("span", null, isLabel ? "SCL · 1995" : "TR-DOS · 1995")));
}
function MediaTile({
  kind,
  label,
  size,
  children
}) {
  return /*#__PURE__*/React.createElement("a", {
    className: "rp-tile rp-tile--" + kind,
    href: "#",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("div", {
    className: "rp-tile__img"
  }, children), /*#__PURE__*/React.createElement("div", {
    className: "rp-tile__meta"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rp-tile__label"
  }, label), /*#__PURE__*/React.createElement("span", {
    className: "rp-tile__size"
  }, size)), /*#__PURE__*/React.createElement("div", {
    className: "rp-tile__hover"
  }, /*#__PURE__*/React.createElement("button", {
    title: "\u041E\u0442\u043A\u0440\u044B\u0442\u044C \u0432 \u043F\u0440\u043E\u0441\u043C\u043E\u0442\u0440\u0449\u0438\u043A\u0435"
  }, /*#__PURE__*/React.createElement(I, {
    name: "eye",
    size: 12
  }), "\u041E\u0442\u043A\u0440\u044B\u0442\u044C"), /*#__PURE__*/React.createElement("button", {
    title: "\u0421\u043A\u0430\u0447\u0430\u0442\u044C \u043E\u0440\u0438\u0433\u0438\u043D\u0430\u043B"
  }, /*#__PURE__*/React.createElement(I, {
    name: "download",
    size: 12
  }))));
}

/* ── File tree row ── */
function fmtBytes(n) {
  if (n == null) return "—";
  if (n < 1024) return n.toLocaleString("ru-RU");
  if (n < 1024 * 1024) return (n / 1024).toFixed(1).replace(".0", "") + " КБ";
  return (n / (1024 * 1024)).toFixed(2).replace(/\.?0+$/, "") + " МБ";
}
function FileTreeRow({
  row,
  onPreview
}) {
  const indents = Array.from({
    length: row.d
  });
  const isFolder = row.kind === "folder";
  const isZip = row.kind === "zip";
  const kindLabel = isZip ? "ZIP архив" : isFolder ? "Папка" : row.ext || "Файл";
  const icon = isZip ? "zip" : isFolder ? "folder" : "file";
  return /*#__PURE__*/React.createElement("div", {
    className: "rp-tree__row"
  }, /*#__PURE__*/React.createElement("div", {
    className: "rp-tree__name"
  }, indents.map((_, i) => /*#__PURE__*/React.createElement("span", {
    key: i,
    className: "rp-tree__indent-rule"
  })), /*#__PURE__*/React.createElement("span", {
    className: "rp-tree__icon"
  }, /*#__PURE__*/React.createElement(I, {
    name: icon
  })), row.kind === "file" ? /*#__PURE__*/React.createElement("span", {
    className: "rp-tree__name--file"
  }, row.viewable ? /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => {
      e.preventDefault();
      onPreview && onPreview(row);
    }
  }, row.name) : /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, row.name)) : /*#__PURE__*/React.createElement("span", {
    className: isZip ? "rp-tree__name--zip" : "rp-tree__name--folder"
  }, row.name)), /*#__PURE__*/React.createElement("div", {
    className: "rp-tree__size"
  }, fmtBytes(row.size)), /*#__PURE__*/React.createElement("div", {
    className: "rp-tree__type"
  }, kindLabel), /*#__PURE__*/React.createElement("div", {
    className: "rp-tree__actions"
  }, (row.kind === "file" || row.kind === "zip") && /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement(I, {
    name: "download",
    size: 14
  }), "\u0421\u043A\u0430\u0447\u0430\u0442\u044C"), row.kind === "file" && row.viewable && /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => {
      e.preventDefault();
      onPreview && onPreview(row);
    }
  }, /*#__PURE__*/React.createElement(I, {
    name: "eye",
    size: 14
  }), "\u041F\u0440\u043E\u0441\u043C\u043E\u0442\u0440\u0435\u0442\u044C")));
}

/* ── Instruction file row (opens modal) ── */
function InstructionRow({
  file,
  onPreview
}) {
  const ext = file.file.split(".").pop().toUpperCase();
  return /*#__PURE__*/React.createElement("div", {
    className: "rp-file",
    onClick: () => onPreview && onPreview(file)
  }, /*#__PURE__*/React.createElement("div", {
    className: "rp-file__icon"
  }, ext), /*#__PURE__*/React.createElement("div", {
    className: "rp-file__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "rp-file__title"
  }, file.title), /*#__PURE__*/React.createElement("div", {
    className: "rp-file__meta"
  }, file.file, " \xB7 ", file.size)), /*#__PURE__*/React.createElement("span", {
    className: "rp-file__lang"
  }, file.lang), /*#__PURE__*/React.createElement("div", {
    className: "rp-file__act"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => {
      e.preventDefault();
      onPreview && onPreview(file);
    }
  }, /*#__PURE__*/React.createElement(I, {
    name: "eye",
    size: 14
  }), "\u041F\u0440\u043E\u0441\u043C\u043E\u0442\u0440\u0435\u0442\u044C"), /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement(I, {
    name: "download",
    size: 14
  }), "\u0421\u043A\u0430\u0447\u0430\u0442\u044C")));
}

/* ── Instruction preview modal ── */
function InstructionModal({
  file,
  onClose
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "rp-modal-backdrop",
    onClick: onClose
  }, /*#__PURE__*/React.createElement("div", {
    className: "rp-modal",
    onClick: e => e.stopPropagation()
  }, /*#__PURE__*/React.createElement("div", {
    className: "rp-modal__head"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rp-modal__title"
  }, file.name || file.file), /*#__PURE__*/React.createElement("span", {
    className: "rp-modal__meta"
  }, file.size ? typeof file.size === "string" ? file.size : fmtBytes(file.size) : ""), /*#__PURE__*/React.createElement("button", {
    className: "rp-modal__close zx-button zx-button--transparent zx-button--sm zx-button--square",
    onClick: onClose,
    title: "\u0417\u0430\u043A\u0440\u044B\u0442\u044C"
  }, /*#__PURE__*/React.createElement(I, {
    name: "close"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "rp-modal__body"
  }, /*#__PURE__*/React.createElement("pre", {
    className: "rp-modal__pre"
  }, file.body || README_RU)), /*#__PURE__*/React.createElement("div", {
    className: "rp-modal__foot"
  }, /*#__PURE__*/React.createElement("span", {
    className: "left"
  }, "\u0422\u0435\u043A\u0441\u0442 \xB7 UTF-8 \xB7 \u043C\u043E\u043D\u043E\u0448\u0438\u0440\u0438\u043D\u043D\u044B\u0439 \u0448\u0440\u0438\u0444\u0442"), /*#__PURE__*/React.createElement("div", {
    className: "zx-button-controls zx-button-controls--align-end"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined zx-button--sm",
    type: "button"
  }, /*#__PURE__*/React.createElement(I, {
    name: "download",
    size: 14
  }), "\u0421\u043A\u0430\u0447\u0430\u0442\u044C"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--primary zx-button--sm",
    type: "button",
    onClick: onClose
  }, "\u0417\u0430\u043A\u0440\u044B\u0442\u044C")))));
}
function ReleasePage({
  minimal = false,
  showInstructionModal = false
}) {
  const t = REL_TYPES[RELEASE.type];
  const hasDescription = !minimal;
  const hasScreens = !minimal && REL_SCREENS.length > 0;
  const hasCovers = !minimal && COVERS.length > 0;
  const hasInstructions = !minimal && INSTRUCTIONS.length > 0;
  const votes = minimal ? [] : REL_VOTES;
  const comments = minimal ? [] : REL_COMMENTS;
  const [previewFile, setPreviewFile] = useState(showInstructionModal ? {
    name: INSTRUCTIONS[0].file,
    size: INSTRUCTIONS[0].size,
    body: README_RU
  } : null);
  return /*#__PURE__*/React.createElement("div", {
    className: "rp-root"
  }, /*#__PURE__*/React.createElement("nav", {
    className: "zx-breadcrumbs",
    "aria-label": "breadcrumb"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0413\u043B\u0430\u0432\u043D\u0430\u044F"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0421\u043E\u0444\u0442"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0418\u0433\u0440\u044B"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, RELEASE.prod.title), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("span", {
    className: "here"
  }, "\u0420\u0435\u043B\u0438\u0437 \u2014 ", RELEASE.publishers[0].name, ", ", RELEASE.year)), /*#__PURE__*/React.createElement("a", {
    className: "rp-anchor",
    href: "#",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("span", {
    className: "rp-anchor__arrow"
  }, "\u2190"), /*#__PURE__*/React.createElement("span", {
    className: "rp-anchor__thumb"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: 42,
    palette: RELEASE.prod.cover
  })), /*#__PURE__*/React.createElement("span", {
    className: "rp-anchor__body"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rp-anchor__label"
  }, "\u043A \u043F\u0440\u043E\u0433\u0440\u0430\u043C\u043C\u0435"), /*#__PURE__*/React.createElement("span", {
    className: "rp-anchor__title"
  }, RELEASE.prod.title)), /*#__PURE__*/React.createElement("span", {
    className: "rp-anchor__meta"
  }, RELEASE.prod.year, " \xB7 ", RELEASE.prod.authors[0])), /*#__PURE__*/React.createElement("header", {
    className: "rp-head"
  }, /*#__PURE__*/React.createElement("div", {
    className: "rp-head__title-row"
  }, /*#__PURE__*/React.createElement("h1", {
    className: "rp-head__title"
  }, RELEASE.title), /*#__PURE__*/React.createElement("span", {
    className: "rp-head__year"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, RELEASE.year)), /*#__PURE__*/React.createElement("span", {
    className: "zx-release-type-badge zx-release-type-badge--" + RELEASE.type
  }, t.label), /*#__PURE__*/React.createElement("span", {
    className: "rp-status"
  }, /*#__PURE__*/React.createElement(I, {
    name: "warn",
    size: 12
  }), RELEASE.status.label)), /*#__PURE__*/React.createElement("div", {
    className: "rp-people"
  }, /*#__PURE__*/React.createElement("b", null, "\u0418\u0437\u0434\u0430\u0442\u0435\u043B\u0438:"), " ", RELEASE.publishers.map((p, i) => /*#__PURE__*/React.createElement(React.Fragment, {
    key: p.id
  }, i > 0 && ", ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, p.name), /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--text-light-color)"
    }
  }, " (", p.role.toLowerCase(), ")"))), /*#__PURE__*/React.createElement("span", {
    className: "dot"
  }, "\xB7"), /*#__PURE__*/React.createElement("b", null, "\u0424\u043E\u0440\u043C\u0430\u0442:"), " ", /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-mono)"
    }
  }, RELEASE.format), /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--secondary-400)"
    }
  }, " \xB7 ", RELEASE.size)), /*#__PURE__*/React.createElement("div", {
    className: "rp-hw"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rp-hw__label"
  }, "\u0416\u0435\u043B\u0435\u0437\u043E:"), RELEASE.hardware.map(h => /*#__PURE__*/React.createElement("a", {
    key: h.id,
    href: "#"
  }, h.name))), /*#__PURE__*/React.createElement("div", {
    className: "rp-bar"
  }, votes.length > 0 ? /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "rp-bar__rating"
  }, /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, RELEASE.votes.score), /*#__PURE__*/React.createElement("span", {
    className: "of"
  }, "/ 5"), /*#__PURE__*/React.createElement("span", {
    className: "votes"
  }, "\xB7 ", RELEASE.votes.count, " \u0433\u043E\u043B\u043E\u0441\u043E\u0432")), /*#__PURE__*/React.createElement(VoteWidget, {
    myVote: 5,
    fav: false
  })) : /*#__PURE__*/React.createElement("div", {
    className: "rp-bar__rating",
    style: {
      color: "var(--text-light-color)"
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "of"
  }, "\u0413\u043E\u043B\u043E\u0441\u043E\u0432 \u043F\u043E\u043A\u0430 \u043D\u0435\u0442."), /*#__PURE__*/React.createElement(VoteWidget, {
    myVote: 0,
    fav: false
  })), /*#__PURE__*/React.createElement("div", {
    className: "rp-bar__counters"
  }, /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", null, RELEASE.downloads), " \u0441\u043A\u0430\u0447\u0438\u0432\u0430\u043D\u0438\u0439"), /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", null, RELEASE.plays), " \u0437\u0430\u043F\u0443\u0441\u043A\u043E\u0432"), /*#__PURE__*/React.createElement("span", null, "\u0434\u043E\u0431\u0430\u0432\u043B\u0435\u043D ", /*#__PURE__*/React.createElement("b", null, RELEASE.addedAt), " ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: {
      color: "var(--primary-600)",
      textDecoration: "none"
    }
  }, RELEASE.addedBy))), /*#__PURE__*/React.createElement("div", {
    className: "rp-bar__actions zx-button-controls zx-button-controls--align-end"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--primary zx-button--md",
    type: "button"
  }, /*#__PURE__*/React.createElement(I, {
    name: "play",
    size: 18
  }), "\u0417\u0430\u043F\u0443\u0441\u0442\u0438\u0442\u044C", /*#__PURE__*/React.createElement("span", {
    className: "rp-action__hint"
  }, "\u0432 \u044D\u043C\u0443\u043B\u044F\u0442\u043E\u0440\u0435")), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined zx-button--md zx-button--square",
    type: "button",
    title: "\u041F\u043E\u043B\u043D\u043E\u044D\u043A\u0440\u0430\u043D\u043D\u044B\u0439 \u0440\u0435\u0436\u0438\u043C \u044D\u043C\u0443\u043B\u044F\u0442\u043E\u0440\u0430"
  }, /*#__PURE__*/React.createElement(I, {
    name: "fullscreen",
    size: 18
  })), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined zx-button--md",
    type: "button"
  }, /*#__PURE__*/React.createElement(I, {
    name: "download",
    size: 18
  }), RELEASE.format.split(" ")[0], /*#__PURE__*/React.createElement("span", {
    className: "rp-action__hint"
  }, RELEASE.size))))), /*#__PURE__*/React.createElement("div", {
    className: "rp-content"
  }, /*#__PURE__*/React.createElement("section", null, /*#__PURE__*/React.createElement("div", {
    className: "rp-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u041E\u043F\u0438\u0441\u0430\u043D\u0438\u0435")), hasDescription ? /*#__PURE__*/React.createElement("p", {
    className: "rp-desc"
  }, RELEASE.description) : /*#__PURE__*/React.createElement("p", {
    className: "rp-inline-empty"
  }, "\u041E\u043F\u0438\u0441\u0430\u043D\u0438\u0435 \u044D\u0442\u043E\u0433\u043E \u0440\u0435\u043B\u0438\u0437\u0430 \u043D\u0435 \u0434\u043E\u0431\u0430\u0432\u043B\u0435\u043D\u043E. ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u041F\u043E\u043C\u043E\u0447\u044C \u0430\u0440\u0445\u0438\u0432\u0443 \u2014 \u043F\u0440\u0435\u0434\u043B\u043E\u0436\u0438\u0442\u044C \u0442\u0435\u043A\u0441\u0442 \u203A"))), hasScreens && /*#__PURE__*/React.createElement("section", null, /*#__PURE__*/React.createElement("div", {
    className: "rp-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0421\u043A\u0440\u0438\u043D\u044B \u0440\u0435\u043B\u0438\u0437\u0430"), /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, REL_SCREENS.length), /*#__PURE__*/React.createElement("span", {
    className: "right"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0432\u0441\u0435 \u0441\u043A\u0440\u0438\u043D\u044B \u203A"))), /*#__PURE__*/React.createElement("div", {
    className: "rp-tiles rp-tiles--screens"
  }, REL_SCREENS.map(s => /*#__PURE__*/React.createElement(MediaTile, {
    key: s.id,
    kind: "screen",
    label: s.file,
    size: s.size
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: s.id,
    palette: s.palette
  }))))), hasCovers && /*#__PURE__*/React.createElement("section", null, /*#__PURE__*/React.createElement("div", {
    className: "rp-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u041E\u0431\u043B\u043E\u0436\u043A\u0438"), /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, COVERS.length), /*#__PURE__*/React.createElement("span", {
    className: "right"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0441\u043A\u0430\u0447\u0430\u0442\u044C \u0430\u0440\u0445\u0438\u0432\u043E\u043C \u203A"))), /*#__PURE__*/React.createElement("div", {
    className: "rp-tiles rp-tiles--covers"
  }, COVERS.map(c => /*#__PURE__*/React.createElement(MediaTile, {
    key: c.id,
    kind: "cover",
    label: c.label,
    size: c.size
  }, /*#__PURE__*/React.createElement(CassetteCover, {
    cover: c
  }))))), /*#__PURE__*/React.createElement("section", null, /*#__PURE__*/React.createElement("div", {
    className: "rp-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0421\u0442\u0440\u0443\u043A\u0442\u0443\u0440\u0430 \u0444\u0430\u0439\u043B\u043E\u0432 \u0438 \u043F\u0430\u043F\u043E\u043A"), /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, FILE_TREE.length), /*#__PURE__*/React.createElement("span", {
    className: "right"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0441\u043A\u0430\u0447\u0430\u0442\u044C \u0432\u0441\u0451 \u203A"))), /*#__PURE__*/React.createElement("div", {
    className: "rp-tree"
  }, FILE_TREE.map((row, i) => /*#__PURE__*/React.createElement(FileTreeRow, {
    key: i,
    row: row,
    onPreview: r => setPreviewFile({
      name: r.name,
      size: r.size,
      body: README_RU
    })
  })))), hasInstructions && /*#__PURE__*/React.createElement("section", null, /*#__PURE__*/React.createElement("div", {
    className: "rp-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0418\u043D\u0441\u0442\u0440\u0443\u043A\u0446\u0438\u044F"), /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, INSTRUCTIONS.length)), /*#__PURE__*/React.createElement("div", {
    className: "rp-files"
  }, INSTRUCTIONS.map((f, i) => /*#__PURE__*/React.createElement(InstructionRow, {
    key: i,
    file: f,
    onPreview: file => setPreviewFile({
      name: file.file,
      size: file.size,
      body: README_RU
    })
  })))), minimal && /*#__PURE__*/React.createElement("div", {
    className: "rp-contribute"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "rp-contribute__title"
  }, "\u041F\u043E\u043C\u043E\u0433\u0438\u0442\u0435 \u0434\u043E\u043F\u043E\u043B\u043D\u0438\u0442\u044C \u0440\u0435\u043B\u0438\u0437"), /*#__PURE__*/React.createElement("div", {
    className: "rp-contribute__hint"
  }, "\u0412 \u0430\u0440\u0445\u0438\u0432\u0435 \u043D\u0435\u0442 \u043E\u043F\u0438\u0441\u0430\u043D\u0438\u044F, \u043E\u0431\u043B\u043E\u0436\u0435\u043A, \u0441\u043A\u0440\u0438\u043D\u043E\u0432 \u0438 \u0438\u043D\u0441\u0442\u0440\u0443\u043A\u0446\u0438\u0439 \u0434\u043B\u044F \u044D\u0442\u043E\u0439 \u0432\u0435\u0440\u0441\u0438\u0438. \u0415\u0441\u043B\u0438 \u0443 \u0432\u0430\u0441 \u0441\u043E\u0445\u0440\u0430\u043D\u0438\u043B\u0430\u0441\u044C \u043A\u0430\u0441\u0441\u0435\u0442\u0430 \u0438\u043B\u0438 \u0434\u0438\u0441\u043A \u2014 \u043F\u043E\u0434\u0435\u043B\u0438\u0442\u0435\u0441\u044C.")), /*#__PURE__*/React.createElement("div", {
    className: "rp-contribute__actions zx-button-controls zx-button-controls--align-end"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined zx-button--sm",
    type: "button"
  }, /*#__PURE__*/React.createElement(I, {
    name: "plus",
    size: 14
  }), "\u041E\u043F\u0438\u0441\u0430\u043D\u0438\u0435"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined zx-button--sm",
    type: "button"
  }, /*#__PURE__*/React.createElement(I, {
    name: "plus",
    size: 14
  }), "\u041E\u0431\u043B\u043E\u0436\u043A\u0438"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined zx-button--sm",
    type: "button"
  }, /*#__PURE__*/React.createElement(I, {
    name: "plus",
    size: 14
  }), "\u0421\u043A\u0440\u0438\u043D\u044B"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--outlined zx-button--sm",
    type: "button"
  }, /*#__PURE__*/React.createElement(I, {
    name: "plus",
    size: 14
  }), "\u0418\u043D\u0441\u0442\u0440\u0443\u043A\u0446\u0438\u044E"))), /*#__PURE__*/React.createElement("section", null, /*#__PURE__*/React.createElement("div", {
    className: "rp-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0418\u0441\u0442\u043E\u0440\u0438\u044F \u0433\u043E\u043B\u043E\u0441\u043E\u0432"), /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, votes.length), votes.length > 0 && /*#__PURE__*/React.createElement("span", {
    className: "right"
  }, "\u0441\u0440\u0435\u0434\u043D\u044F\u044F ", /*#__PURE__*/React.createElement("b", {
    style: {
      color: "var(--warning-700)"
    }
  }, "\u2605 ", RELEASE.votes.score))), votes.length > 0 ? /*#__PURE__*/React.createElement("div", {
    className: "rp-list"
  }, votes.map((v, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    className: "rp-list__row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rp-list__date"
  }, v.date), /*#__PURE__*/React.createElement("span", {
    className: "rp-list__user"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: {
      color: "inherit"
    }
  }, v.user)), /*#__PURE__*/React.createElement("span", {
    className: "rp-list__score"
  }, "★".repeat(v.score), "☆".repeat(5 - v.score))))) : /*#__PURE__*/React.createElement("p", {
    className: "rp-comment--empty"
  }, "\u042D\u0442\u043E\u0442 \u0440\u0435\u043B\u0438\u0437 \u0435\u0449\u0451 \u043D\u0438\u043A\u0442\u043E \u043D\u0435 \u043E\u0446\u0435\u043D\u0438\u0432\u0430\u043B.")), /*#__PURE__*/React.createElement("section", null, /*#__PURE__*/React.createElement("div", {
    className: "rp-section__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u041A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0438"), /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, comments.length)), comments.length > 0 ? /*#__PURE__*/React.createElement("div", null, comments.map(c => /*#__PURE__*/React.createElement("div", {
    key: c.id,
    className: "rp-comment"
  }, /*#__PURE__*/React.createElement("div", {
    className: "rp-comment__head"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rp-comment__user"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: {
      color: "inherit"
    }
  }, c.user)), /*#__PURE__*/React.createElement("span", {
    className: "rp-comment__date"
  }, c.date)), /*#__PURE__*/React.createElement("div", {
    className: "rp-comment__body"
  }, c.body)))) : /*#__PURE__*/React.createElement("p", {
    className: "rp-comment--empty"
  }, "\u0411\u0443\u0434\u044C\u0442\u0435 \u043F\u0435\u0440\u0432\u044B\u043C, \u043A\u0442\u043E \u043D\u0430\u043F\u0438\u0448\u0435\u0442 \u043E \u0440\u0435\u043B\u0438\u0437\u0435."), /*#__PURE__*/React.createElement("div", {
    className: "rp-add"
  }, /*#__PURE__*/React.createElement("div", {
    className: "rp-add__h"
  }, "\u0414\u043E\u0431\u0430\u0432\u0438\u0442\u044C \u043A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0439"), /*#__PURE__*/React.createElement("textarea", {
    placeholder: "\u041F\u043E\u0434\u0435\u043B\u0438\u0442\u044C\u0441\u044F \u043D\u0430\u0431\u043B\u044E\u0434\u0435\u043D\u0438\u0435\u043C \u043E \u0440\u0435\u043B\u0438\u0437\u0435\u2026"
  }), /*#__PURE__*/React.createElement("div", {
    className: "rp-add__foot"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rp-add__hint"
  }, "Markdown \xB7 \u043A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0439 \u043F\u0440\u0438\u0432\u044F\u0437\u044B\u0432\u0430\u0435\u0442\u0441\u044F \u043A \u0440\u0435\u043B\u0438\u0437\u0443, \u043D\u0435 \u043A \u043F\u0440\u043E\u0433\u0440\u0430\u043C\u043C\u0435"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--primary zx-button--sm",
    type: "button"
  }, "\u041E\u0442\u043F\u0440\u0430\u0432\u0438\u0442\u044C"))))), previewFile && /*#__PURE__*/React.createElement(InstructionModal, {
    file: previewFile,
    onClose: () => setPreviewFile(null)
  }));
}
window.ReleasePage = ReleasePage;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/ReleasePage.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/TunePage.jsx
try { (() => {
/* TunePage.jsx — ZX Spectrum tune (chiptune) detail page for "Hibernation" by MmcM.
   Structure mirrors PicturePage: breadcrumb → hero (oscilloscope "player" + head
   meta with competition context, rating, added-by) → prominent tag band →
   meta + downloads two-column → votes + comments 50/50 → three related rails
   (author · tags · same tracker). Light mode only. The big "Прослушать в браузере"
   CTA hands playback to the existing site player; here it locally drives the
   oscilloscope + progress so the interaction reads true. */

const {
  useState,
  useRef,
  useEffect,
  useCallback
} = React;

/* local icon set (inline to avoid global scope collisions) */
function TIcon({
  name,
  size = 16
}) {
  const p = {
    play: "M8 5v14l11-7z",
    pause: "M6 5h4v14H6zm8 0h4v14h-4z",
    download: "M5 20h14v-2H5v2zm7-18l-5.5 5.5h3.5V14h4V7.5h3.5L12 2z",
    plus: "M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6z",
    chevron: "M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z",
    trophy: "M18 2H6v2H2v4a4 4 0 0 0 4 4 6 6 0 0 0 5 4.9V20H8v2h8v-2h-3v-3.1A6 6 0 0 0 18 12a4 4 0 0 0 4-4V4h-4V2zM6 10a2 2 0 0 1-2-2V6h2v4zm14-2a2 2 0 0 1-2 2V6h2v2z",
    chip: "M9 3v2H7v2H5v2H3v2h2v2H3v2h2v2h2v2h2v2h2v-2h2v2h2v-2h2v-2h2v-2h-2v-2h2V9h-2V7h-2V5h-2V3h-2v2h-2V3H9zm0 6h6v6H9V9z"
  };
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: "0 0 24 24",
    width: size,
    height: size,
    fill: "currentColor",
    "aria-hidden": "true",
    style: {
      flexShrink: 0
    }
  }, /*#__PURE__*/React.createElement("path", {
    d: p[name]
  }));
}
function Medal({
  place
}) {
  if (!place || place > 3) return null;
  const m = ["gold", "silver", "bronze"][place - 1];
  return /*#__PURE__*/React.createElement("span", {
    className: "zx-medal zx-medal--" + m
  }, place);
}
function fmtTime(sec) {
  sec = Math.max(0, sec);
  const m = Math.floor(sec / 60);
  const s = Math.floor(sec % 60);
  return m + ":" + String(s).padStart(2, "0");
}

/* compact tune row for the related rails */
function TuneMini({
  tune
}) {
  return /*#__PURE__*/React.createElement("a", {
    className: "tp-mini",
    href: "#",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("button", {
    className: "tp-mini__play",
    type: "button",
    "aria-label": "\u0421\u043B\u0443\u0448\u0430\u0442\u044C",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement(TIcon, {
    name: "play",
    size: 13
  })), /*#__PURE__*/React.createElement("span", {
    className: "tp-mini__body"
  }, /*#__PURE__*/React.createElement("span", {
    className: "tp-mini__title"
  }, tune.title), /*#__PURE__*/React.createElement("span", {
    className: "tp-mini__sub"
  }, tune.author, " \xB7 ", tune.chip)), /*#__PURE__*/React.createElement("span", {
    className: "tp-mini__time"
  }, tune.duration));
}
function Rail({
  title,
  kicker,
  items
}) {
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "tp-rail__h"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, title), kicker && /*#__PURE__*/React.createElement("span", {
    className: "kicker"
  }, kicker)), /*#__PURE__*/React.createElement("div", {
    className: "tp-rail"
  }, items.map(it => /*#__PURE__*/React.createElement(TuneMini, {
    key: it.id,
    tune: it
  }))));
}
function TunePage() {
  const t = TUNE;
  const [playing, setPlaying] = useState(false);
  const [pos, setPos] = useState(0); // seconds
  const rafRef = useRef(0);
  const lastRef = useRef(0);
  useEffect(() => {
    if (!playing) return;
    lastRef.current = performance.now();
    const tick = now => {
      const dt = (now - lastRef.current) / 1000;
      lastRef.current = now;
      setPos(p => {
        const np = p + dt;
        if (np >= t.durationSec) {
          setPlaying(false);
          return 0;
        }
        return np;
      });
      rafRef.current = requestAnimationFrame(tick);
    };
    rafRef.current = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(rafRef.current);
  }, [playing, t.durationSec]);
  const seek = useCallback(e => {
    const r = e.currentTarget.getBoundingClientRect();
    setPos(Math.max(0, Math.min(1, (e.clientX - r.left) / r.width)) * t.durationSec);
  }, [t.durationSec]);
  const pct = pos / t.durationSec * 100;
  return /*#__PURE__*/React.createElement("div", {
    className: "tp-root"
  }, /*#__PURE__*/React.createElement("nav", {
    className: "zx-breadcrumbs",
    "aria-label": "breadcrumb"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u0413\u043B\u0430\u0432\u043D\u0430\u044F"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\u041C\u0443\u0437\u044B\u043A\u0430"), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, t.author.name), " ", /*#__PURE__*/React.createElement("span", {
    className: "sep"
  }, "/"), " ", /*#__PURE__*/React.createElement("span", {
    className: "here"
  }, t.title)), /*#__PURE__*/React.createElement("div", {
    className: "tp-hero"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-player"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-stage"
  }, /*#__PURE__*/React.createElement(Oscilloscope, {
    playing: playing
  })), /*#__PURE__*/React.createElement("div", {
    className: "tp-transport"
  }, /*#__PURE__*/React.createElement("button", {
    className: "tp-listen",
    type: "button",
    onClick: () => setPlaying(p => !p)
  }, /*#__PURE__*/React.createElement(TIcon, {
    name: playing ? "pause" : "play",
    size: 22
  }), /*#__PURE__*/React.createElement("span", {
    className: "tp-listen__txt"
  }, playing ? "Пауза" : "Прослушать в браузере", /*#__PURE__*/React.createElement("span", {
    className: "tp-listen__sub"
  }, "OGG \xB7 ", t.duration))), /*#__PURE__*/React.createElement("div", {
    className: "tp-progress"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-progress__bar",
    onClick: seek
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-progress__fill",
    style: {
      width: pct + "%"
    }
  })), /*#__PURE__*/React.createElement("div", {
    className: "tp-progress__time"
  }, /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", null, fmtTime(pos))), /*#__PURE__*/React.createElement("span", null, t.duration))), /*#__PURE__*/React.createElement("button", {
    className: "tp-transport__icon",
    type: "button",
    "aria-label": "\u0421\u043A\u0430\u0447\u0430\u0442\u044C",
    title: "\u0421\u043A\u0430\u0447\u0430\u0442\u044C"
  }, /*#__PURE__*/React.createElement(TIcon, {
    name: "download",
    size: 18
  })))), /*#__PURE__*/React.createElement("div", {
    className: "tp-head"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-head__top"
  }, /*#__PURE__*/React.createElement("h1", {
    className: "tp-head__title"
  }, t.title), /*#__PURE__*/React.createElement("span", {
    className: "tp-head__id"
  }, "#", t.id)), /*#__PURE__*/React.createElement("div", {
    className: "tp-head__authors"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, t.author.name), /*#__PURE__*/React.createElement("span", {
    className: "tp-head__year"
  }, " \xB7 ", t.year)), /*#__PURE__*/React.createElement("div", {
    className: "tp-context"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-context__row"
  }, /*#__PURE__*/React.createElement(Medal, {
    place: t.competition.place
  }), /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, t.competition.name), /*#__PURE__*/React.createElement("span", {
    className: "dim"
  }, " \xB7 ", t.competition.compo, " \xB7 ", t.competition.place, " \u043C\u0435\u0441\u0442\u043E"))), /*#__PURE__*/React.createElement("div", {
    className: "tp-context__row"
  }, /*#__PURE__*/React.createElement(TIcon, {
    name: "chip",
    size: 15
  }), /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", null, t.device), /*#__PURE__*/React.createElement("span", {
    className: "dim"
  }, " \xB7 ", t.chip, " \xB7 \u0440\u0430\u0441\u043A\u043B\u0430\u0434\u043A\u0430 ", t.layout)))), /*#__PURE__*/React.createElement("div", {
    className: "tp-module"
  }, /*#__PURE__*/React.createElement("span", {
    className: "tp-module__label"
  }, "\u0412 \u043C\u043E\u0434\u0443\u043B\u0435"), /*#__PURE__*/React.createElement("div", {
    className: "tp-module__rows"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-module__row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "tp-module__k"
  }, "title"), /*#__PURE__*/React.createElement("span", {
    className: "tp-module__v"
  }, t.metaTitle)), /*#__PURE__*/React.createElement("div", {
    className: "tp-module__row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "tp-module__k"
  }, "author"), /*#__PURE__*/React.createElement("span", {
    className: "tp-module__v"
  }, t.metaAuthor)))), /*#__PURE__*/React.createElement("div", {
    className: "tp-rate"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-rate__score"
  }, /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, t.rating), /*#__PURE__*/React.createElement("span", {
    className: "of"
  }, "/ 5")), /*#__PURE__*/React.createElement(VoteWidget, {
    myVote: t.myVote,
    fav: t.fav
  }), /*#__PURE__*/React.createElement("div", {
    className: "tp-rate__counts"
  }, /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", null, t.votes), " \u0433\u043E\u043B\u043E\u0441\u043E\u0432"), /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", null, t.plays.toLocaleString("ru-RU")), " \u043F\u0440\u043E\u0441\u043B\u0443\u0448\u0438\u0432\u0430\u043D\u0438\u0439"))), /*#__PURE__*/React.createElement("div", {
    className: "tp-added"
  }, "\u0414\u043E\u0431\u0430\u0432\u0438\u043B ", /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, t.addedBy.name), " \xB7 ", t.addedAt))), /*#__PURE__*/React.createElement("div", {
    className: "tp-tagband"
  }, /*#__PURE__*/React.createElement("span", {
    className: "tp-tagband__label"
  }, "\u0422\u0435\u0433\u0438"), /*#__PURE__*/React.createElement("span", {
    className: "tp-tags"
  }, t.tags.map(tag => /*#__PURE__*/React.createElement("a", {
    key: tag,
    href: "#",
    onClick: e => e.preventDefault()
  }, tag)))), /*#__PURE__*/React.createElement("div", {
    className: "tp-cols"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-panel"
  }, /*#__PURE__*/React.createElement("h2", {
    className: "tp-panel__h"
  }, "\u0421\u0432\u0435\u0434\u0435\u043D\u0438\u044F"), /*#__PURE__*/React.createElement("dl", {
    className: "tp-meta"
  }, /*#__PURE__*/React.createElement("dt", null, "\u0413\u043E\u0434"), /*#__PURE__*/React.createElement("dd", null, t.year), /*#__PURE__*/React.createElement("dt", null, "\u0424\u043E\u0440\u043C\u0430\u0442 \u0444\u0430\u0439\u043B\u0430"), /*#__PURE__*/React.createElement("dd", {
    className: "mono"
  }, t.format), /*#__PURE__*/React.createElement("dt", null, "\u0417\u0432\u0443\u043A\u043E\u0432\u043E\u0435 \u0443\u0441\u0442\u0440\u043E\u0439\u0441\u0442\u0432\u043E"), /*#__PURE__*/React.createElement("dd", null, t.device), /*#__PURE__*/React.createElement("dt", null, "\u0427\u0438\u043F AY"), /*#__PURE__*/React.createElement("dd", {
    className: "mono"
  }, t.chip), /*#__PURE__*/React.createElement("dt", null, "\u0420\u0430\u0441\u043A\u043B\u0430\u0434\u043A\u0430 \u043A\u0430\u043D\u0430\u043B\u043E\u0432"), /*#__PURE__*/React.createElement("dd", {
    className: "mono"
  }, t.layout), /*#__PURE__*/React.createElement("dt", null, "\u041A\u0430\u043D\u0430\u043B\u043E\u0432"), /*#__PURE__*/React.createElement("dd", null, t.channels), /*#__PURE__*/React.createElement("dt", null, "\u0414\u043B\u0438\u0442\u0435\u043B\u044C\u043D\u043E\u0441\u0442\u044C"), /*#__PURE__*/React.createElement("dd", {
    className: "mono"
  }, t.duration), /*#__PURE__*/React.createElement("dt", null, "\u0422\u0440\u0435\u043A\u0435\u0440"), /*#__PURE__*/React.createElement("dd", null, t.tracker), /*#__PURE__*/React.createElement("dt", null, "\u041A\u043E\u043D\u043A\u0443\u0440\u0441"), /*#__PURE__*/React.createElement("dd", null, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, t.competition.name))), /*#__PURE__*/React.createElement("details", {
    className: "tp-tech"
  }, /*#__PURE__*/React.createElement("summary", null, /*#__PURE__*/React.createElement("span", {
    className: "chev"
  }, /*#__PURE__*/React.createElement(TIcon, {
    name: "chevron",
    size: 14
  })), "\u0422\u0435\u0445\u043D\u0438\u0447\u0435\u0441\u043A\u0438\u0435 \u0434\u0430\u043D\u043D\u044B\u0435"), /*#__PURE__*/React.createElement("dl", {
    className: "tp-tech__grid"
  }, /*#__PURE__*/React.createElement("dt", null, "\u0427\u0430\u0441\u0442\u043E\u0442\u0430 AY"), /*#__PURE__*/React.createElement("dd", {
    className: "mono"
  }, t.ayFreq), /*#__PURE__*/React.createElement("dt", null, "\u0427\u0430\u0441\u0442\u043E\u0442\u0430 \u043F\u0440\u0435\u0440\u044B\u0432\u0430\u043D\u0438\u0439"), /*#__PURE__*/React.createElement("dd", {
    className: "mono"
  }, t.intFreq), /*#__PURE__*/React.createElement("dt", null, "\u0418\u043C\u044F \u0444\u0430\u0439\u043B\u0430"), /*#__PURE__*/React.createElement("dd", {
    className: "mono"
  }, t.filename), /*#__PURE__*/React.createElement("dt", null, "\u0420\u0435\u043D\u0434\u0435\u0440 \u0430\u0443\u0434\u0438\u043E"), /*#__PURE__*/React.createElement("dd", {
    className: "mono"
  }, t.convertedBy)))), /*#__PURE__*/React.createElement("div", {
    className: "tp-rightcol"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-panel"
  }, /*#__PURE__*/React.createElement("h2", {
    className: "tp-panel__h"
  }, "\u0421\u043A\u0430\u0447\u0430\u0442\u044C ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, TUNE_DOWNLOADS.length, " \u0444\u043E\u0440\u043C\u0430\u0442\u0430")), /*#__PURE__*/React.createElement("div", {
    className: "tp-dl"
  }, TUNE_DOWNLOADS.map(d => /*#__PURE__*/React.createElement("a", {
    className: "tp-dl__row",
    key: d.id,
    href: "#",
    onClick: e => e.preventDefault()
  }, /*#__PURE__*/React.createElement("span", {
    className: "tp-dl__badge tp-dl__badge--" + d.kind
  }, d.ext), /*#__PURE__*/React.createElement("span", {
    className: "tp-dl__body"
  }, /*#__PURE__*/React.createElement("span", {
    className: "tp-dl__label"
  }, d.label), /*#__PURE__*/React.createElement("span", {
    className: "tp-dl__sub"
  }, d.sub)), /*#__PURE__*/React.createElement("span", {
    className: "tp-dl__size"
  }, d.size))))), /*#__PURE__*/React.createElement("div", {
    className: "tp-panel tp-used-panel"
  }, /*#__PURE__*/React.createElement("h2", {
    className: "tp-panel__h"
  }, "\u0418\u0441\u043F\u043E\u043B\u044C\u0437\u043E\u0432\u0430\u043D\u043E \u0432 ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, "1 \u043F\u0440\u043E\u0434\u0430\u043A\u0448\u0435\u043D")), /*#__PURE__*/React.createElement(ProdCard, {
    prod: USED_IN
  })))), /*#__PURE__*/React.createElement("section", {
    className: "tp-section"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-two"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-col__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u0413\u043E\u043B\u043E\u0441\u0430 ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, TUNE_VOTES.length)), /*#__PURE__*/React.createElement("span", {
    className: "right"
  }, "\u0441\u0440\u0435\u0434\u043D\u044F\u044F ", /*#__PURE__*/React.createElement("b", null, "\u2605 ", t.rating))), /*#__PURE__*/React.createElement("div", {
    className: "tp-list"
  }, TUNE_VOTES.map((v, i) => /*#__PURE__*/React.createElement("div", {
    className: "tp-list__row",
    key: i
  }, /*#__PURE__*/React.createElement("span", {
    className: "tp-list__date"
  }, v.date), /*#__PURE__*/React.createElement("span", {
    className: "tp-list__user"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, v.user)), /*#__PURE__*/React.createElement("span", {
    className: "tp-list__score"
  }, "★".repeat(v.score), "☆".repeat(5 - v.score)))))), /*#__PURE__*/React.createElement("div", {
    className: "tp-col"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-col__h"
  }, /*#__PURE__*/React.createElement("h2", null, "\u041A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0438 ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, TUNE_COMMENTS.length))), /*#__PURE__*/React.createElement("div", {
    className: "tp-comments"
  }, TUNE_COMMENTS.map(c => /*#__PURE__*/React.createElement("div", {
    className: "tp-comment",
    key: c.id
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-comment__head"
  }, /*#__PURE__*/React.createElement("span", {
    className: "tp-comment__user"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: e => e.preventDefault()
  }, c.user)), /*#__PURE__*/React.createElement("span", {
    className: "tp-comment__date"
  }, c.date)), /*#__PURE__*/React.createElement("div", {
    className: "tp-comment__body"
  }, c.body)))), /*#__PURE__*/React.createElement("div", {
    className: "tp-add"
  }, /*#__PURE__*/React.createElement("textarea", {
    placeholder: "\u041F\u043E\u0434\u0435\u043B\u0438\u0442\u044C\u0441\u044F \u0432\u043F\u0435\u0447\u0430\u0442\u043B\u0435\u043D\u0438\u0435\u043C \u043E \u043C\u0435\u043B\u043E\u0434\u0438\u0438\u2026"
  }), /*#__PURE__*/React.createElement("div", {
    className: "tp-add__foot"
  }, /*#__PURE__*/React.createElement("span", {
    className: "tp-add__hint"
  }, "Markdown \u043F\u043E\u0434\u0434\u0435\u0440\u0436\u0438\u0432\u0430\u0435\u0442\u0441\u044F"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--primary zx-button--sm",
    type: "button"
  }, "\u041E\u0442\u043F\u0440\u0430\u0432\u0438\u0442\u044C")))))), /*#__PURE__*/React.createElement("div", {
    className: "tp-related"
  }, /*#__PURE__*/React.createElement("div", {
    className: "tp-rails"
  }, /*#__PURE__*/React.createElement(Rail, {
    title: "Ещё от " + t.author.name,
    kicker: "\u0430\u0432\u0442\u043E\u0440",
    items: BY_AUTHOR_TUNES
  }), /*#__PURE__*/React.createElement(Rail, {
    title: "\u041F\u043E\u0445\u043E\u0436\u0438\u0435 \u043F\u043E \u0442\u0435\u0433\u0430\u043C",
    kicker: "ambient \xB7 realtime",
    items: BY_TAGS_TUNES
  }), /*#__PURE__*/React.createElement(Rail, {
    title: "Из " + t.tracker.split(" ").slice(0, 2).join(" "),
    kicker: "\u0442\u043E\u0442 \u0436\u0435 \u0442\u0440\u0435\u043A\u0435\u0440",
    items: BY_TRACKER_TUNES
  }))));
}
window.TunePage = TunePage;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/TunePage.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/VariantA.jsx
try { (() => {
/* Variant A — "Player-first"
   - Big hero with cover, alias, primary cat chips, rating, key people inline
   - Recommended release strip with one prominent Play button
   - Big screens mosaic above the fold (mosaic w/ 1 hero cell + 11 thumbs + "+X")
   - Tabs for: Releases / Music / Articles / Compilations / Series / Comments
   - Releases pane: filter bar + groups by type, expandable groups, card grid
*/
const {
  useState
} = React;
function VariantA() {
  const [tab, setTab] = useState("releases");
  const [groupOpen, setGroupOpen] = useState({
    original: true,
    adaptation: false,
    translation: false,
    modification: false,
    crack: false,
    unknown: false
  });

  // group releases
  const groups = {};
  RELEASES.forEach(r => {
    (groups[r.type] = groups[r.type] || []).push(r);
  });
  const groupOrder = ["original", "modification", "adaptation", "translation", "crack", "unknown"];

  // recommended = original 1992
  const recommended = RELEASES[0];
  return /*#__PURE__*/React.createElement("div", {
    style: {
      padding: 24,
      maxWidth: 1280,
      margin: "0 auto",
      fontFamily: "var(--font-sans)",
      color: "var(--text-color)",
      background: "var(--background-page)",
      minHeight: 1700
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)",
      marginBottom: 12,
      fontFamily: "var(--font-mono)"
    }
  }, "\u0413\u043B\u0430\u0432\u043D\u0430\u044F / \u0421\u043E\u0444\u0442 / \u0418\u0433\u0440\u044B / \u041F\u0440\u0438\u043A\u043B\u044E\u0447\u0435\u043D\u0438\u044F / \u041A\u0432\u0435\u0441\u0442\u044B-\u0433\u043E\u043B\u043E\u0432\u043E\u043B\u043E\u043C\u043A\u0438 / ", /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--text-color)"
    }
  }, "Crystal Kingdom Dizzy")), /*#__PURE__*/React.createElement("div", {
    className: "va-hero"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-hero__cover"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: 42,
    palette: "forest"
  }), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__shots"
  }, "\uD83D\uDCF7 46 \u0441\u043A\u0440\u0438\u043D\u043E\u0432")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "va-hero__title-row"
  }, /*#__PURE__*/React.createElement("h1", {
    className: "va-hero__title"
  }, PROD.title), /*#__PURE__*/React.createElement("span", {
    className: "va-hero__year"
  }, "\xB7 ", PROD.year)), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__alias"
  }, "\u0442\u0430\u043A\u0436\u0435 \u0438\u0437\u0432\u0435\u0441\u0442\u043D\u0430 \u043A\u0430\u043A ", /*#__PURE__*/React.createElement("i", null, PROD.alsoKnownAs)), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__chips"
  }, PROD.category.map(c => /*#__PURE__*/React.createElement("span", {
    key: c,
    className: "chip chip--cat"
  }, c)), /*#__PURE__*/React.createElement("span", {
    className: "chip"
  }, "\uD83C\uDDEC\uD83C\uDDE7 English"), /*#__PURE__*/React.createElement("span", {
    className: "chip",
    title: PROD.status
  }, "\u26A0 \u0420\u0430\u0441\u043F\u0440\u043E\u0441\u0442\u0440\u0430\u043D\u0435\u043D\u0438\u0435 \u0437\u0430\u043F\u0440\u0435\u0449\u0435\u043D\u043E")), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__rating-row"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-hero__rating"
  }, /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, PROD.rating.score), /*#__PURE__*/React.createElement("span", {
    className: "of"
  }, "/ ", PROD.rating.ofFive), /*#__PURE__*/React.createElement("span", {
    className: "votes"
  }, "\xB7 ", PROD.rating.votes, " \u0433\u043E\u043B\u043E\u0441\u043E\u0432")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 4
    }
  }, [1, 2, 3, 4, 5].map(s => /*#__PURE__*/React.createElement("span", {
    key: s,
    style: {
      color: s <= Math.round(PROD.rating.score) ? "var(--warning-500)" : "var(--secondary-300)",
      fontSize: 18
    }
  }, "\u2605"))), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--transparent"
  }, "\u0413\u043E\u043B\u043E\u0441\u043E\u0432\u0430\u0442\u044C")), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__people"
  }, /*#__PURE__*/React.createElement("b", null, "\u0410\u0432\u0442\u043E\u0440\u044B:"), " ", PROD.authors.join(", "), " \xB7 ", /*#__PURE__*/React.createElement("b", null, "\u041C\u0443\u0437\u044B\u043A\u0430:"), " ", PROD.music, " \xB7 ", /*#__PURE__*/React.createElement("b", null, "\u0418\u0437\u0434\u0430\u0442\u0435\u043B\u044C:"), " ", PROD.publisher, " \xB7 ", /*#__PURE__*/React.createElement("b", null, "\u0420\u0430\u0437\u0440\u0430\u0431\u043E\u0442\u0447\u0438\u043A:"), " ", PROD.developer), /*#__PURE__*/React.createElement("div", {
    className: "va-recommended"
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-recommended__badge"
  }, "\u2605 \u0440\u0435\u043A\u043E\u043C\u0435\u043D\u0434\u043E\u0432\u0430\u043D\u043D\u044B\u0439"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "va-recommended__title"
  }, recommended.title), /*#__PURE__*/React.createElement("div", {
    className: "va-recommended__meta"
  }, "\u041E\u0440\u0438\u0433\u0438\u043D\u0430\u043B \xB7 ", recommended.year, " \xB7 ", recommended.releasedBy, " \xB7 ", recommended.format, " \xB7 \uD83C\uDDEC\uD83C\uDDE7")), /*#__PURE__*/React.createElement("div", {
    className: "va-recommended__cta"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--md zx-button--secondary"
  }, "\u25B6 \u0418\u0433\u0440\u0430\u0442\u044C \u043E\u043D\u043B\u0430\u0439\u043D"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--md zx-button--outlined"
  }, "\u2B07 \u0421\u043A\u0430\u0447\u0430\u0442\u044C"))), /*#__PURE__*/React.createElement("div", {
    className: "va-hero__cta-row",
    style: {
      marginTop: 8
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--transparent"
  }, "\u2665 \u0412 \u0438\u0437\u0431\u0440\u0430\u043D\u043D\u043E\u0435"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--transparent"
  }, "+ \u0412 \u043F\u043E\u0434\u0431\u043E\u0440\u043A\u0443"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--transparent"
  }, "\uD83D\uDCE4 \u041F\u043E\u0434\u0435\u043B\u0438\u0442\u044C\u0441\u044F"), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "auto",
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, "\u0414\u043E\u0431\u0430\u0432\u043B\u0435\u043D\u0430 ", PROD.added)))), /*#__PURE__*/React.createElement("div", {
    className: "pp-card",
    style: {
      marginBottom: 16,
      padding: 12
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      marginBottom: 10
    }
  }, "\u0421\u043A\u0440\u0438\u043D\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, SCREENS.length), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "auto",
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)",
      fontWeight: 400
    }
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: {
      color: "var(--primary-600)"
    }
  }, "\u0433\u0430\u043B\u0435\u0440\u0435\u044F \u2197"))), /*#__PURE__*/React.createElement("div", {
    className: "va-screens"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-screens__cell va-screens__cell--big"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: SCREENS[0].id,
    palette: SCREENS[0].palette
  })), SCREENS.slice(1, 17).map(s => /*#__PURE__*/React.createElement("div", {
    key: s.id,
    className: "va-screens__cell"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: s.id,
    palette: s.palette
  }))), /*#__PURE__*/React.createElement("div", {
    className: "va-screens__cell va-screens__more"
  }, "+", SCREENS.length - 17))), /*#__PURE__*/React.createElement("div", {
    className: "pp-card",
    style: {
      marginBottom: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h"
  }, "\u041E \u043F\u0440\u043E\u0433\u0440\u0430\u043C\u043C\u0435"), /*#__PURE__*/React.createElement("p", {
    style: {
      margin: 0,
      lineHeight: 1.65,
      fontSize: "var(--font-md)"
    }
  }, PROD.story), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 12,
      display: "flex",
      gap: 6,
      flexWrap: "wrap"
    }
  }, PROD.tags.map(t => /*#__PURE__*/React.createElement("span", {
    key: t,
    style: {
      fontSize: "var(--font-xs)",
      padding: "2px 8px",
      background: "var(--secondary-100)",
      border: "1px solid var(--secondary-200)",
      borderRadius: 999,
      color: "var(--text-light-color)"
    }
  }, t)))), /*#__PURE__*/React.createElement("div", {
    className: "va-tabs"
  }, /*#__PURE__*/React.createElement("button", {
    className: tab === "releases" ? "active" : "",
    onClick: () => setTab("releases")
  }, "\u0420\u0435\u043B\u0438\u0437\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, RELEASES.length)), /*#__PURE__*/React.createElement("button", {
    className: tab === "music" ? "active" : "",
    onClick: () => setTab("music")
  }, "\u041C\u0443\u0437\u044B\u043A\u0430 ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, PROD_TUNES.length)), /*#__PURE__*/React.createElement("button", {
    className: tab === "articles" ? "active" : "",
    onClick: () => setTab("articles")
  }, "\u0421\u0442\u0430\u0442\u044C\u0438 \u0438 \u043A\u0430\u0440\u0442\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, MENTIONS.length + MAPS.length)), /*#__PURE__*/React.createElement("button", {
    className: tab === "compilations" ? "active" : "",
    onClick: () => setTab("compilations")
  }, "\u0412 \u0441\u0431\u043E\u0440\u043D\u0438\u043A\u0430\u0445 ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, COMPILATIONS.length)), /*#__PURE__*/React.createElement("button", {
    className: tab === "series" ? "active" : "",
    onClick: () => setTab("series")
  }, "\u0421\u0435\u0440\u0438\u044F Dizzy ", /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, SAME_SERIES.length)), /*#__PURE__*/React.createElement("button", {
    className: tab === "comments" ? "active" : "",
    onClick: () => setTab("comments")
  }, "\u041E\u0431\u0441\u0443\u0436\u0434\u0435\u043D\u0438\u044F")), tab === "releases" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar",
    style: {
      marginBottom: 12
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "vb-filter-bar__label"
  }, "\u0424\u0438\u043B\u044C\u0442\u0440:"), /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar__group"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--outlined"
  }, "\u0432\u0441\u0435 \u044F\u0437\u044B\u043A\u0438 \u25BE"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--outlined"
  }, "\u0432\u0441\u0435 \u043F\u043B\u0430\u0442\u0444\u043E\u0440\u043C\u044B \u25BE"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--outlined"
  }, "\u0432\u0441\u0435 \u0444\u043E\u0440\u043C\u0430\u0442\u044B \u25BE")), /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar__sep"
  }), /*#__PURE__*/React.createElement("span", {
    className: "vb-filter-bar__label"
  }, "\u0421\u043E\u0440\u0442:"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--outlined"
  }, "\u043F\u043E \u0433\u043E\u0434\u0443 \u2193")), groupOrder.map(t => {
    const list = groups[t];
    if (!list) return null;
    const open = groupOpen[t];
    return /*#__PURE__*/React.createElement("div", {
      key: t,
      className: "va-rel-group" + (open ? " va-rel-group--open" : "")
    }, /*#__PURE__*/React.createElement("div", {
      className: "va-rel-group__head",
      onClick: () => setGroupOpen({
        ...groupOpen,
        [t]: !open
      })
    }, /*#__PURE__*/React.createElement("span", {
      className: "rel-type-pill rel-type-pill--" + t
    }, RELEASE_TYPES[t].label), /*#__PURE__*/React.createElement("span", {
      className: "va-rel-group__title"
    }, list.length === 1 ? "1 релиз" : list.length + " релизов"), /*#__PURE__*/React.createElement("span", {
      className: "va-rel-group__count"
    }, list.map(r => r.year).filter(Boolean).filter((v, i, a) => a.indexOf(v) === i).join(" · ")), /*#__PURE__*/React.createElement("svg", {
      className: "va-rel-group__chev",
      viewBox: "0 0 24 24",
      width: "18",
      height: "18",
      fill: "currentColor"
    }, /*#__PURE__*/React.createElement("path", {
      d: "M7 10l5 5 5-5z"
    }))), open && /*#__PURE__*/React.createElement("div", {
      className: "va-rel-group__body"
    }, list.map(r => /*#__PURE__*/React.createElement("div", {
      key: r.id,
      className: "va-rel-card"
    }, /*#__PURE__*/React.createElement("div", {
      className: "va-rel-card__cover"
    }, r.screens.length ? /*#__PURE__*/React.createElement(ZxScreen, {
      seed: r.id * 13,
      palette: ["sunset", "cool", "forest", "night", "default"][r.id % 5]
    }) : /*#__PURE__*/React.createElement("div", {
      style: {
        height: "100%",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        fontSize: 11,
        color: "var(--secondary-400)"
      }
    }, "\u2014")), /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        minWidth: 0
      }
    }, /*#__PURE__*/React.createElement("div", {
      className: "va-rel-card__title"
    }, r.title), /*#__PURE__*/React.createElement("div", {
      className: "va-rel-card__meta"
    }, r.releasedBy || "автор неизвестен", r.year ? " · " + r.year : ""), /*#__PURE__*/React.createElement("div", {
      className: "va-rel-card__chips"
    }, /*#__PURE__*/React.createElement("span", {
      className: "va-rel-card__chip va-rel-card__chip--lang"
    }, r.lang === "ru" ? "🇷🇺" : "🇬🇧", " ", r.lang.toUpperCase()), r.format && /*#__PURE__*/React.createElement("span", {
      className: "va-rel-card__chip"
    }, r.format.includes("SCL") ? "💾" : "📼", " ", r.format), r.hardware.map(h => /*#__PURE__*/React.createElement("span", {
      key: h,
      className: "va-rel-card__chip"
    }, h.includes("AY") ? "🔊 " : "", h)), r.note && /*#__PURE__*/React.createElement("span", {
      className: "va-rel-card__chip va-rel-card__chip--cheats"
    }, "\u2605 ", r.note)), /*#__PURE__*/React.createElement("div", {
      className: "va-rel-card__bottom"
    }, r.playOnline && /*#__PURE__*/React.createElement("a", {
      className: "play-link",
      href: "#"
    }, "\u25B6 \u0418\u0433\u0440\u0430\u0442\u044C"), /*#__PURE__*/React.createElement("span", null, "\u2B07 ", r.downloads), /*#__PURE__*/React.createElement("span", null, "\xB7 \u25B6 ", r.plays), /*#__PURE__*/React.createElement("span", {
      style: {
        marginLeft: "auto"
      }
    }, "\u2605 ", r.votes)))))));
  })), tab === "music" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gap: 6
    }
  }, PROD_TUNES.map(t => /*#__PURE__*/React.createElement("div", {
    key: t.id,
    style: {
      display: "flex",
      alignItems: "center",
      gap: 12,
      padding: "8px 4px",
      borderBottom: "1px solid var(--secondary-200)"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-mono)",
      color: "var(--text-light-color)",
      width: 24,
      fontSize: "var(--font-sm)"
    }
  }, t.idx), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--secondary zx-button--round",
    "aria-label": "Play"
  }, "\u25B6"), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 700,
      fontSize: "var(--font-sm)"
    }
  }, t.title), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, t.author, " \xB7 ", t.chip, " \xB7 ", t.year)), /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-mono)",
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, t.duration), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)",
      width: 60,
      textAlign: "right"
    }
  }, "\u25B6 ", t.plays), /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--warning-500)",
      fontSize: 14
    }
  }, "★".repeat(t.stars)))))), tab === "articles" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      fontSize: "var(--font-md)"
    }
  }, "\u041A\u0430\u0440\u0442\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, MAPS.length)), /*#__PURE__*/React.createElement("div", {
    className: "va-mini"
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-mini__icon"
  }, "\uD83D\uDDFA"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-mini__title"
  }, "\u041A\u0430\u0440\u0442\u0430 \u043F\u0440\u043E\u0445\u043E\u0436\u0434\u0435\u043D\u0438\u044F"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__sub"
  }, "by ", MAPS[0].author)), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--outlined"
  }, "\u041E\u0442\u043A\u0440\u044B\u0442\u044C"))), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      fontSize: "var(--font-md)"
    }
  }, "\u0423\u043F\u043E\u043C\u0438\u043D\u0430\u043D\u0438\u044F \u0432 \u0441\u0442\u0430\u0442\u044C\u044F\u0445 ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, MENTIONS.length)), MENTIONS.map((m, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    className: "va-mini"
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-mini__icon"
  }, "\uD83D\uDCF0"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-mini__title"
  }, m.mag, " #", String(m.issue).padStart(2, "0"), " (", m.year, ") \xB7 ", m.section), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__sub"
  }, m.body)))), /*#__PURE__*/React.createElement("div", {
    className: "va-mini"
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-mini__icon"
  }, "\uD83C\uDFAC"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-mini__title"
  }, "\u041F\u0440\u043E\u0445\u043E\u0436\u0434\u0435\u043D\u0438\u0435 \u0432 \u0444\u043E\u0440\u043C\u0430\u0442\u0435 RZX"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__sub"
  }, "CrystalKingdomDizzy.rzx.zip \xB7 by Jamie Angus (with rollback)")), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--outlined",
    disabled: true
  }, "denied")))), tab === "compilations" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fill, minmax(280px, 1fr))",
      gap: 10
    }
  }, COMPILATIONS.map((c, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      padding: 12,
      border: "1px solid var(--secondary-200)",
      borderRadius: "var(--radius-md)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 700
    }
  }, c.title), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)",
      marginTop: 2
    }
  }, c.by || "—", c.year ? " · " + c.year : "", c.count ? " · " + c.count + " программ" : ""), c.format && /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 8
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-rel-card__chip"
  }, c.format)))))), tab === "series" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fill, minmax(220px, 1fr))",
      gap: 10
    }
  }, SAME_SERIES.map((s, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      padding: 10,
      border: s.title === PROD.title ? "2px solid var(--primary-500)" : "1px solid var(--secondary-200)",
      borderRadius: "var(--radius-md)",
      background: s.title === PROD.title ? "var(--primary-50)" : "var(--surface)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      aspectRatio: "4/3",
      background: "var(--background-deep)",
      borderRadius: "var(--radius-sm)",
      overflow: "hidden",
      marginBottom: 8
    }
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: i * 1000 + 7,
    palette: ["sunset", "cool", "forest", "night", "default"][i % 5]
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 700,
      fontSize: "var(--font-sm)"
    }
  }, s.title), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, s.by, " \xB7 ", s.year), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 6,
      fontSize: 10,
      color: "var(--text-light-color)"
    }
  }, s.hardware.join(" · ")))))), tab === "comments" && /*#__PURE__*/React.createElement("div", {
    className: "pp-card"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: 12
    }
  }, /*#__PURE__*/React.createElement("textarea", {
    className: "zx-input",
    style: {
      height: 80,
      width: "100%",
      padding: 10
    },
    placeholder: "\u041E\u0441\u0442\u0430\u0432\u0438\u0442\u044C \u043A\u043E\u043C\u043C\u0435\u043D\u0442\u0430\u0440\u0438\u0439\u2026"
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      justifyContent: "flex-end",
      marginTop: 8
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--secondary"
  }, "\u041E\u0442\u043F\u0440\u0430\u0432\u0438\u0442\u044C"))), /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      fontSize: "var(--font-md)"
    }
  }, "\u0418\u0441\u0442\u043E\u0440\u0438\u044F \u0433\u043E\u043B\u043E\u0441\u043E\u0432 ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, VOTES.length)), VOTES.map((v, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      padding: "8px 0",
      borderBottom: "1px dashed var(--secondary-200)",
      display: "flex",
      gap: 12,
      fontSize: "var(--font-sm)"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-mono)",
      color: "var(--text-light-color)",
      fontSize: "var(--font-xs)",
      width: 60
    }
  }, v.year), /*#__PURE__*/React.createElement("span", {
    style: {
      flex: 1
    }
  }, /*#__PURE__*/React.createElement("b", null, v.user), " \u0433\u043E\u043B\u043E\u0441\u043E\u0432\u0430\u043B \u0437\u0430 ", /*#__PURE__*/React.createElement("i", null, v.target)), /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--warning-500)"
    }
  }, v.score ? "★".repeat(v.score) : "—")))));
}
window.VariantA = VariantA;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/VariantA.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/VariantB.jsx
try { (() => {
/* Variant B — "Collector-first"
   - Wikipedia-style sticky sidebar infobox (cover, key facts, expandable details)
   - Main column: short summary, screen mosaic, releases with table↔cards toggle + rich filter bar,
     accordions for secondary sections.
*/
const {
  useState
} = React;
function VariantB() {
  const [view, setView] = useState("table");
  const [filterLang, setFilterLang] = useState("all");
  const [filterType, setFilterType] = useState("all");
  const filtered = RELEASES.filter(r => (filterLang === "all" || r.lang === filterLang) && (filterType === "all" || r.type === filterType));
  return /*#__PURE__*/React.createElement("div", {
    style: {
      padding: 24,
      maxWidth: 1280,
      margin: "0 auto",
      fontFamily: "var(--font-sans)",
      color: "var(--text-color)",
      background: "var(--background-page)",
      minHeight: 1700
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)",
      marginBottom: 12,
      fontFamily: "var(--font-mono)"
    }
  }, "\u0413\u043B\u0430\u0432\u043D\u0430\u044F / \u0421\u043E\u0444\u0442 / \u0418\u0433\u0440\u044B / \u041F\u0440\u0438\u043A\u043B\u044E\u0447\u0435\u043D\u0438\u044F / \u041A\u0432\u0435\u0441\u0442\u044B-\u0433\u043E\u043B\u043E\u0432\u043E\u043B\u043E\u043C\u043A\u0438 / ", /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--text-color)"
    }
  }, "Crystal Kingdom Dizzy")), /*#__PURE__*/React.createElement("h1", {
    style: {
      fontSize: "var(--font-xxl)",
      margin: "0 0 4px"
    }
  }, PROD.title), /*#__PURE__*/React.createElement("div", {
    style: {
      color: "var(--text-light-color)",
      marginBottom: 16
    }
  }, "\u041F\u0440\u0438\u043A\u043B\u044E\u0447\u0435\u043D\u0447\u0435\u0441\u043A\u0430\u044F \u0438\u0433\u0440\u0430-\u0433\u043E\u043B\u043E\u0432\u043E\u043B\u043E\u043C\u043A\u0430 \xB7 ", PROD.year, " \xB7 \u0442\u0430\u043A\u0436\u0435 \u0438\u0437\u0432\u0435\u0441\u0442\u043D\u0430 \u043A\u0430\u043A ", /*#__PURE__*/React.createElement("i", null, PROD.alsoKnownAs)), /*#__PURE__*/React.createElement("div", {
    className: "vb-grid"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "vb-summary pp-card",
    style: {
      padding: 12
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 10,
      alignItems: "center"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "baseline",
      gap: 4
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--font-xl)",
      fontWeight: 700,
      color: "var(--warning-700)"
    }
  }, PROD.rating.score), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--font-sm)",
      color: "var(--text-light-color)"
    }
  }, "/ 5")), /*#__PURE__*/React.createElement("div", {
    style: {
      color: "var(--warning-500)",
      fontSize: 16
    }
  }, "★".repeat(Math.round(PROD.rating.score))), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, "\xB7 ", PROD.rating.votes, " \u0433\u043E\u043B\u043E\u0441\u043E\u0432"), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, "\xB7 \uD83D\uDCF7 ", SCREENS.length), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, "\xB7 \uD83D\uDCBF ", RELEASES.length), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, "\xB7 \uD83C\uDFB5 ", PROD_TUNES.length)), /*#__PURE__*/React.createElement("div", {
    className: "vb-summary__cta"
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--md zx-button--secondary"
  }, "\u25B6 \u0418\u0433\u0440\u0430\u0442\u044C \u043E\u043D\u043B\u0430\u0439\u043D"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--md zx-button--outlined"
  }, "\u2B07 \u0421\u043A\u0430\u0447\u0430\u0442\u044C"), /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--md zx-button--transparent"
  }, "\u2665"))), /*#__PURE__*/React.createElement("div", {
    className: "pp-card",
    style: {
      marginBottom: 16,
      padding: 12
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      marginBottom: 10
    }
  }, "\u0421\u043A\u0440\u0438\u043D\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, SCREENS.length), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "auto",
      fontSize: "var(--font-xs)",
      fontWeight: 400
    }
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: {
      color: "var(--primary-600)"
    }
  }, "\u0433\u0430\u043B\u0435\u0440\u0435\u044F \u2197"))), /*#__PURE__*/React.createElement("div", {
    className: "vb-mosaic"
  }, SCREENS.slice(0, 24).map(s => /*#__PURE__*/React.createElement("div", {
    key: s.id,
    className: "vb-mosaic__cell"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: s.id,
    palette: s.palette
  }))))), /*#__PURE__*/React.createElement("div", {
    className: "pp-card",
    style: {
      marginBottom: 16
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      margin: 0,
      lineHeight: 1.65
    }
  }, PROD.story)), /*#__PURE__*/React.createElement("div", {
    className: "pp-section-h",
    style: {
      marginTop: 24
    }
  }, "\u0420\u0435\u043B\u0438\u0437\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, RELEASES.length), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "auto"
    }
  })), /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar"
  }, /*#__PURE__*/React.createElement("span", {
    className: "vb-filter-bar__label"
  }, "\u042F\u0437\u044B\u043A:"), /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar__group"
  }, [["all", "все"], ["en", "🇬🇧 EN"], ["ru", "🇷🇺 RU"]].map(([k, l]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    onClick: () => setFilterLang(k),
    className: "zx-button zx-button--sm " + (filterLang === k ? "zx-button--secondary" : "zx-button--outlined")
  }, l))), /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar__sep"
  }), /*#__PURE__*/React.createElement("span", {
    className: "vb-filter-bar__label"
  }, "\u0422\u0438\u043F:"), /*#__PURE__*/React.createElement("div", {
    className: "vb-filter-bar__group"
  }, [["all", "все"], ...Object.entries(RELEASE_TYPES).map(([k, v]) => [k, v.label])].map(([k, l]) => /*#__PURE__*/React.createElement("button", {
    key: k,
    onClick: () => setFilterType(k),
    className: "zx-button zx-button--sm " + (filterType === k ? "zx-button--secondary" : "zx-button--outlined")
  }, l))), /*#__PURE__*/React.createElement("div", {
    className: "vb-toggle"
  }, /*#__PURE__*/React.createElement("button", {
    className: view === "table" ? "active" : "",
    onClick: () => setView("table")
  }, "\u2630 \u0442\u0430\u0431\u043B\u0438\u0446\u0430"), /*#__PURE__*/React.createElement("button", {
    className: view === "cards" ? "active" : "",
    onClick: () => setView("cards")
  }, "\u25A6 \u043A\u0430\u0440\u0442\u043E\u0447\u043A\u0438"))), view === "table" && /*#__PURE__*/React.createElement("table", {
    className: "vb-rel-table"
  }, /*#__PURE__*/React.createElement("thead", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null), /*#__PURE__*/React.createElement("th", null, "\u041D\u0430\u0437\u0432\u0430\u043D\u0438\u0435 \xB7 \u0430\u0432\u0442\u043E\u0440"), /*#__PURE__*/React.createElement("th", null, "\u0413\u043E\u0434"), /*#__PURE__*/React.createElement("th", null, "\u0422\u0438\u043F"), /*#__PURE__*/React.createElement("th", null, "\u042F\u0437."), /*#__PURE__*/React.createElement("th", null, "\u041F\u043B\u0430\u0442\u0444\u043E\u0440\u043C\u0430 / \u0444\u043E\u0440\u043C\u0430\u0442"), /*#__PURE__*/React.createElement("th", {
    style: {
      textAlign: "right"
    }
  }, "\u2605"), /*#__PURE__*/React.createElement("th", {
    style: {
      textAlign: "right"
    }
  }, "\u2B07"), /*#__PURE__*/React.createElement("th", {
    style: {
      textAlign: "right"
    }
  }, "\u25B6"), /*#__PURE__*/React.createElement("th", null))), /*#__PURE__*/React.createElement("tbody", null, filtered.map(r => /*#__PURE__*/React.createElement("tr", {
    key: r.id,
    className: r.id === 1 ? "recommended-row" : ""
  }, /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("div", {
    className: "vb-rel-table__shot"
  }, r.screens.length ? /*#__PURE__*/React.createElement(ZxScreen, {
    seed: r.id * 13,
    palette: ["sunset", "cool", "forest", "night", "default"][r.id % 5]
  }) : null)), /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("div", {
    className: "vb-rel-table__title"
  }, r.id === 1 && /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--warning-500)",
      marginRight: 4
    }
  }, "\u2605"), r.title), /*#__PURE__*/React.createElement("div", {
    className: "vb-rel-table__by"
  }, r.releasedBy || "—", r.note ? " · " + r.note : "")), /*#__PURE__*/React.createElement("td", {
    style: {
      fontFamily: "var(--font-mono)"
    }
  }, r.year || "—"), /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("span", {
    className: "rel-type-pill rel-type-pill--" + r.type
  }, RELEASE_TYPES[r.type].label)), /*#__PURE__*/React.createElement("td", null, r.lang === "ru" ? "🇷🇺" : "🇬🇧"), /*#__PURE__*/React.createElement("td", {
    style: {
      fontSize: "var(--font-xs)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "tag-row"
  }, r.format && /*#__PURE__*/React.createElement("span", {
    className: "tag-glyph",
    title: r.format
  }, r.format.includes("SCL") ? "💾" : "📼"), r.hardware.slice(0, 3).map(h => /*#__PURE__*/React.createElement("span", {
    key: h,
    className: "tag-glyph",
    title: h
  }, h.includes("AY") ? "🔊" : h.includes("джойстик") ? "🕹" : "🖥")))), /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "right",
      fontFamily: "var(--font-mono)"
    }
  }, r.votes || "—"), /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "right",
      fontFamily: "var(--font-mono)",
      color: "var(--text-light-color)"
    }
  }, r.downloads), /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "right",
      fontFamily: "var(--font-mono)",
      color: "var(--text-light-color)"
    }
  }, r.plays), /*#__PURE__*/React.createElement("td", null, r.playOnline && /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--secondary"
  }, "\u25B6")))))), view === "cards" && /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fill, minmax(260px, 1fr))",
      gap: 10
    }
  }, filtered.map(r => /*#__PURE__*/React.createElement("div", {
    key: r.id,
    className: "va-rel-card",
    style: {
      borderColor: r.id === 1 ? "var(--primary-400)" : "var(--secondary-200)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__cover"
  }, r.screens.length ? /*#__PURE__*/React.createElement(ZxScreen, {
    seed: r.id * 13,
    palette: ["sunset", "cool", "forest", "night", "default"][r.id % 5]
  }) : null), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      minWidth: 0
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__title"
  }, r.title), /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__meta"
  }, r.releasedBy || "—", r.year ? " · " + r.year : ""), /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__chips"
  }, /*#__PURE__*/React.createElement("span", {
    className: "rel-type-pill rel-type-pill--" + r.type
  }, RELEASE_TYPES[r.type].label), /*#__PURE__*/React.createElement("span", {
    className: "va-rel-card__chip va-rel-card__chip--lang"
  }, r.lang === "ru" ? "🇷🇺" : "🇬🇧"), r.format && /*#__PURE__*/React.createElement("span", {
    className: "va-rel-card__chip"
  }, r.format)), /*#__PURE__*/React.createElement("div", {
    className: "va-rel-card__bottom"
  }, r.playOnline && /*#__PURE__*/React.createElement("a", {
    className: "play-link",
    href: "#"
  }, "\u25B6"), /*#__PURE__*/React.createElement("span", null, "\u2B07", r.downloads), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "auto"
    }
  }, "\u2605", r.votes)))))), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 24
    }
  }, /*#__PURE__*/React.createElement("details", {
    className: "vb-acc",
    open: true
  }, /*#__PURE__*/React.createElement("summary", null, "\uD83C\uDFB5 \u041C\u0443\u0437\u044B\u043A\u0430 \u043F\u0440\u043E\u0433\u0440\u0430\u043C\u043C\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, PROD_TUNES.length), " ", /*#__PURE__*/React.createElement("svg", {
    className: "chev",
    viewBox: "0 0 24 24",
    width: "16",
    height: "16",
    fill: "currentColor"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M7 10l5 5 5-5z"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "vb-acc__body"
  }, PROD_TUNES.map(t => /*#__PURE__*/React.createElement("div", {
    key: t.id,
    style: {
      display: "flex",
      alignItems: "center",
      gap: 10,
      padding: "6px 0",
      borderBottom: "1px solid var(--secondary-200)",
      fontSize: "var(--font-sm)"
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: "zx-button zx-button--sm zx-button--secondary zx-button--round"
  }, "\u25B6"), /*#__PURE__*/React.createElement("span", {
    style: {
      fontWeight: 700
    }
  }, t.title), /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--text-light-color)"
    }
  }, t.author), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "auto",
      color: "var(--warning-500)"
    }
  }, "★".repeat(t.stars)), /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-mono)",
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)"
    }
  }, t.duration))))), /*#__PURE__*/React.createElement("details", {
    className: "vb-acc"
  }, /*#__PURE__*/React.createElement("summary", null, "\uD83D\uDCF0 \u0423\u043F\u043E\u043C\u0438\u043D\u0430\u043D\u0438\u044F \u0438 \u043A\u0430\u0440\u0442\u044B ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, MENTIONS.length + MAPS.length), " ", /*#__PURE__*/React.createElement("svg", {
    className: "chev",
    viewBox: "0 0 24 24",
    width: "16",
    height: "16",
    fill: "currentColor"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M7 10l5 5 5-5z"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "vb-acc__body"
  }, MENTIONS.map((m, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    className: "va-mini"
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-mini__icon"
  }, "\uD83D\uDCF0"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-mini__title"
  }, m.mag, " #", String(m.issue).padStart(2, "0"), " (", m.year, ") \xB7 ", m.section), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__sub"
  }, m.body)))), /*#__PURE__*/React.createElement("div", {
    className: "va-mini"
  }, /*#__PURE__*/React.createElement("span", {
    className: "va-mini__icon"
  }, "\uD83D\uDDFA"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "va-mini__title"
  }, "\u041A\u0430\u0440\u0442\u0430 \u043F\u0440\u043E\u0445\u043E\u0436\u0434\u0435\u043D\u0438\u044F"), /*#__PURE__*/React.createElement("div", {
    className: "va-mini__sub"
  }, "by ", MAPS[0].author))))), /*#__PURE__*/React.createElement("details", {
    className: "vb-acc"
  }, /*#__PURE__*/React.createElement("summary", null, "\uD83D\uDCE6 \u0412 \u0441\u0431\u043E\u0440\u043D\u0438\u043A\u0430\u0445 ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, COMPILATIONS.length), " ", /*#__PURE__*/React.createElement("svg", {
    className: "chev",
    viewBox: "0 0 24 24",
    width: "16",
    height: "16",
    fill: "currentColor"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M7 10l5 5 5-5z"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "vb-acc__body"
  }, COMPILATIONS.map((c, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      padding: "6px 0",
      borderBottom: "1px dashed var(--secondary-200)",
      fontSize: "var(--font-sm)"
    }
  }, /*#__PURE__*/React.createElement("b", null, c.title), " ", /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--text-light-color)"
    }
  }, "\xB7 ", c.by || "—", c.year ? " · " + c.year : ""))))), /*#__PURE__*/React.createElement("details", {
    className: "vb-acc"
  }, /*#__PURE__*/React.createElement("summary", null, "\uD83C\uDFAE \u0421\u0435\u0440\u0438\u044F Dizzy ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, SAME_SERIES.length), " ", /*#__PURE__*/React.createElement("svg", {
    className: "chev",
    viewBox: "0 0 24 24",
    width: "16",
    height: "16",
    fill: "currentColor"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M7 10l5 5 5-5z"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "vb-acc__body"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fill, minmax(180px, 1fr))",
      gap: 10
    }
  }, SAME_SERIES.map((s, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      fontSize: "var(--font-xs)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      aspectRatio: "4/3",
      background: "var(--background-deep)",
      borderRadius: 4,
      overflow: "hidden",
      marginBottom: 4
    }
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: i * 1000 + 7,
    palette: ["sunset", "cool", "forest", "night", "default"][i % 5]
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 700
    }
  }, s.title), /*#__PURE__*/React.createElement("div", {
    style: {
      color: "var(--text-light-color)"
    }
  }, s.year || "—")))))), /*#__PURE__*/React.createElement("details", {
    className: "vb-acc"
  }, /*#__PURE__*/React.createElement("summary", null, "\uD83D\uDCAC \u041E\u0431\u0441\u0443\u0436\u0434\u0435\u043D\u0438\u044F \u0438 \u0438\u0441\u0442\u043E\u0440\u0438\u044F \u0433\u043E\u043B\u043E\u0441\u043E\u0432 ", /*#__PURE__*/React.createElement("span", {
    className: "count"
  }, VOTES.length), " ", /*#__PURE__*/React.createElement("svg", {
    className: "chev",
    viewBox: "0 0 24 24",
    width: "16",
    height: "16",
    fill: "currentColor"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M7 10l5 5 5-5z"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "vb-acc__body"
  }, VOTES.map((v, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      padding: "4px 0",
      fontSize: "var(--font-sm)",
      display: "flex",
      gap: 10
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-mono)",
      color: "var(--text-light-color)",
      width: 50
    }
  }, v.year), /*#__PURE__*/React.createElement("span", {
    style: {
      flex: 1
    }
  }, /*#__PURE__*/React.createElement("b", null, v.user), " \u2192 ", /*#__PURE__*/React.createElement("i", null, v.target)), /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--warning-500)"
    }
  }, v.score ? "★".repeat(v.score) : "—"))))))), /*#__PURE__*/React.createElement("aside", {
    className: "vb-infobox"
  }, /*#__PURE__*/React.createElement("div", {
    className: "vb-infobox__cover"
  }, /*#__PURE__*/React.createElement(ZxScreen, {
    seed: 42,
    palette: "forest"
  })), /*#__PURE__*/React.createElement("div", {
    className: "vb-infobox__title-strip"
  }, /*#__PURE__*/React.createElement("h3", {
    className: "vb-infobox__title"
  }, PROD.title), /*#__PURE__*/React.createElement("div", {
    className: "vb-infobox__alias"
  }, "aka ", PROD.alsoKnownAs)), /*#__PURE__*/React.createElement("div", {
    className: "vb-infobox__rating-row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "big"
  }, PROD.rating.score), /*#__PURE__*/React.createElement("span", {
    style: {
      color: "var(--warning-500)"
    }
  }, "★".repeat(Math.round(PROD.rating.score))), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--text-light-color)",
      marginLeft: "auto"
    }
  }, PROD.rating.votes, " votes")), /*#__PURE__*/React.createElement("dl", null, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("dt", null, "\u0413\u043E\u0434"), /*#__PURE__*/React.createElement("dd", {
    style: {
      fontFamily: "var(--font-mono)"
    }
  }, PROD.year)), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("dt", null, "\u041A\u0430\u0442\u0435\u0433\u043E\u0440\u0438\u044F"), /*#__PURE__*/React.createElement("dd", null, PROD.category.join(" / "))), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("dt", null, "\u042F\u0437\u044B\u043A"), /*#__PURE__*/React.createElement("dd", null, "\uD83C\uDDEC\uD83C\uDDE7 English")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("dt", null, "\u0410\u0432\u0442\u043E\u0440\u044B"), /*#__PURE__*/React.createElement("dd", null, PROD.authors.join(", "))), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("dt", null, "\u041C\u0443\u0437\u044B\u043A\u0430"), /*#__PURE__*/React.createElement("dd", null, PROD.music)), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("dt", null, "\u0418\u0437\u0434\u0430\u0442\u0435\u043B\u044C"), /*#__PURE__*/React.createElement("dd", null, PROD.publisher)), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("dt", null, "\u0420\u0430\u0437\u0440\u0430\u0431\u043E\u0442\u0447\u0438\u043A"), /*#__PURE__*/React.createElement("dd", null, PROD.developer)), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("dt", null, "\u0421\u0435\u0440\u0438\u044F"), /*#__PURE__*/React.createElement("dd", null, "Dizzy (", PROD.series.count, ")")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("dt", null, "\u0421\u0442\u0430\u0442\u0443\u0441"), /*#__PURE__*/React.createElement("dd", {
    style: {
      color: "var(--danger-500)",
      fontSize: "var(--font-xs)"
    }
  }, "\u26A0 ", PROD.status))), /*#__PURE__*/React.createElement("details", {
    className: "vb-infobox__details"
  }, /*#__PURE__*/React.createElement("summary", null, "\u0412\u043D\u0435\u0448\u043D\u0438\u0435 \u0441\u0441\u044B\u043B\u043A\u0438 (", PROD.links.length, ") \u25BE"), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 6,
      display: "grid",
      gap: 4
    }
  }, PROD.links.map((l, i) => /*#__PURE__*/React.createElement("a", {
    key: i,
    href: "#",
    style: {
      fontSize: "var(--font-xs)",
      color: "var(--primary-600)",
      textDecoration: "none"
    }
  }, "\u2197 ", l.label)))), /*#__PURE__*/React.createElement("details", {
    className: "vb-infobox__details"
  }, /*#__PURE__*/React.createElement("summary", null, "\u0422\u0435\u0433\u0438 (", PROD.tags.length, ") \u25BE"), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 6,
      display: "flex",
      gap: 4,
      flexWrap: "wrap"
    }
  }, PROD.tags.map(t => /*#__PURE__*/React.createElement("span", {
    key: t,
    style: {
      fontSize: 10,
      padding: "1px 6px",
      background: "var(--secondary-100)",
      borderRadius: 999,
      color: "var(--text-light-color)"
    }
  }, t)))))));
}
window.VariantB = VariantB;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/VariantB.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/VoteWidget.jsx
try { (() => {
function VoteWidget({
  myVote = 0,
  fav = false
}) {
  return /*#__PURE__*/React.createElement("span", {
    className: "vote-widget",
    "aria-label": "\u0413\u043E\u043B\u043E\u0441\u043E\u0432\u0430\u0442\u044C"
  }, /*#__PURE__*/React.createElement("button", {
    className: "vote-widget__clear",
    type: "button",
    "aria-label": "\u0421\u043D\u044F\u0442\u044C \u0433\u043E\u043B\u043E\u0441",
    disabled: !myVote
  }, "\u2715"), [1, 2, 3, 4, 5].map(s => /*#__PURE__*/React.createElement("button", {
    key: s,
    type: "button",
    className: "vote-widget__star" + (s <= myVote ? " vote-widget__star--filled" : ""),
    "aria-label": `${s} звёзд`
  }, "\u2605")), myVote ? /*#__PURE__*/React.createElement("span", {
    className: "vote-widget__my"
  }, myVote) : null, /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "vote-widget__heart" + (fav ? " vote-widget__heart--on" : ""),
    "aria-label": fav ? "В избранном" : "В избранное"
  }, "\u2665"));
}
window.VoteWidget = VoteWidget;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/VoteWidget.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/ZxScreen.jsx
try { (() => {
/* Procedural ZX Spectrum-style screen mocks. Every screen renders a deterministic
   pseudo-pixel image using the seed id, so they look distinct without bundling
   real captures. */

function ZxScreen({
  seed = 0,
  palette = "default"
}) {
  // 32×24 pixel grid (Spectrum 256×192 / 8)
  const W = 32,
    H = 24;
  const palettes = {
    default: ["#000", "#0d3b66", "#1a90ff", "#80c2ff", "#ffbd04", "#bb0000", "#9c8c5a", "#fff"],
    sunset: ["#1a0000", "#3d0000", "#bb0000", "#ff3333", "#ff8080", "#ffbd04", "#ffe9ac", "#fff0f0"],
    cool: ["#000f1f", "#001d38", "#005cb3", "#1a90ff", "#4da9ff", "#80c2ff", "#b3daff", "#fff"],
    forest: ["#000", "#0d3b66", "#1a4d2e", "#2d8659", "#5cb380", "#ffbd04", "#fff4d6", "#fff"],
    night: ["#000", "#131313", "#262626", "#404040", "#005cb3", "#0077ee", "#a6a6a6", "#fff"]
  };
  const colors = palettes[palette] || palettes.default;

  // deterministic LCG
  function lcg(s) {
    let x = s + 1;
    return () => {
      x = x * 1664525 + 1013904223 >>> 0;
      return x / 0xffffffff;
    };
  }
  const rnd = lcg(seed * 99991 + 17);

  // build a "scene": top sky band, a horizon line, sprites, ground tiles
  const cells = [];
  const horizon = 8 + Math.floor(rnd() * 6);
  for (let y = 0; y < H; y++) {
    for (let x = 0; x < W; x++) {
      let c;
      if (y < horizon - 1) c = colors[3]; // sky
      else if (y === horizon) c = colors[6]; // horizon line
      else if (y < horizon + 2) c = (x + y) % 3 === 0 ? colors[2] : colors[3]; // distant
      else c = Math.floor(rnd() * 4) === 0 ? colors[1] : colors[2]; // ground
      cells.push({
        x,
        y,
        c
      });
    }
  }
  // stamp a "character" sprite mid-screen
  const cx = 8 + Math.floor(rnd() * (W - 16));
  const cy = horizon - 3;
  const sprite = ["..XX..", ".XYYX.", ".XYYX.", "XYYYYX", "X.XX.X", ".X..X."];
  sprite.forEach((row, j) => {
    [...row].forEach((ch, i) => {
      const idx = (cy + j) * W + (cx + i);
      if (cells[idx]) {
        if (ch === "X") cells[idx].c = colors[0];else if (ch === "Y") cells[idx].c = colors[4];
      }
    });
  });
  // some "stars" / coins
  for (let k = 0; k < 4; k++) {
    const sx = Math.floor(rnd() * W);
    const sy = Math.floor(rnd() * (horizon - 1));
    const idx = sy * W + sx;
    if (cells[idx]) cells[idx].c = colors[7];
  }
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: `0 0 ${W} ${H}`,
    preserveAspectRatio: "none",
    xmlns: "http://www.w3.org/2000/svg"
  }, cells.map((c, i) => /*#__PURE__*/React.createElement("rect", {
    key: i,
    x: c.x,
    y: c.y,
    width: "1",
    height: "1",
    fill: c.c
  })));
}
window.ZxScreen = ZxScreen;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/ZxScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/author-data.jsx
try { (() => {
/* author-data.jsx — mock data for AuthorPage.
   Two presets: "moroz" (chudovischny all-rounder, mostly graphics) and
   "newbie" (two works, no rating yet). */

const AUTHOR_PRESETS = {
  moroz: {
    handle: "moroz1999",
    realName: "Дмитрий",
    location: ["Таллин", "Эстония"],
    roles: ["artist", "musician", "coder"],
    // for the role chips at top
    groups: [{
      name: "dibiliki",
      parent: null,
      years: "1996–наст."
    }, {
      name: "FREEgroup",
      parent: null,
      years: "1998–2004"
    }, {
      name: "RAZOR 1911",
      parent: null,
      years: "2000–2003"
    }, {
      name: "ZX-Spec sub",
      parent: "FREEgroup",
      years: "1999–2001"
    }],
    aliases: ["Moroz", "Dead Moroz", "Jose Luis Pendejo", "mr. bungle", "Мальчик Дима", "Отец хлеба", "Juan González Pendejo Ibáñez", "Автор Работы", "Cosmic stranger of love", "kolbasa-style", "Vitalino", "PharaO", "Абсолютный Мастер Всея Некопии", "Владис", "Vivan", "D.Ivan", "Vovka", "Nurgo", "Antonius", "Borgee", "8bit fan", "TruarT", "Longshooter", "Автор всех работ", "сеньор кшишлав не покупает нашу рыбу", "Well-known Gribofairy"],
    links: [{
      site: "zxaaa.net",
      label: "Страница на zxaaa.net",
      icon: "Z"
    }, {
      site: "spectrumcomputing.co.uk",
      label: "Страница на Spectrum Computing",
      icon: "S"
    }, {
      site: "speccywiki.org",
      label: "Страница на SpeccyWiki",
      icon: "W"
    }],
    tech: {
      palette: "sRGB",
      ayChip: "AY-3-8910 / YM2149F",
      ayChannels: "ACB",
      ayClock: "1.75 МГц (Пентагон)",
      intFreq: "48.828125 Гц (Пентагон)"
    },
    ratings: {
      artist: 457.45,
      musician: 28.12
    },
    counters: {
      pictures: 187,
      tunes: 42,
      prods: 11,
      comments: 318
    },
    badges: ["VIP-спонсор", "Волонтёр"],
    avatar: "pixel",
    // pixelized photo
    siteUser: "moroz1999",
    joined: "2008-03-14"
  },
  newbie: {
    handle: "bytekid",
    realName: "—",
    location: null,
    roles: ["musician"],
    groups: [],
    aliases: ["bk", "byte-kid"],
    links: [{
      site: "speccywiki.org",
      label: "Страница на SpeccyWiki",
      icon: "W"
    }],
    tech: {
      palette: "sRGB",
      ayChip: "AY-3-8910",
      ayChannels: "ABC",
      ayClock: "1.7734 МГц (Спектрум)",
      intFreq: "50.08 Гц (Спектрум)"
    },
    ratings: {
      artist: 0,
      musician: 0
    },
    counters: {
      pictures: 0,
      tunes: 2,
      prods: 0,
      comments: 0
    },
    badges: [],
    avatar: "none",
    siteUser: "bytekid",
    joined: "2026-04-11"
  }
};

/* ── deterministic generators (so pages stay stable across reloads) ── */
function _rnd(seed) {
  let s = seed * 9301 + 49297;
  return () => {
    s = (s * 9301 + 49297) % 233280;
    return s / 233280;
  };
}
const PARTIES = ["Chaos Constructions", "DiHalt", "Forever", "Multimatograf", "CAFePARTY", "Outline", "ArtField", "ZX-Spectrum Demo", "Yandex.Demoscene", null, null];
const PALETTES = ["sunset", "cool", "forest", "night", "default"];
const PIC_FORMATS = ["original", "gigascreen", "multicolor", "sam coupe", "atm2"];
const PIC_TITLES = ["Eternal Flame", "Ten Years After", "Cyber Night", "Pixel Tears", "Old Soldier", "Frozen Land", "Speccy Dreams", "Beyond The Walls", "Star Voyager", "Magic Cube", "Code of Honor", "Cyrillic Spring", "Mr. Boombastic", "Snow Queen", "Robocop", "Last Bastion", "Crystal Garden", "Iron Gate", "Lone Wolf", "Heat Wave", "Twilight", "Sunset Boulevard", "Bittersweet Symphony", "Vector Field", "Polygon Soul", "Bitmap Heart", "Pixel Storm", "Echo", "Resonance", "Stochastic", "Glitch in the Matrix", "Static Noise", "Static Beauty", "Stained Glass", "Untitled", "Untitled II", "Untitled III", "Untitled IV", "Untitled V"];
const TUNE_CHIPS = ["AY", "AY", "AY", "Beeper", "Turbosound"];
const PROD_KINDS = ["Игра", "Демо", "Интро", "Утилита", "Музыкальный диск"];
/* Sub-categories: each top-level kind may have a 2nd-level tag.
   Filter UI shows a tree of top-level and sub-categories. */
const PROD_SUBCATS = {
  "Игра": ["Стрелялка", "Командер", "Квест", "Аркада", "Платформер"],
  "Демо": ["Megademo", "Тех-демо"],
  "Интро": ["64K", "256B", "Cracktro"]
};
const PROD_TITLES = ["Crystal Kingdom Dizzy", "Black Raven 2", "Mighty Final Fight", "Inferno", "Refresh 2", "Wolfenstein 3D Speccy", "Star Heritage", "Black Adder", "Tundra", "ARM-tan", "Dizzy Quest"];
const ROLE_TYPES = {
  music: {
    label: "Музыка",
    icon: "music-note",
    color: "music"
  },
  gfx: {
    label: "Графика",
    icon: "image",
    color: "gfx"
  },
  code: {
    label: "Код",
    icon: "code",
    color: "code"
  },
  intro: {
    label: "Интро к релизу",
    icon: "videogame-asset",
    color: "intro"
  },
  sfx: {
    label: "Звук",
    icon: "music-note",
    color: "music"
  },
  design: {
    label: "Гейм-дизайн",
    icon: "settings",
    color: "code"
  }
};
function genPictures(handle, n) {
  if (n <= 0) return [];
  const r = _rnd(handle.charCodeAt(0) + 3);
  const out = [];
  for (let i = 0; i < n; i++) {
    const year = 1995 + Math.floor(r() * 30);
    const partyIx = Math.floor(r() * PARTIES.length);
    out.push({
      id: 10000 + i,
      title: PIC_TITLES[i % PIC_TITLES.length] + (i >= PIC_TITLES.length ? " " + Math.floor(i / PIC_TITLES.length + 2) : ""),
      year,
      palette: PALETTES[i % PALETTES.length],
      format: PIC_FORMATS[Math.floor(r() * PIC_FORMATS.length)],
      realtime: r() > 0.92,
      flickering: r() > 0.88,
      authors: [handle],
      coAuthors: r() > 0.78 ? ["nq"] : [],
      stars: 3 + Math.floor(r() * 3),
      votes: 4 + Math.floor(r() * 95),
      plays: 200 + Math.floor(r() * 4800),
      /* «запуски» = просмотры для картины */
      downloads: 30 + Math.floor(r() * 400),
      party: PARTIES[partyIx],
      place: PARTIES[partyIx] ? r() > 0.5 ? Math.ceil(r() * 3) : null : null,
      added: year + "-0" + (1 + Math.floor(r() * 9)) + "-1" + Math.floor(r() * 9)
    });
  }
  return out;
}
function genTunes(handle, n) {
  if (n <= 0) return [];
  const r = _rnd(handle.charCodeAt(1) + 7);
  const out = [];
  for (let i = 0; i < n; i++) {
    out.push({
      id: 20000 + i,
      title: PIC_TITLES[(i + 5) % PIC_TITLES.length],
      year: 1995 + Math.floor(r() * 30),
      author: handle,
      chip: TUNE_CHIPS[Math.floor(r() * TUNE_CHIPS.length)],
      duration: 1 + Math.floor(r() * 4) + ":" + String(Math.floor(r() * 60)).padStart(2, "0"),
      stars: 3 + Math.floor(r() * 3),
      votes: 5 + Math.floor(r() * 50),
      plays: 100 + Math.floor(r() * 2400),
      /* «запуски» = прослушивания */
      downloads: 30 + Math.floor(r() * 350)
    });
  }
  return out;
}
function genProds(handle, n) {
  if (n <= 0) return [];
  const r = _rnd(handle.charCodeAt(0) + 13);
  const out = [];
  for (let i = 0; i < n; i++) {
    /* Each prod gets 1-3 roles, sometimes "intro for release X". */
    const rolesPool = ["music", "gfx", "code", "design", "sfx"];
    const roleCount = 1 + Math.floor(r() * 2.4);
    const roles = [];
    while (roles.length < roleCount) {
      const c = rolesPool[Math.floor(r() * rolesPool.length)];
      if (!roles.includes(c)) roles.push(c);
    }
    const introRelease = r() > 0.7 ? "Cracked release v" + (1 + Math.floor(r() * 3)) : null;
    const kind = PROD_KINDS[Math.floor(r() * PROD_KINDS.length)];
    const subPool = PROD_SUBCATS[kind];
    const sub = subPool && r() > 0.35 ? subPool[Math.floor(r() * subPool.length)] : null;
    out.push({
      id: 30000 + i,
      title: PROD_TITLES[i % PROD_TITLES.length] + (i >= PROD_TITLES.length ? " " + (Math.floor(i / PROD_TITLES.length) + 2) : ""),
      kind,
      subKind: sub,
      year: 1996 + Math.floor(r() * 28),
      palette: PALETTES[i % PALETTES.length],
      roles,
      // global roles in the prod
      introRelease,
      // if non-null, additionally intro author for this release
      stars: 3 + Math.floor(r() * 3),
      votes: 5 + Math.floor(r() * 80),
      downloads: 50 + Math.floor(r() * 4900),
      plays: 200 + Math.floor(r() * 6000),
      coAuthors: ["nq", "tiboh", "Skrju", "Diver/4D"].slice(0, 1 + Math.floor(r() * 3))
    });
  }
  return out;
}
const COLLABORATORS = [{
  handle: "nq",
  groups: "Skrju · RetroSouls",
  joint: {
    pictures: 14,
    tunes: 8,
    prods: 4
  },
  years: "1999–2018"
}, {
  handle: "tiboh",
  groups: "debris · AAABand",
  joint: {
    pictures: 7,
    tunes: 12,
    prods: 2
  },
  years: "2005–2023"
}, {
  handle: "Diver/4D",
  groups: "4D",
  joint: {
    pictures: 22,
    tunes: 0,
    prods: 5
  },
  years: "1998–2012"
}, {
  handle: "Riskej",
  groups: "RetroSouls",
  joint: {
    pictures: 4,
    tunes: 6,
    prods: 3
  },
  years: "2014–2022"
}, {
  handle: "Sergey",
  groups: "FREEgroup",
  joint: {
    pictures: 11,
    tunes: 0,
    prods: 1
  },
  years: "1998–2004"
}, {
  handle: "Karbofos",
  groups: "Antares · Kabardin",
  joint: {
    pictures: 3,
    tunes: 9,
    prods: 2
  },
  years: "2008–2021"
}, {
  handle: "Lethargeek",
  groups: "Skrju",
  joint: {
    pictures: 0,
    tunes: 5,
    prods: 4
  },
  years: "2011–2019"
}, {
  handle: "MmcM",
  groups: "Sage",
  joint: {
    pictures: 6,
    tunes: 0,
    prods: 1
  },
  years: "2007–2015"
}];
const COLLAB_GROUPS = [{
  name: "Skrju",
  members: 11,
  ourWorks: 28,
  years: "1998–2024",
  releases: 41
}, {
  name: "RetroSouls",
  members: 6,
  ourWorks: 14,
  years: "2013–2024",
  releases: 22
}, {
  name: "Outsiders",
  members: 4,
  ourWorks: 7,
  years: "2002–2009",
  releases: 11
}, {
  name: "Stardust",
  members: 9,
  ourWorks: 6,
  years: "2005–2014",
  releases: 9
}, {
  name: "Sibcrew",
  members: 5,
  ourWorks: 4,
  years: "2010–2016",
  releases: 6
}];
const RECENT_COMMENTS_RICH = [{
  id: 1,
  by: "diver4d",
  date: "2026-05-19",
  workType: "picture",
  workTitle: "Eternal Flame",
  body: "Это просто шедевр. Зачем ты так с нами?"
}, {
  id: 2,
  by: "voxel",
  date: "2026-05-18",
  workType: "tune",
  workTitle: "Crystal Garden",
  body: "Чип точно AY? Мне кажется, под YM2149 звучит не так."
}, {
  id: 3,
  by: "Riskej",
  date: "2026-05-15",
  workType: "prod",
  workTitle: "Crystal Kingdom Dizzy",
  role: "Музыка",
  body: "Лучшая музыка в Diz-серии. Подскажешь pt3?"
}, {
  id: 4,
  by: "g0blin",
  date: "2026-05-13",
  workType: "picture",
  workTitle: "Cyber Night",
  body: "На моей CGA-карте всё хорошо отрисовывается, спасибо."
}, {
  id: 5,
  by: "Lethargeek",
  date: "2026-05-09",
  workType: "picture",
  workTitle: "Snow Queen",
  body: "Был мульт по мотивам? Очень в стиле."
}, {
  id: 6,
  by: "anonimno",
  date: "2026-05-08",
  workType: "tune",
  workTitle: "Frozen Land",
  body: "→ комментарий удалён модератором"
}, {
  id: 7,
  by: "tiboh",
  date: "2026-05-04",
  workType: "picture",
  workTitle: "Mr. Boombastic",
  body: "Пиксели — лучшее, что было в твоей графике в 2013."
}, {
  id: 8,
  by: "Karbofos",
  date: "2026-05-02",
  workType: "prod",
  workTitle: "Inferno",
  role: "Графика + Код",
  body: "Перепрошёл первый раз. Финал жесть."
}];
const RECENT_VOTES_RICH = [{
  id: 1,
  by: "diver4d",
  date: "2026-05-19",
  workTitle: "Eternal Flame",
  workType: "picture",
  score: 5
}, {
  id: 2,
  by: "Riskej",
  date: "2026-05-19",
  workTitle: "Crystal Garden",
  workType: "tune",
  score: 5
}, {
  id: 3,
  by: "voxel",
  date: "2026-05-18",
  workTitle: "Cyber Night",
  workType: "picture",
  score: 4
}, {
  id: 4,
  by: "tiboh",
  date: "2026-05-17",
  workTitle: "Mr. Boombastic",
  workType: "picture",
  score: 5
}, {
  id: 5,
  by: "g0blin",
  date: "2026-05-15",
  workTitle: "Inferno",
  workType: "prod",
  score: 5
}, {
  id: 6,
  by: "Karbofos",
  date: "2026-05-13",
  workTitle: "Lone Wolf",
  workType: "picture",
  score: 3
}, {
  id: 7,
  by: "Sergey",
  date: "2026-05-11",
  workTitle: "Twilight",
  workType: "picture",
  score: 4
}, {
  id: 8,
  by: "Lethargeek",
  date: "2026-05-08",
  workTitle: "Glitch in the Matrix",
  workType: "picture",
  score: 5
}];
const AUTHOR_WALL_RICH = [{
  id: 1,
  by: "diver4d",
  date: "2026-05-20",
  body: "Слушай, опять видел в SCENE Magazine упоминание твоего интро к Inferno. Поздравляю — заметили!"
}, {
  id: 2,
  by: "tiboh",
  date: "2026-05-12",
  body: "Помнишь Forever 2009? Я наконец нашёл fly-by-кассету. Если интересно — могу прислать рип."
}, {
  id: 3,
  by: "g0blin",
  date: "2026-04-30",
  body: "Спасибо за консультацию по AY-каналам в личке, всё разложилось. У меня теперь чище звучит."
}, {
  id: 4,
  by: "Karbofos",
  date: "2026-04-22",
  body: "Брат, ты живой? Давно тебя не было на DiHalt. Ждём."
}, {
  id: 5,
  by: "newuser92",
  date: "2026-04-15",
  body: "Я тут только начал, посмотрел всю твою графику за вечер. Это ОЧЕНЬ круто. Спасибо."
}, {
  id: 6,
  by: "Riskej",
  date: "2026-03-28",
  body: "За «Eternal Flame» ставлю отдельный респект. Думал, такого тайминга вообще нельзя добиться на 48k."
}];
const AUTHOR_WALL_NEWBIE = [];

/* Build the full data set per preset */
function buildAuthorData(presetKey) {
  const profile = AUTHOR_PRESETS[presetKey];
  return {
    profile,
    pictures: genPictures(profile.handle, profile.counters.pictures),
    tunes: genTunes(profile.handle, profile.counters.tunes),
    prods: genProds(profile.handle, profile.counters.prods),
    collaborators: presetKey === "moroz" ? COLLABORATORS : [],
    collabGroups: presetKey === "moroz" ? COLLAB_GROUPS : [],
    comments: presetKey === "moroz" ? RECENT_COMMENTS_RICH : [],
    votes: presetKey === "moroz" ? RECENT_VOTES_RICH : [],
    wall: presetKey === "moroz" ? AUTHOR_WALL_RICH : AUTHOR_WALL_NEWBIE
  };
}
Object.assign(window, {
  AUTHOR_PRESETS,
  ROLE_TYPES,
  PALETTES,
  PROD_KINDS,
  PROD_SUBCATS,
  buildAuthorData
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/author-data.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/data.jsx
try { (() => {
/** Sample data for the UI kit. Names are real ZX-scene fixtures used illustratively. */
const SAMPLE_PICTURES = [{
  id: 1,
  title: "Eternal Flame",
  authors: ["Diver/4D"],
  party: "Chaos Constructions 2003",
  place: 1,
  year: 2003,
  format: ".SCR",
  stars: 5,
  votes: 42,
  palette: "sunset"
}, {
  id: 2,
  title: "Black Sea Dawn",
  authors: ["Sergey Frolov"],
  party: "DiHalt 2014",
  place: 2,
  year: 2014,
  format: ".SCR",
  stars: 4,
  votes: 28,
  palette: "cool"
}, {
  id: 3,
  title: "Neon Avenue",
  authors: ["Andy/CFM"],
  party: "Forever 2008",
  place: 3,
  year: 2008,
  format: ".MC",
  stars: 4,
  votes: 17,
  palette: "night"
}, {
  id: 4,
  title: "Last Battle",
  authors: ["Riskej/4th Dimension"],
  party: "Chaos Constructions 1999",
  place: 1,
  year: 1999,
  format: ".SCR",
  flickering: true,
  stars: 5,
  votes: 64,
  palette: "sunset"
}, {
  id: 5,
  title: "Forest Whisper",
  authors: ["g0blinish"],
  year: 2019,
  format: ".SCR",
  stars: 3,
  votes: 9,
  palette: "forest"
}, {
  id: 6,
  title: "Spectrum Skyline",
  authors: ["Demiurge Ash", "Frog/CPU"],
  party: "Multimatograf 2017",
  place: 2,
  year: 2017,
  format: ".SCR",
  realtime: true,
  stars: 4,
  votes: 22,
  palette: "cool"
}, {
  id: 7,
  title: "Magnetic Fields",
  authors: ["Diver/4D"],
  year: 2005,
  format: ".SCR",
  stars: 5,
  votes: 31,
  palette: "night"
}, {
  id: 8,
  title: "Cosmonaut #7",
  authors: ["Hellboj"],
  party: "Chaos Constructions 2010",
  place: 1,
  year: 2010,
  format: ".SCR",
  stars: 4,
  votes: 18,
  palette: "default"
}];
const SAMPLE_TUNES = [{
  id: 1,
  title: "Robocop",
  author: "Tim Follin",
  chip: "AY",
  duration: "2:14"
}, {
  id: 2,
  title: "Last V8",
  author: "David Whittaker",
  chip: "Beeper",
  duration: "1:48"
}, {
  id: 3,
  title: "Aquaplane",
  author: "Rob Hubbard",
  chip: "AY",
  duration: "3:02"
}, {
  id: 4,
  title: "Cobra",
  author: "Jonathan Dunn",
  chip: "Beeper",
  duration: "2:21"
}, {
  id: 5,
  title: "Storm Lord",
  author: "Tim Follin",
  chip: "AY",
  duration: "3:48"
}, {
  id: 6,
  title: "Chronos",
  author: "Bjarne Christensen",
  chip: "AY",
  duration: "2:55"
}, {
  id: 7,
  title: "Game Over",
  author: "Marc Wilding",
  chip: "Beeper",
  duration: "1:32"
}, {
  id: 8,
  title: "Savage",
  author: "Adam Gilmore",
  chip: "AY",
  duration: "2:08"
}];
const SAMPLE_PRODS = [{
  id: 101,
  title: "Forever 96k",
  authors: ["DiHalt", "Outsiders"],
  kind: "demo",
  party: "Chaos Constructions 2018",
  place: 1,
  year: 2018,
  stars: 5,
  votes: 73,
  palette: "night"
}, {
  id: 102,
  title: "Castlevania ZX",
  authors: ["g0blinish"],
  kind: "game",
  year: 2021,
  stars: 4,
  votes: 19,
  palette: "sunset"
}, {
  id: 103,
  title: "AY-Tracker 0.91",
  authors: ["Sergey Bulba"],
  kind: "tool",
  year: 2007,
  stars: 5,
  votes: 41,
  palette: "cool"
}, {
  id: 104,
  title: "Eka 4k Intro",
  authors: ["Riskej"],
  kind: "intro",
  party: "Multimatograf 2016",
  place: 2,
  year: 2016,
  stars: 4,
  votes: 12,
  palette: "forest"
}];
Object.assign(window, {
  SAMPLE_PICTURES,
  SAMPLE_TUNES,
  SAMPLE_PRODS
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/data.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/design-canvas.jsx
try { (() => {
// DesignCanvas.jsx — Figma-ish design canvas wrapper
// Warm gray grid bg + Sections + Artboards + PostIt notes.
// Artboards are reorderable (grip-drag), labels/titles are inline-editable,
// and any artboard can be opened in a fullscreen focus overlay (←/→/Esc).
// State persists to a .design-canvas.state.json sidecar via the host
// bridge. No assets, no deps.
//
// Usage:
//   <DesignCanvas>
//     <DCSection id="onboarding" title="Onboarding" subtitle="First-run variants">
//       <DCArtboard id="a" label="A · Dusk" width={260} height={480}>…</DCArtboard>
//       <DCArtboard id="b" label="B · Minimal" width={260} height={480}>…</DCArtboard>
//     </DCSection>
//   </DesignCanvas>

const DC = {
  bg: '#f0eee9',
  grid: 'rgba(0,0,0,0.06)',
  label: 'rgba(60,50,40,0.7)',
  title: 'rgba(40,30,20,0.85)',
  subtitle: 'rgba(60,50,40,0.6)',
  postitBg: '#fef4a8',
  postitText: '#5a4a2a',
  font: '-apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif'
};

// One-time CSS injection (classes are dc-prefixed so they don't collide with
// the hosted design's own styles).
if (typeof document !== 'undefined' && !document.getElementById('dc-styles')) {
  const s = document.createElement('style');
  s.id = 'dc-styles';
  s.textContent = ['.dc-editable{cursor:text;outline:none;white-space:nowrap;border-radius:3px;padding:0 2px;margin:0 -2px}', '.dc-editable:focus{background:#fff;box-shadow:0 0 0 1.5px #c96442}', '[data-dc-slot]{transition:transform .18s cubic-bezier(.2,.7,.3,1)}', '[data-dc-slot].dc-dragging{transition:none;z-index:10;pointer-events:none}', '[data-dc-slot].dc-dragging .dc-card{box-shadow:0 12px 40px rgba(0,0,0,.25),0 0 0 2px #c96442;transform:scale(1.02)}', '.dc-card{transition:box-shadow .15s,transform .15s}', '.dc-card *{scrollbar-width:none}', '.dc-card *::-webkit-scrollbar{display:none}', '.dc-labelrow{display:flex;align-items:center;gap:4px;height:24px}', '.dc-grip{cursor:grab;display:flex;align-items:center;padding:5px 4px;border-radius:4px;transition:background .12s}', '.dc-grip:hover{background:rgba(0,0,0,.08)}', '.dc-grip:active{cursor:grabbing}', '.dc-labeltext{cursor:pointer;border-radius:4px;padding:3px 6px;display:flex;align-items:center;transition:background .12s}', '.dc-labeltext:hover{background:rgba(0,0,0,.05)}', '.dc-expand{position:absolute;bottom:100%;right:0;margin-bottom:5px;z-index:2;opacity:0;transition:opacity .12s,background .12s;', '  width:22px;height:22px;border-radius:5px;border:none;cursor:pointer;padding:0;', '  background:transparent;color:rgba(60,50,40,.7);display:flex;align-items:center;justify-content:center}', '.dc-expand:hover{background:rgba(0,0,0,.06);color:#2a251f}', '[data-dc-slot]:hover .dc-expand{opacity:1}'].join('\n');
  document.head.appendChild(s);
}
const DCCtx = React.createContext(null);

// ─────────────────────────────────────────────────────────────
// DesignCanvas — stateful wrapper around the pan/zoom viewport.
// Owns runtime state (per-section order, renamed titles/labels, focused
// artboard). Order/titles/labels persist to a .design-canvas.state.json
// sidecar next to the HTML. Reads go via plain fetch() so the saved
// arrangement is visible anywhere the HTML + sidecar are served together
// (omelette preview, direct link, downloaded zip). Writes go through the
// host's window.omelette bridge — editing requires the omelette runtime.
// Focus is ephemeral.
// ─────────────────────────────────────────────────────────────
const DC_STATE_FILE = '.design-canvas.state.json';
function DesignCanvas({
  children,
  minScale,
  maxScale,
  style
}) {
  const [state, setState] = React.useState({
    sections: {},
    focus: null
  });
  // Hold rendering until the sidecar read settles so the saved order/titles
  // appear on first paint (no source-order flash). didRead gates writes until
  // the read settles so the empty initial state can't clobber a slow read;
  // skipNextWrite suppresses the one echo-write that would otherwise follow
  // hydration.
  const [ready, setReady] = React.useState(false);
  const didRead = React.useRef(false);
  const skipNextWrite = React.useRef(false);
  React.useEffect(() => {
    let off = false;
    fetch('./' + DC_STATE_FILE).then(r => r.ok ? r.json() : null).then(saved => {
      if (off || !saved || !saved.sections) return;
      skipNextWrite.current = true;
      setState(s => ({
        ...s,
        sections: saved.sections
      }));
    }).catch(() => {}).finally(() => {
      didRead.current = true;
      if (!off) setReady(true);
    });
    const t = setTimeout(() => {
      if (!off) setReady(true);
    }, 150);
    return () => {
      off = true;
      clearTimeout(t);
    };
  }, []);
  React.useEffect(() => {
    if (!didRead.current) return;
    if (skipNextWrite.current) {
      skipNextWrite.current = false;
      return;
    }
    const t = setTimeout(() => {
      window.omelette?.writeFile(DC_STATE_FILE, JSON.stringify({
        sections: state.sections
      })).catch(() => {});
    }, 250);
    return () => clearTimeout(t);
  }, [state.sections]);

  // Build registries synchronously from children so FocusOverlay can read
  // them in the same render. Only direct DCSection > DCArtboard children are
  // walked — wrapping them in other elements opts out of focus/reorder.
  const registry = {}; // slotId -> { sectionId, artboard }
  const sectionMeta = {}; // sectionId -> { title, subtitle, slotIds[] }
  const sectionOrder = [];
  React.Children.forEach(children, sec => {
    if (!sec || sec.type !== DCSection) return;
    const sid = sec.props.id ?? sec.props.title;
    if (!sid) return;
    sectionOrder.push(sid);
    const persisted = state.sections[sid] || {};
    const srcIds = [];
    React.Children.forEach(sec.props.children, ab => {
      if (!ab || ab.type !== DCArtboard) return;
      const aid = ab.props.id ?? ab.props.label;
      if (!aid) return;
      registry[`${sid}/${aid}`] = {
        sectionId: sid,
        artboard: ab
      };
      srcIds.push(aid);
    });
    const kept = (persisted.order || []).filter(k => srcIds.includes(k));
    sectionMeta[sid] = {
      title: persisted.title ?? sec.props.title,
      subtitle: sec.props.subtitle,
      slotIds: [...kept, ...srcIds.filter(k => !kept.includes(k))]
    };
  });
  const api = React.useMemo(() => ({
    state,
    section: id => state.sections[id] || {},
    patchSection: (id, p) => setState(s => ({
      ...s,
      sections: {
        ...s.sections,
        [id]: {
          ...s.sections[id],
          ...(typeof p === 'function' ? p(s.sections[id] || {}) : p)
        }
      }
    })),
    setFocus: slotId => setState(s => ({
      ...s,
      focus: slotId
    }))
  }), [state]);

  // Esc exits focus; any outside pointerdown commits an in-progress rename.
  React.useEffect(() => {
    const onKey = e => {
      if (e.key === 'Escape') api.setFocus(null);
    };
    const onPd = e => {
      const ae = document.activeElement;
      if (ae && ae.isContentEditable && !ae.contains(e.target)) ae.blur();
    };
    document.addEventListener('keydown', onKey);
    document.addEventListener('pointerdown', onPd, true);
    return () => {
      document.removeEventListener('keydown', onKey);
      document.removeEventListener('pointerdown', onPd, true);
    };
  }, [api]);
  return /*#__PURE__*/React.createElement(DCCtx.Provider, {
    value: api
  }, /*#__PURE__*/React.createElement(DCViewport, {
    minScale: minScale,
    maxScale: maxScale,
    style: style
  }, ready && children), state.focus && registry[state.focus] && /*#__PURE__*/React.createElement(DCFocusOverlay, {
    entry: registry[state.focus],
    sectionMeta: sectionMeta,
    sectionOrder: sectionOrder
  }));
}

// ─────────────────────────────────────────────────────────────
// DCViewport — transform-based pan/zoom (internal)
//
// Input mapping (Figma-style):
//   • trackpad pinch  → zoom   (ctrlKey wheel; Safari gesture* events)
//   • trackpad scroll → pan    (two-finger)
//   • mouse wheel     → zoom   (notched; distinguished from trackpad scroll)
//   • middle-drag / primary-drag-on-bg → pan
//
// Transform state lives in a ref and is written straight to the DOM
// (translate3d + will-change) so wheel ticks don't go through React —
// keeps pans at 60fps on dense canvases.
// ─────────────────────────────────────────────────────────────
function DCViewport({
  children,
  minScale = 0.1,
  maxScale = 8,
  style = {}
}) {
  const vpRef = React.useRef(null);
  const worldRef = React.useRef(null);
  const tf = React.useRef({
    x: 0,
    y: 0,
    scale: 1
  });
  const apply = React.useCallback(() => {
    const {
      x,
      y,
      scale
    } = tf.current;
    const el = worldRef.current;
    if (el) el.style.transform = `translate3d(${x}px, ${y}px, 0) scale(${scale})`;
  }, []);
  React.useEffect(() => {
    const vp = vpRef.current;
    if (!vp) return;
    const zoomAt = (cx, cy, factor) => {
      const r = vp.getBoundingClientRect();
      const px = cx - r.left,
        py = cy - r.top;
      const t = tf.current;
      const next = Math.min(maxScale, Math.max(minScale, t.scale * factor));
      const k = next / t.scale;
      // keep the world point under the cursor fixed
      t.x = px - (px - t.x) * k;
      t.y = py - (py - t.y) * k;
      t.scale = next;
      apply();
    };

    // Mouse-wheel vs trackpad-scroll heuristic. A physical wheel sends
    // line-mode deltas (Firefox) or large integer pixel deltas with no X
    // component (Chrome/Safari, typically multiples of 100/120). Trackpad
    // two-finger scroll sends small/fractional pixel deltas, often with
    // non-zero deltaX. ctrlKey is set by the browser for trackpad pinch.
    const isMouseWheel = e => e.deltaMode !== 0 || e.deltaX === 0 && Number.isInteger(e.deltaY) && Math.abs(e.deltaY) >= 40;
    const onWheel = e => {
      e.preventDefault();
      if (isGesturing) return; // Safari: gesture* owns the pinch — discard concurrent wheels
      if (e.ctrlKey) {
        // trackpad pinch (or explicit ctrl+wheel)
        zoomAt(e.clientX, e.clientY, Math.exp(-e.deltaY * 0.01));
      } else if (isMouseWheel(e)) {
        // notched mouse wheel — fixed-ratio step per click
        zoomAt(e.clientX, e.clientY, Math.exp(-Math.sign(e.deltaY) * 0.18));
      } else {
        // trackpad two-finger scroll — pan
        tf.current.x -= e.deltaX;
        tf.current.y -= e.deltaY;
        apply();
      }
    };

    // Safari sends native gesture* events for trackpad pinch with a smooth
    // e.scale; preferring these over the ctrl+wheel fallback gives a much
    // better feel there. No-ops on other browsers. Safari also fires
    // ctrlKey wheel events during the same pinch — isGesturing makes
    // onWheel drop those entirely so they neither zoom nor pan.
    let gsBase = 1;
    let isGesturing = false;
    const onGestureStart = e => {
      e.preventDefault();
      isGesturing = true;
      gsBase = tf.current.scale;
    };
    const onGestureChange = e => {
      e.preventDefault();
      zoomAt(e.clientX, e.clientY, gsBase * e.scale / tf.current.scale);
    };
    const onGestureEnd = e => {
      e.preventDefault();
      isGesturing = false;
    };

    // Drag-pan: middle button anywhere, or primary button on canvas
    // background (anything that isn't an artboard or an inline editor).
    let drag = null;
    const onPointerDown = e => {
      const onBg = !e.target.closest('[data-dc-slot], .dc-editable');
      if (!(e.button === 1 || e.button === 0 && onBg)) return;
      e.preventDefault();
      vp.setPointerCapture(e.pointerId);
      drag = {
        id: e.pointerId,
        lx: e.clientX,
        ly: e.clientY
      };
      vp.style.cursor = 'grabbing';
    };
    const onPointerMove = e => {
      if (!drag || e.pointerId !== drag.id) return;
      tf.current.x += e.clientX - drag.lx;
      tf.current.y += e.clientY - drag.ly;
      drag.lx = e.clientX;
      drag.ly = e.clientY;
      apply();
    };
    const onPointerUp = e => {
      if (!drag || e.pointerId !== drag.id) return;
      vp.releasePointerCapture(e.pointerId);
      drag = null;
      vp.style.cursor = '';
    };
    vp.addEventListener('wheel', onWheel, {
      passive: false
    });
    vp.addEventListener('gesturestart', onGestureStart, {
      passive: false
    });
    vp.addEventListener('gesturechange', onGestureChange, {
      passive: false
    });
    vp.addEventListener('gestureend', onGestureEnd, {
      passive: false
    });
    vp.addEventListener('pointerdown', onPointerDown);
    vp.addEventListener('pointermove', onPointerMove);
    vp.addEventListener('pointerup', onPointerUp);
    vp.addEventListener('pointercancel', onPointerUp);
    return () => {
      vp.removeEventListener('wheel', onWheel);
      vp.removeEventListener('gesturestart', onGestureStart);
      vp.removeEventListener('gesturechange', onGestureChange);
      vp.removeEventListener('gestureend', onGestureEnd);
      vp.removeEventListener('pointerdown', onPointerDown);
      vp.removeEventListener('pointermove', onPointerMove);
      vp.removeEventListener('pointerup', onPointerUp);
      vp.removeEventListener('pointercancel', onPointerUp);
    };
  }, [apply, minScale, maxScale]);
  const gridSvg = `url("data:image/svg+xml,%3Csvg width='120' height='120' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M120 0H0v120' fill='none' stroke='${encodeURIComponent(DC.grid)}' stroke-width='1'/%3E%3C/svg%3E")`;
  return /*#__PURE__*/React.createElement("div", {
    ref: vpRef,
    className: "design-canvas",
    style: {
      height: '100vh',
      width: '100vw',
      background: DC.bg,
      overflow: 'hidden',
      overscrollBehavior: 'none',
      touchAction: 'none',
      position: 'relative',
      fontFamily: DC.font,
      boxSizing: 'border-box',
      ...style
    }
  }, /*#__PURE__*/React.createElement("div", {
    ref: worldRef,
    style: {
      position: 'absolute',
      top: 0,
      left: 0,
      transformOrigin: '0 0',
      willChange: 'transform',
      width: 'max-content',
      minWidth: '100%',
      minHeight: '100%',
      padding: '60px 0 80px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      inset: -6000,
      backgroundImage: gridSvg,
      backgroundSize: '120px 120px',
      pointerEvents: 'none',
      zIndex: -1
    }
  }), children));
}

// ─────────────────────────────────────────────────────────────
// DCSection — editable title + h-row of artboards in persisted order
// ─────────────────────────────────────────────────────────────
function DCSection({
  id,
  title,
  subtitle,
  children,
  gap = 48
}) {
  const ctx = React.useContext(DCCtx);
  const sid = id ?? title;
  const all = React.Children.toArray(children);
  const artboards = all.filter(c => c && c.type === DCArtboard);
  const rest = all.filter(c => !(c && c.type === DCArtboard));
  const srcOrder = artboards.map(a => a.props.id ?? a.props.label);
  const sec = ctx && sid && ctx.section(sid) || {};
  const order = React.useMemo(() => {
    const kept = (sec.order || []).filter(k => srcOrder.includes(k));
    return [...kept, ...srcOrder.filter(k => !kept.includes(k))];
  }, [sec.order, srcOrder.join('|')]);
  const byId = Object.fromEntries(artboards.map(a => [a.props.id ?? a.props.label, a]));
  return /*#__PURE__*/React.createElement("div", {
    "data-dc-section": sid,
    style: {
      marginBottom: 80,
      position: 'relative'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      padding: '0 60px 56px'
    }
  }, /*#__PURE__*/React.createElement(DCEditable, {
    tag: "div",
    value: sec.title ?? title,
    onChange: v => ctx && sid && ctx.patchSection(sid, {
      title: v
    }),
    style: {
      fontSize: 28,
      fontWeight: 600,
      color: DC.title,
      letterSpacing: -0.4,
      marginBottom: 6,
      display: 'inline-block'
    }
  }), subtitle && /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: 16,
      color: DC.subtitle
    }
  }, subtitle)), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap,
      padding: '0 60px',
      alignItems: 'flex-start',
      width: 'max-content'
    }
  }, order.map(k => /*#__PURE__*/React.createElement(DCArtboardFrame, {
    key: k,
    sectionId: sid,
    artboard: byId[k],
    order: order,
    label: (sec.labels || {})[k] ?? byId[k].props.label,
    onRename: v => ctx && ctx.patchSection(sid, x => ({
      labels: {
        ...x.labels,
        [k]: v
      }
    })),
    onReorder: next => ctx && ctx.patchSection(sid, {
      order: next
    }),
    onFocus: () => ctx && ctx.setFocus(`${sid}/${k}`)
  }))), rest);
}

// DCArtboard — marker; rendered by DCArtboardFrame via DCSection.
function DCArtboard() {
  return null;
}
function DCArtboardFrame({
  sectionId,
  artboard,
  label,
  order,
  onRename,
  onReorder,
  onFocus
}) {
  const {
    id: rawId,
    label: rawLabel,
    width = 260,
    height = 480,
    children,
    style = {}
  } = artboard.props;
  const id = rawId ?? rawLabel;
  const ref = React.useRef(null);

  // Live drag-reorder: dragged card sticks to cursor; siblings slide into
  // their would-be slots in real time via transforms. DOM order only
  // changes on drop.
  const onGripDown = e => {
    e.preventDefault();
    e.stopPropagation();
    const me = ref.current;
    // translateX is applied in local (pre-scale) space but pointer deltas and
    // getBoundingClientRect().left are screen-space — divide by the viewport's
    // current scale so the dragged card tracks the cursor at any zoom level.
    const scale = me.getBoundingClientRect().width / me.offsetWidth || 1;
    const peers = Array.from(document.querySelectorAll(`[data-dc-section="${sectionId}"] [data-dc-slot]`));
    const homes = peers.map(el => ({
      el,
      id: el.dataset.dcSlot,
      x: el.getBoundingClientRect().left
    }));
    const slotXs = homes.map(h => h.x);
    const startIdx = order.indexOf(id);
    const startX = e.clientX;
    let liveOrder = order.slice();
    me.classList.add('dc-dragging');
    const layout = () => {
      for (const h of homes) {
        if (h.id === id) continue;
        const slot = liveOrder.indexOf(h.id);
        h.el.style.transform = `translateX(${(slotXs[slot] - h.x) / scale}px)`;
      }
    };
    const move = ev => {
      const dx = ev.clientX - startX;
      me.style.transform = `translateX(${dx / scale}px)`;
      const cur = homes[startIdx].x + dx;
      let nearest = 0,
        best = Infinity;
      for (let i = 0; i < slotXs.length; i++) {
        const d = Math.abs(slotXs[i] - cur);
        if (d < best) {
          best = d;
          nearest = i;
        }
      }
      if (liveOrder.indexOf(id) !== nearest) {
        liveOrder = order.filter(k => k !== id);
        liveOrder.splice(nearest, 0, id);
        layout();
      }
    };
    const up = () => {
      document.removeEventListener('pointermove', move);
      document.removeEventListener('pointerup', up);
      const finalSlot = liveOrder.indexOf(id);
      me.classList.remove('dc-dragging');
      me.style.transform = `translateX(${(slotXs[finalSlot] - homes[startIdx].x) / scale}px)`;
      // After the settle transition, kill transitions + clear transforms +
      // commit the reorder in the same frame so there's no visual snap-back.
      setTimeout(() => {
        for (const h of homes) {
          h.el.style.transition = 'none';
          h.el.style.transform = '';
        }
        if (liveOrder.join('|') !== order.join('|')) onReorder(liveOrder);
        requestAnimationFrame(() => requestAnimationFrame(() => {
          for (const h of homes) h.el.style.transition = '';
        }));
      }, 180);
    };
    document.addEventListener('pointermove', move);
    document.addEventListener('pointerup', up);
  };
  return /*#__PURE__*/React.createElement("div", {
    ref: ref,
    "data-dc-slot": id,
    style: {
      position: 'relative',
      flexShrink: 0
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "dc-labelrow",
    style: {
      position: 'absolute',
      bottom: '100%',
      left: -4,
      marginBottom: 4,
      color: DC.label
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "dc-grip",
    onPointerDown: onGripDown,
    title: "Drag to reorder"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "9",
    height: "13",
    viewBox: "0 0 9 13",
    fill: "currentColor"
  }, /*#__PURE__*/React.createElement("circle", {
    cx: "2",
    cy: "2",
    r: "1.1"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "7",
    cy: "2",
    r: "1.1"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "2",
    cy: "6.5",
    r: "1.1"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "7",
    cy: "6.5",
    r: "1.1"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "2",
    cy: "11",
    r: "1.1"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "7",
    cy: "11",
    r: "1.1"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "dc-labeltext",
    onClick: onFocus,
    title: "Click to focus"
  }, /*#__PURE__*/React.createElement(DCEditable, {
    value: label,
    onChange: onRename,
    onClick: e => e.stopPropagation(),
    style: {
      fontSize: 15,
      fontWeight: 500,
      color: DC.label,
      lineHeight: 1
    }
  }))), /*#__PURE__*/React.createElement("button", {
    className: "dc-expand",
    onClick: onFocus,
    onPointerDown: e => e.stopPropagation(),
    title: "Focus"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "12",
    height: "12",
    viewBox: "0 0 12 12",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "1.6",
    strokeLinecap: "round"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M7 1h4v4M5 11H1V7M11 1L7.5 4.5M1 11l3.5-3.5"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "dc-card",
    style: {
      borderRadius: 2,
      boxShadow: '0 1px 3px rgba(0,0,0,.08),0 4px 16px rgba(0,0,0,.06)',
      overflow: 'hidden',
      width,
      height,
      background: '#fff',
      ...style
    }
  }, children || /*#__PURE__*/React.createElement("div", {
    style: {
      height: '100%',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      color: '#bbb',
      fontSize: 13,
      fontFamily: DC.font
    }
  }, id)));
}

// Inline rename — commits on blur or Enter.
function DCEditable({
  value,
  onChange,
  style,
  tag = 'span',
  onClick
}) {
  const T = tag;
  return /*#__PURE__*/React.createElement(T, {
    className: "dc-editable",
    contentEditable: true,
    suppressContentEditableWarning: true,
    onClick: onClick,
    onPointerDown: e => e.stopPropagation(),
    onBlur: e => onChange && onChange(e.currentTarget.textContent),
    onKeyDown: e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        e.currentTarget.blur();
      }
    },
    style: style
  }, value);
}

// ─────────────────────────────────────────────────────────────
// Focus mode — overlay one artboard; ←/→ within section, ↑/↓ across
// sections, Esc or backdrop click to exit.
// ─────────────────────────────────────────────────────────────
function DCFocusOverlay({
  entry,
  sectionMeta,
  sectionOrder
}) {
  const ctx = React.useContext(DCCtx);
  const {
    sectionId,
    artboard
  } = entry;
  const sec = ctx.section(sectionId);
  const meta = sectionMeta[sectionId];
  const peers = meta.slotIds;
  const aid = artboard.props.id ?? artboard.props.label;
  const idx = peers.indexOf(aid);
  const secIdx = sectionOrder.indexOf(sectionId);
  const go = d => {
    const n = peers[(idx + d + peers.length) % peers.length];
    if (n) ctx.setFocus(`${sectionId}/${n}`);
  };
  const goSection = d => {
    const ns = sectionOrder[(secIdx + d + sectionOrder.length) % sectionOrder.length];
    const first = sectionMeta[ns] && sectionMeta[ns].slotIds[0];
    if (first) ctx.setFocus(`${ns}/${first}`);
  };
  React.useEffect(() => {
    const k = e => {
      if (e.key === 'ArrowLeft') {
        e.preventDefault();
        go(-1);
      }
      if (e.key === 'ArrowRight') {
        e.preventDefault();
        go(1);
      }
      if (e.key === 'ArrowUp') {
        e.preventDefault();
        goSection(-1);
      }
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        goSection(1);
      }
    };
    document.addEventListener('keydown', k);
    return () => document.removeEventListener('keydown', k);
  });
  const {
    width = 260,
    height = 480,
    children
  } = artboard.props;
  const [vp, setVp] = React.useState({
    w: window.innerWidth,
    h: window.innerHeight
  });
  React.useEffect(() => {
    const r = () => setVp({
      w: window.innerWidth,
      h: window.innerHeight
    });
    window.addEventListener('resize', r);
    return () => window.removeEventListener('resize', r);
  }, []);
  const scale = Math.max(0.1, Math.min((vp.w - 200) / width, (vp.h - 260) / height, 2));
  const [ddOpen, setDd] = React.useState(false);
  const Arrow = ({
    dir,
    onClick
  }) => /*#__PURE__*/React.createElement("button", {
    onClick: e => {
      e.stopPropagation();
      onClick();
    },
    style: {
      position: 'absolute',
      top: '50%',
      [dir]: 28,
      transform: 'translateY(-50%)',
      border: 'none',
      background: 'rgba(255,255,255,.08)',
      color: 'rgba(255,255,255,.9)',
      width: 44,
      height: 44,
      borderRadius: 22,
      fontSize: 18,
      cursor: 'pointer',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      transition: 'background .15s'
    },
    onMouseEnter: e => e.currentTarget.style.background = 'rgba(255,255,255,.18)',
    onMouseLeave: e => e.currentTarget.style.background = 'rgba(255,255,255,.08)'
  }, /*#__PURE__*/React.createElement("svg", {
    width: "18",
    height: "18",
    viewBox: "0 0 18 18",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "2",
    strokeLinecap: "round"
  }, /*#__PURE__*/React.createElement("path", {
    d: dir === 'left' ? 'M11 3L5 9l6 6' : 'M7 3l6 6-6 6'
  })));

  // Portal to body so position:fixed is the real viewport regardless of any
  // transform on DesignCanvas's ancestors (including the canvas zoom itself).
  return ReactDOM.createPortal(/*#__PURE__*/React.createElement("div", {
    onClick: () => ctx.setFocus(null),
    onWheel: e => e.preventDefault(),
    style: {
      position: 'fixed',
      inset: 0,
      zIndex: 100,
      background: 'rgba(24,20,16,.6)',
      backdropFilter: 'blur(14px)',
      fontFamily: DC.font,
      color: '#fff'
    }
  }, /*#__PURE__*/React.createElement("div", {
    onClick: e => e.stopPropagation(),
    style: {
      position: 'absolute',
      top: 0,
      left: 0,
      right: 0,
      height: 72,
      display: 'flex',
      alignItems: 'flex-start',
      padding: '16px 20px 0',
      gap: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'relative'
    }
  }, /*#__PURE__*/React.createElement("button", {
    onClick: () => setDd(o => !o),
    style: {
      border: 'none',
      background: 'transparent',
      color: '#fff',
      cursor: 'pointer',
      padding: '6px 8px',
      borderRadius: 6,
      textAlign: 'left',
      fontFamily: 'inherit'
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 8
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 18,
      fontWeight: 600,
      letterSpacing: -0.3
    }
  }, meta.title), /*#__PURE__*/React.createElement("svg", {
    width: "11",
    height: "11",
    viewBox: "0 0 11 11",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "1.8",
    strokeLinecap: "round",
    style: {
      opacity: .7
    }
  }, /*#__PURE__*/React.createElement("path", {
    d: "M2 4l3.5 3.5L9 4"
  }))), meta.subtitle && /*#__PURE__*/React.createElement("span", {
    style: {
      display: 'block',
      fontSize: 13,
      opacity: .6,
      fontWeight: 400,
      marginTop: 2
    }
  }, meta.subtitle)), ddOpen && /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: '100%',
      left: 0,
      marginTop: 4,
      background: '#2a251f',
      borderRadius: 8,
      boxShadow: '0 8px 32px rgba(0,0,0,.4)',
      padding: 4,
      minWidth: 200,
      zIndex: 10
    }
  }, sectionOrder.map(sid => /*#__PURE__*/React.createElement("button", {
    key: sid,
    onClick: () => {
      setDd(false);
      const f = sectionMeta[sid].slotIds[0];
      if (f) ctx.setFocus(`${sid}/${f}`);
    },
    style: {
      display: 'block',
      width: '100%',
      textAlign: 'left',
      border: 'none',
      cursor: 'pointer',
      background: sid === sectionId ? 'rgba(255,255,255,.1)' : 'transparent',
      color: '#fff',
      padding: '8px 12px',
      borderRadius: 5,
      fontSize: 14,
      fontWeight: sid === sectionId ? 600 : 400,
      fontFamily: 'inherit'
    }
  }, sectionMeta[sid].title)))), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1
    }
  }), /*#__PURE__*/React.createElement("button", {
    onClick: () => ctx.setFocus(null),
    onMouseEnter: e => e.currentTarget.style.background = 'rgba(255,255,255,.12)',
    onMouseLeave: e => e.currentTarget.style.background = 'transparent',
    style: {
      border: 'none',
      background: 'transparent',
      color: 'rgba(255,255,255,.7)',
      width: 32,
      height: 32,
      borderRadius: 16,
      fontSize: 20,
      cursor: 'pointer',
      lineHeight: 1,
      transition: 'background .12s'
    }
  }, "\xD7")), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: 64,
      bottom: 56,
      left: 100,
      right: 100,
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      justifyContent: 'center',
      gap: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    onClick: e => e.stopPropagation(),
    style: {
      width: width * scale,
      height: height * scale,
      position: 'relative'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      width,
      height,
      transform: `scale(${scale})`,
      transformOrigin: 'top left',
      background: '#fff',
      borderRadius: 2,
      overflow: 'hidden',
      boxShadow: '0 20px 80px rgba(0,0,0,.4)'
    }
  }, children || /*#__PURE__*/React.createElement("div", {
    style: {
      height: '100%',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      color: '#bbb'
    }
  }, aid))), /*#__PURE__*/React.createElement("div", {
    onClick: e => e.stopPropagation(),
    style: {
      fontSize: 14,
      fontWeight: 500,
      opacity: .85,
      textAlign: 'center'
    }
  }, (sec.labels || {})[aid] ?? artboard.props.label, /*#__PURE__*/React.createElement("span", {
    style: {
      opacity: .5,
      marginLeft: 10,
      fontVariantNumeric: 'tabular-nums'
    }
  }, idx + 1, " / ", peers.length))), /*#__PURE__*/React.createElement(Arrow, {
    dir: "left",
    onClick: () => go(-1)
  }), /*#__PURE__*/React.createElement(Arrow, {
    dir: "right",
    onClick: () => go(1)
  }), /*#__PURE__*/React.createElement("div", {
    onClick: e => e.stopPropagation(),
    style: {
      position: 'absolute',
      bottom: 20,
      left: '50%',
      transform: 'translateX(-50%)',
      display: 'flex',
      gap: 8
    }
  }, peers.map((p, i) => /*#__PURE__*/React.createElement("button", {
    key: p,
    onClick: () => ctx.setFocus(`${sectionId}/${p}`),
    style: {
      border: 'none',
      padding: 0,
      cursor: 'pointer',
      width: 6,
      height: 6,
      borderRadius: 3,
      background: i === idx ? '#fff' : 'rgba(255,255,255,.3)'
    }
  })))), document.body);
}

// ─────────────────────────────────────────────────────────────
// Post-it — absolute-positioned sticky note
// ─────────────────────────────────────────────────────────────
function DCPostIt({
  children,
  top,
  left,
  right,
  bottom,
  rotate = -2,
  width = 180
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top,
      left,
      right,
      bottom,
      width,
      background: DC.postitBg,
      padding: '14px 16px',
      fontFamily: '"Comic Sans MS", "Marker Felt", "Segoe Print", cursive',
      fontSize: 14,
      lineHeight: 1.4,
      color: DC.postitText,
      boxShadow: '0 2px 8px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.08)',
      transform: `rotate(${rotate}deg)`,
      zIndex: 5
    }
  }, children);
}
Object.assign(window, {
  DesignCanvas,
  DCSection,
  DCArtboard,
  DCPostIt
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/design-canvas.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/group-data.jsx
try { (() => {
/* group-data.jsx — mock data for the Group page.
   A "group" on zxart is a crew / firm / studio / homebrew label / association.
   It has MEMBERS (with roles + active years), can contain SUBGROUPS, and plays
   up to three roles in the catalogue:
     • developer — authored its own prods (demos, intros, games)
     • publisher — released someone else's prods
     • cracker   — published cracked / adapted releases

   Three presets exercise the range:
     • "rush"  — Rush I.S.P.A.: big association, 12 members, 6 subgroups,
                 develops + publishes + cracks (faithful to the real archive entry)
     • "crack" — Bytebusters: pure cracker label, few members, only cracked releases
     • "crew"  — Pixel Saints: small 4-person demogroup, demos only, no publishing
*/

/* ── member role taxonomy (maps to colored chips) ── */
const GROUP_ROLES = {
  code: {
    label: "Программист",
    short: "Код",
    color: "code"
  },
  gfx: {
    label: "Художник",
    short: "Графика",
    color: "gfx"
  },
  music: {
    label: "Музыкант",
    short: "Музыка",
    color: "music"
  },
  support: {
    label: "Поддержка",
    short: "Саппорт",
    color: "support"
  },
  text: {
    label: "Текст",
    short: "Текст",
    color: "code"
  },
  unknown: {
    label: "Роль неизвестна",
    short: "—",
    color: "unknown"
  }
};

/* ── work-nature taxonomy for the group itself ── */
const GROUP_NATURE = {
  developer: {
    label: "Разработчик",
    hint: "делает собственные продукты"
  },
  publisher: {
    label: "Издатель",
    hint: "издаёт чужие программы"
  },
  cracker: {
    label: "Кракерская группа",
    hint: "публиковала взломанные релизы"
  }
};

/* ── release-type → badge class (matches preview/release-type-badge.html) ── */
const RELEASE_TYPE_LABELS = {
  original: "Оригинал",
  demoversion: "Демоверсия",
  adaptation: "Адаптация",
  mod: "Модификация",
  crack: "Взлом",
  rerelease: "Переиздание",
  unknown: "Неизвестный"
};
const PALETTES = ["sunset", "cool", "forest", "night", "default"];

/* ══════════════════════════════════════════════════════════════════════════
   PRESET 1 — RUSH (faithful to the archive entry in the brief)
   ══════════════════════════════════════════════════════════════════════════ */
const RUSH = {
  id: 87267,
  name: "Rush International Software Producing Association",
  abbr: "RUSH",
  type: "Демосценовая группа / ассоциация",
  nature: ["developer", "publisher", "cracker"],
  country: "Беларусь",
  city: "Гомель",
  years: "1995–2018",
  summary: "Гомельская ассоциация Speccy-сцены: делала демки и кряк-интро, выпускала " + "дискмаг Rush, издавала и адаптировала игры, объединяла под собой несколько " + "локальных команд.",
  links: [{
    site: "zxaaa.net",
    label: "zxaaa.net",
    icon: "Z"
  }, {
    site: "spectrumcomputing",
    label: "Spectrum Computing",
    icon: "S"
  }, {
    site: "spectrumcomputing",
    label: "Spectrum Computing",
    icon: "S"
  }, {
    site: "speccy.info",
    label: "SpeccyWiki",
    icon: "W"
  }],
  /* child groups — clickable cards */
  subgroups: [{
    id: 87539,
    name: "Avalon",
    abbr: "AVL",
    members: 6,
    prods: 4,
    years: "1996–2000"
  }, {
    id: 87130,
    name: "BrokImSoft",
    abbr: "BIS",
    members: 5,
    prods: 9,
    years: "1997–2002"
  }, {
    id: 87269,
    name: "Dream Makers Software",
    abbr: "DMS",
    members: 8,
    prods: 7,
    years: "1996–1999"
  }, {
    id: 87215,
    name: "ETC Group",
    abbr: "ETC",
    members: 4,
    prods: 3,
    years: "1997–1999"
  }, {
    id: 351705,
    name: "Reaction Group",
    abbr: "RG",
    members: 3,
    prods: 2,
    years: "1998–2000"
  }, {
    id: 351706,
    name: "Lavers",
    abbr: "LAV",
    members: 4,
    prods: 5,
    years: "1995–1998"
  }],
  /* members — handle, real name, role(s), active years, which subgroups */
  members: [{
    handle: "Afin'a",
    real: "",
    roles: ["unknown"],
    years: "",
    subs: []
  }, {
    handle: "Deeckobruz",
    real: "Дмитрий Дудко",
    roles: ["unknown"],
    years: "1998–2001",
    subs: ["ETC Group"]
  }, {
    handle: "Elf",
    real: "Константин Радчук",
    roles: ["gfx"],
    years: "1996–2002",
    subs: ["Dream Makers Software"],
    works: 9
  }, {
    handle: "Evolver",
    real: "Владислав Петюкевич",
    roles: ["music"],
    years: "1997–2000",
    subs: [],
    works: 4
  }, {
    handle: "Grunge",
    real: "Сергей Степанов",
    roles: ["unknown"],
    years: "",
    subs: ["Reaction Group"]
  }, {
    handle: "IMP",
    real: "Владимир Хропов",
    roles: ["music"],
    years: "1995–2001",
    subs: ["Dream Makers Software"],
    works: 11
  }, {
    handle: "Kamikaze",
    real: "Вадим Власов",
    roles: ["gfx"],
    years: "1998–2000",
    subs: ["Avalon"],
    works: 3
  }, {
    handle: "Kvazar",
    real: "Александр Селезнев",
    roles: ["support"],
    years: "1996–2003",
    subs: ["BrokImSoft"],
    works: 6
  }, {
    handle: "Ruff",
    real: "Павел Дудко",
    roles: ["code"],
    years: "1998–2002",
    subs: ["ETC Group"],
    works: 5
  }, {
    handle: "Slider",
    real: "Игорь Поздеев",
    roles: ["code", "text"],
    years: "1995–2018",
    subs: ["BrokImSoft"],
    works: 14
  }, {
    handle: "Viator",
    real: "Виктор Онищенко",
    roles: ["gfx", "code"],
    years: "1996–2002",
    subs: ["Avalon"],
    works: 12
  }, {
    handle: "Znahar",
    real: "Александр Лиходед",
    roles: ["code", "music"],
    years: "1996–2002",
    subs: ["Avalon", "Dream Makers Software"],
    works: 16
  }],
  /* OWN prods (developer) */
  prods: [{
    id: 265816,
    title: "Adventurer Crack Intro",
    year: 2000,
    kind: "Крактро",
    stars: 4,
    votes: 1,
    coGroups: []
  }, {
    id: 278252,
    title: "AleXofT Music",
    year: 2001,
    kind: "Демосцена",
    stars: 4,
    votes: 1,
    coGroups: ["BrokImSoft"]
  }, {
    id: 280119,
    title: "boot Avalon",
    year: 1997,
    kind: "Бут",
    stars: 4,
    votes: 1,
    coGroups: ["Avalon"]
  }, {
    id: 120142,
    title: "Brain Surgery",
    year: 1997,
    kind: "Трекмо",
    stars: 4,
    votes: 3,
    place: 0,
    coGroups: ["Dream Makers Software"],
    hw: ["128", "48"]
  }, {
    id: 273120,
    title: "Chronos 128 Crack Intro",
    year: 1996,
    kind: "Крактро",
    stars: 4,
    votes: 1,
    coGroups: ["Avalon"]
  }, {
    id: 281796,
    title: "Confusion",
    year: 1998,
    kind: "Демо",
    stars: 4,
    votes: 1,
    coGroups: ["ETC Group"],
    hw: ["48"]
  }, {
    id: 282322,
    title: "Dark Wheel",
    year: 1999,
    kind: "Электронная книга",
    stars: 4,
    votes: 1,
    coGroups: []
  }, {
    id: 120465,
    title: "Ecstasy",
    year: 1996,
    kind: "Мегадемо",
    stars: 4,
    votes: 2,
    coGroups: ["Dream Makers Software"],
    hw: ["128", "48", "Скорп"]
  }, {
    id: 284442,
    title: "Elf Gfx",
    year: 1998,
    kind: "Графика",
    stars: 4,
    votes: 1,
    coGroups: []
  }, {
    id: 304409,
    title: "Kenotron Gift",
    year: 1996,
    kind: "Гифт",
    stars: 4,
    votes: 1,
    coGroups: ["ETC Group"]
  }, {
    id: 264860,
    title: "Kick da Gaga Crack Intro",
    year: 1997,
    kind: "Крактро",
    stars: 4,
    votes: 2,
    coGroups: ["Avalon"]
  }, {
    id: 121050,
    title: "Mental Masturbation",
    year: 1996,
    kind: "Демо",
    stars: 4,
    votes: 1,
    coGroups: ["Dream Makers Software"],
    hw: ["128"]
  }, {
    id: 356297,
    title: "Priest Gift",
    year: 2000,
    kind: "Гифт",
    stars: 4,
    votes: 1,
    coGroups: []
  }, {
    id: 428111,
    title: "Rush #01",
    year: 1999,
    kind: "Дискмаг",
    stars: 4,
    votes: 1,
    coGroups: [],
    featured: true,
    hw: ["Pent128"]
  }, {
    id: 296210,
    title: "Rush '96 Info",
    year: 1996,
    kind: "Инфо",
    stars: 4,
    votes: 1,
    coGroups: ["Dream Makers Software"]
  }, {
    id: 296213,
    title: "RUSH boot",
    year: 1996,
    kind: "Бут",
    stars: 4,
    votes: 1,
    coGroups: ["Dream Makers Software"]
  }, {
    id: 265819,
    title: "Utok Bile Mysky 1 Crack Intro",
    year: 1999,
    kind: "Крактро",
    stars: 4,
    votes: 1,
    coGroups: []
  }, {
    id: 121982,
    title: "Vibrations",
    year: 1996,
    kind: "Трекмо",
    stars: 4,
    votes: 3,
    place: 2,
    party: "Enlight 1996",
    coGroups: ["Dream Makers Software"],
    hw: ["128", "48"],
    featured: true
  }],
  /* PUBLISHED prods — authored by others, RUSH published */
  published: [{
    id: 134662,
    title: "Crime of the Santa Claus: Deja Vu",
    year: 1997,
    kind: "Квест",
    stars: 4,
    votes: 3,
    by: ["BrokImSoft"],
    authors: ["Panda", "Slider", "Ticklish Jim"],
    hw: ["128"]
  }, {
    id: 155317,
    title: "Data Squeezer",
    year: 1995,
    kind: "Утилита",
    stars: 4,
    votes: 2,
    by: [],
    authors: ["APUS", "IMP"],
    hw: ["48"]
  }],
  /* RELEASES — distributions RUSH put out, with a release type */
  releases: [{
    id: 134666,
    title: "Crime Santa Clause: Deja Vu",
    year: 1997,
    type: "original",
    format: "TRD",
    coPub: ["BrokImSoft"]
  }, {
    id: 134667,
    title: "Crime Santa Clause: Deja Vu",
    year: 1997,
    type: "demoversion",
    format: "TRD",
    coPub: ["BrokImSoft"]
  }, {
    id: 134669,
    title: "Crime Santa Clause: Deja Vu",
    year: 1997,
    type: "original",
    format: "TRD",
    coPub: ["BrokImSoft"]
  }, {
    id: 155319,
    title: "Data Squeezer",
    year: 1995,
    type: "original",
    format: "TRD",
    coPub: []
  }, {
    id: 261891,
    title: "Warlock",
    year: 2002,
    type: "adaptation",
    format: "SCL",
    coPub: ["BrokImSoft"]
  }, {
    id: 261550,
    title: "Turbo Boat Simulator",
    year: 2001,
    type: "adaptation",
    format: "SCL",
    coPub: ["BrokImSoft"],
    authors: ["Slider"]
  }, {
    id: 261362,
    title: "Tokyo Gang",
    year: 2002,
    type: "adaptation",
    format: "SCL",
    coPub: ["BrokImSoft"],
    authors: ["Slider"]
  }, {
    id: 257630,
    title: "Metal Army",
    year: 2001,
    type: "adaptation",
    format: "SCL",
    coPub: ["BrokImSoft"],
    authors: ["Slider"]
  }, {
    id: 255742,
    title: "Gnoni",
    year: 2001,
    type: "adaptation",
    format: "SCL",
    coPub: ["BrokImSoft"]
  }, {
    id: 310587,
    title: "Soldier of Fortune",
    year: 2018,
    type: "adaptation",
    format: "SCL",
    coPub: ["BrokImSoft"],
    authors: ["AleXofT", "Panda", "Slider"]
  }, {
    id: 249375,
    title: "Art Studio",
    year: 1999,
    type: "mod",
    format: "TRD",
    coPub: [],
    authors: ["Viator"]
  }],
  /* mentions in scene press */
  mentions: [{
    pub: "Micro #04",
    year: 1998,
    section: "Взгляд в будущее",
    desc: "Эволюция энтузиастов ZX на фоне падения цен на ПК, призыв к профессиональному объединению."
  }, {
    pub: "Oberon #02",
    year: 1996,
    section: "Обзор",
    desc: "Разбор демо-конкурса ENLIGHT 1996 в Санкт-Петербурге, оценка участников по платформам."
  }, {
    pub: "Oberon #03",
    year: 1997,
    section: "Amiga rulez?",
    desc: "Полемика IBM против Amiga; Amiga как символ креативности."
  }, {
    pub: "Revival #01",
    year: 1997,
    section: "Новости",
    desc: "Анонсы событий сцены: Инлайт, релизы игр, компьютер Sprinter."
  }, {
    pub: "Rush #01",
    year: 1999,
    section: "Сценохрония",
    desc: "Обзор деятельности Rush: прошлые проекты, текущие начинания, планы."
  }, {
    pub: "Rush #01",
    year: 1999,
    section: "Spectrum программинг",
    desc: "Совмещение звуковых эффектов с музыкой на AY; примеры из CSC: Deja Vu."
  }, {
    pub: "ZX Format #07",
    year: 1997,
    section: "Разное",
    desc: "Разработка редактора STATE OF THE ART командой Avalon."
  }],
  /* external collaborators + groups this group published for */
  connections: {
    people: [{
      handle: "Panda",
      real: "Сергей Коротков",
      role: "код, дизайн",
      joint: 7,
      via: "BrokImSoft"
    }, {
      handle: "Ticklish Jim",
      real: "",
      role: "графика",
      joint: 5,
      via: "BrokImSoft"
    }, {
      handle: "AleXofT",
      real: "Александр Титов",
      role: "музыка",
      joint: 5,
      via: "фриланс"
    }, {
      handle: "APUS",
      real: "",
      role: "код",
      joint: 3,
      via: "—"
    }, {
      handle: "Scorpion",
      real: "Дмитрий Пянков",
      role: "код",
      joint: 2,
      via: "Scorpion Software"
    }],
    publishedGroups: [{
      name: "BrokImSoft",
      years: "1997–2018",
      count: 8,
      note: "со-издание и адаптации"
    }, {
      name: "Dream Makers Software",
      years: "1996–1999",
      count: 3,
      note: "издание демо"
    }, {
      name: "Power of Sound",
      years: "1998–1999",
      count: 2,
      note: "распространение музыки"
    }, {
      name: "X-Trade",
      years: "2001",
      count: 1,
      note: "адаптация игры"
    }]
  },
  comments: [{
    id: 1,
    by: "diver4d",
    date: "2024-11-19",
    workType: "prod",
    workTitle: "Vibrations",
    body: "Второе место на Enlight'96 заслуженно. Скроллер до сих пор гипнотизирует."
  }, {
    id: 2,
    by: "AAA",
    date: "2024-10-30",
    workType: "prod",
    workTitle: "Rush #01",
    body: "Лучший дискмаг из Гомеля. Жаль, второго номера не было."
  }, {
    id: 3,
    by: "oldman",
    date: "2024-09-12",
    workType: "release",
    workTitle: "Soldier of Fortune",
    body: "Адаптация на SCL в 2018-м — респект, что не бросили."
  }, {
    id: 4,
    by: "znahar_fan",
    date: "2024-08-04",
    workType: "prod",
    workTitle: "Ecstasy",
    body: "Мегадемо на скорпе летало. Кто помнит часть с плазмой?"
  }],
  votes: [{
    id: 1,
    by: "diver4d",
    date: "2024-11-19",
    workTitle: "Vibrations",
    workType: "prod",
    score: 5
  }, {
    id: 2,
    by: "AAA",
    date: "2024-10-30",
    workTitle: "Rush #01",
    workType: "prod",
    score: 4
  }, {
    id: 3,
    by: "oldman",
    date: "2024-09-12",
    workTitle: "Brain Surgery",
    workType: "prod",
    score: 5
  }, {
    id: 4,
    by: "voxel",
    date: "2024-08-22",
    workTitle: "Ecstasy",
    workType: "prod",
    score: 4
  }, {
    id: 5,
    by: "speccyboy",
    date: "2024-07-30",
    workTitle: "Confusion",
    workType: "prod",
    score: 4
  }],
  wall: [{
    id: 1,
    by: "AleXofT",
    date: "2024-12-01",
    body: "Если у кого сохранились исходники Rush #02 — отзовитесь, хотим доделать архив."
  }, {
    id: 2,
    by: "Kvazar",
    date: "2024-10-15",
    body: "Спасибо всем, кто оцифровал наши кассеты. Гомель помнит."
  }, {
    id: 3,
    by: "newbie03",
    date: "2024-09-20",
    body: "Только открыл для себя белорусскую сцену — это золото. Где послушать всю музыку IMP?"
  }]
};

/* ══════════════════════════════════════════════════════════════════════════
   PRESET 2 — Bytebusters: pure cracker label
   ══════════════════════════════════════════════════════════════════════════ */
const CRACK = {
  id: 90001,
  name: "Bytebusters Cracking Service",
  abbr: "BYTE",
  type: "Кракерская группа",
  nature: ["cracker", "publisher"],
  country: "Россия",
  city: "Москва",
  years: "1991–1996",
  summary: "Московский кряк-сервис начала 90-х: снимали защиту с импортных игр, " + "лепили интро и расходили копии по BBS и кассетным барахолкам.",
  links: [{
    site: "zxaaa.net",
    label: "zxaaa.net",
    icon: "Z"
  }, {
    site: "speccy.info",
    label: "SpeccyWiki",
    icon: "W"
  }],
  subgroups: [],
  members: [{
    handle: "Spider",
    real: "Олег П.",
    roles: ["code"],
    years: "1991–1996",
    subs: [],
    works: 22
  }, {
    handle: "Razor",
    real: "",
    roles: ["code", "gfx"],
    years: "1991–1995",
    subs: [],
    works: 14
  }, {
    handle: "Beat",
    real: "Антон К.",
    roles: ["music"],
    years: "1992–1996",
    subs: [],
    works: 8
  }, {
    handle: "Courier",
    real: "",
    roles: ["support"],
    years: "1991–1994",
    subs: []
  }],
  prods: [{
    id: 91001,
    title: "Bytebusters Intro #3",
    year: 1992,
    kind: "Крактро",
    stars: 4,
    votes: 2,
    coGroups: []
  }, {
    id: 91002,
    title: "Bytebusters Intro #5",
    year: 1993,
    kind: "Крактро",
    stars: 3,
    votes: 1,
    coGroups: []
  }, {
    id: 91003,
    title: "Megacrack Menu",
    year: 1994,
    kind: "Меню",
    stars: 4,
    votes: 2,
    coGroups: []
  }],
  published: [],
  releases: [{
    id: 92001,
    title: "Robocop 3",
    year: 1992,
    type: "crack",
    format: "TAP",
    coPub: []
  }, {
    id: 92002,
    title: "Lemmings",
    year: 1993,
    type: "crack",
    format: "TRD",
    coPub: []
  }, {
    id: 92003,
    title: "Prince of Persia",
    year: 1993,
    type: "crack",
    format: "TRD",
    coPub: []
  }, {
    id: 92004,
    title: "Dizzy Collection",
    year: 1994,
    type: "rerelease",
    format: "TRD",
    coPub: []
  }, {
    id: 92005,
    title: "Saboteur II",
    year: 1992,
    type: "crack",
    format: "TAP",
    coPub: []
  }, {
    id: 92006,
    title: "Elite (RU)",
    year: 1994,
    type: "adaptation",
    format: "TRD",
    coPub: [],
    authors: ["Spider"]
  }, {
    id: 92007,
    title: "Target Renegade",
    year: 1993,
    type: "crack",
    format: "TAP",
    coPub: []
  }, {
    id: 92008,
    title: "Rick Dangerous",
    year: 1992,
    type: "crack",
    format: "TAP",
    coPub: []
  }],
  mentions: [{
    pub: "ZX Revue",
    year: 1993,
    section: "Пиратские вести",
    desc: "Упоминание Bytebusters среди активных кряк-сервисов Москвы."
  }],
  connections: {
    people: [{
      handle: "Doctor",
      real: "",
      role: "код",
      joint: 4,
      via: "фриланс"
    }, {
      handle: "Maxx",
      real: "Максим Б.",
      role: "музыка",
      joint: 3,
      via: "Flash Inc."
    }, {
      handle: "Vandal",
      real: "",
      role: "код",
      joint: 2,
      via: "—"
    }],
    publishedGroups: [{
      name: "Flash Inc.",
      years: "1992–1994",
      count: 4,
      note: "совместные кряки"
    }, {
      name: "Bit Bandits",
      years: "1993",
      count: 2,
      note: "распространение"
    }]
  },
  comments: [{
    id: 1,
    by: "retr0",
    date: "2023-05-10",
    workType: "release",
    workTitle: "Lemmings",
    body: "Их кряк Lemmings гонял весь двор. Музыка в интро огонь."
  }],
  votes: [{
    id: 1,
    by: "retr0",
    date: "2023-05-10",
    workTitle: "Lemmings",
    workType: "release",
    score: 5
  }, {
    id: 2,
    by: "x",
    date: "2023-04-02",
    workTitle: "Prince of Persia",
    workType: "release",
    score: 4
  }],
  wall: [{
    id: 1,
    by: "collector",
    date: "2023-06-01",
    body: "Ищу полную коллекцию интро Bytebusters. Кто оцифрует?"
  }]
};

/* ══════════════════════════════════════════════════════════════════════════
   PRESET 3 — Pixel Saints: small demo crew, no publishing
   ══════════════════════════════════════════════════════════════════════════ */
const CREW = {
  id: 95001,
  name: "Pixel Saints",
  abbr: "PXS",
  type: "Демосценовая группа",
  nature: ["developer"],
  country: "Украина",
  city: "Харьков",
  years: "2014–наст.",
  summary: "Маленькая харьковская команда: четыре человека, чистая демосцена — демки, " + "интро и графика для современных пати.",
  links: [{
    site: "zxaaa.net",
    label: "zxaaa.net",
    icon: "Z"
  }],
  subgroups: [],
  members: [{
    handle: "nooly",
    real: "Богдан",
    roles: ["code"],
    years: "2014–наст.",
    subs: [],
    works: 9
  }, {
    handle: "feen",
    real: "Ирина",
    roles: ["gfx"],
    years: "2015–наст.",
    subs: [],
    works: 7
  }, {
    handle: "tq",
    real: "",
    roles: ["music"],
    years: "2014–наст.",
    subs: [],
    works: 6
  }, {
    handle: "kost",
    real: "Костя",
    roles: ["code", "gfx"],
    years: "2016–наст.",
    subs: [],
    works: 4
  }],
  prods: [{
    id: 96001,
    title: "Saint Lights",
    year: 2017,
    kind: "Демо",
    stars: 5,
    votes: 6,
    place: 1,
    party: "Multimatograf 2017",
    coGroups: [],
    hw: ["128"],
    featured: true
  }, {
    id: 96002,
    title: "Tiny Halo",
    year: 2018,
    kind: "Интро",
    stars: 4,
    votes: 3,
    place: 2,
    party: "DiHalt 2018",
    coGroups: [],
    hw: ["128"]
  }, {
    id: 96003,
    title: "Pixel Prayer",
    year: 2019,
    kind: "Демо",
    stars: 4,
    votes: 4,
    coGroups: [],
    hw: ["128"]
  }, {
    id: 96004,
    title: "Feen Gfx Pack",
    year: 2020,
    kind: "Графика",
    stars: 4,
    votes: 2,
    coGroups: []
  }, {
    id: 96005,
    title: "Lowtech",
    year: 2022,
    kind: "Интро",
    stars: 4,
    votes: 3,
    place: 3,
    party: "CAFePARTY 2022",
    coGroups: [],
    hw: ["48"]
  }],
  published: [],
  releases: [],
  mentions: [],
  connections: {
    people: [{
      handle: "diver4d",
      real: "Андрей",
      role: "орг/музыка",
      joint: 3,
      via: "фриланс"
    }, {
      handle: "q-bee",
      real: "",
      role: "графика",
      joint: 2,
      via: "Sand"
    }],
    publishedGroups: []
  },
  comments: [{
    id: 1,
    by: "diver4d",
    date: "2022-09-15",
    workType: "prod",
    workTitle: "Lowtech",
    body: "Третье место, но по вайбу — первое. Классный AY-саунд у tq."
  }],
  votes: [{
    id: 1,
    by: "diver4d",
    date: "2022-09-15",
    workTitle: "Lowtech",
    workType: "prod",
    score: 5
  }, {
    id: 2,
    by: "feen_fan",
    date: "2020-03-10",
    workTitle: "Saint Lights",
    workType: "prod",
    score: 5
  }],
  wall: [{
    id: 1,
    by: "organizer",
    date: "2024-01-12",
    body: "Ждём вас на следующем Multimatograf! Привозите новую демку."
  }]
};
const GROUP_PRESETS = {
  rush: RUSH,
  crack: CRACK,
  crew: CREW
};

/* Russian plural helper */
function pluralRuG(n, [one, few, many]) {
  const m10 = n % 10,
    m100 = n % 100;
  if (m10 === 1 && m100 !== 11) return one;
  if (m10 >= 2 && m10 <= 4 && (m100 < 12 || m100 > 14)) return few;
  return many;
}

/* palette pick (stable per id) */
function paletteFor(id) {
  return PALETTES[id % PALETTES.length];
}
Object.assign(window, {
  GROUP_PRESETS,
  GROUP_ROLES,
  GROUP_NATURE,
  RELEASE_TYPE_LABELS,
  pluralRuG,
  paletteFor
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/group-data.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/ios-frame.jsx
try { (() => {
// iOS.jsx — Simplified iOS 26 (Liquid Glass) device frame
// Based on the iOS 26 UI Kit + Figma status bar spec. No assets, no deps.
// Exports: IOSDevice, IOSStatusBar, IOSNavBar, IOSGlassPill, IOSList, IOSListRow, IOSKeyboard

// ─────────────────────────────────────────────────────────────
// Status bar
// ─────────────────────────────────────────────────────────────
function IOSStatusBar({
  dark = false,
  time = '9:41'
}) {
  const c = dark ? '#fff' : '#000';
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 154,
      alignItems: 'center',
      justifyContent: 'center',
      padding: '21px 24px 19px',
      boxSizing: 'border-box',
      position: 'relative',
      zIndex: 20,
      width: '100%'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      height: 22,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      paddingTop: 1.5
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: '-apple-system, "SF Pro", system-ui',
      fontWeight: 590,
      fontSize: 17,
      lineHeight: '22px',
      color: c
    }
  }, time)), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      height: 22,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      gap: 7,
      paddingTop: 1,
      paddingRight: 1
    }
  }, /*#__PURE__*/React.createElement("svg", {
    width: "19",
    height: "12",
    viewBox: "0 0 19 12"
  }, /*#__PURE__*/React.createElement("rect", {
    x: "0",
    y: "7.5",
    width: "3.2",
    height: "4.5",
    rx: "0.7",
    fill: c
  }), /*#__PURE__*/React.createElement("rect", {
    x: "4.8",
    y: "5",
    width: "3.2",
    height: "7",
    rx: "0.7",
    fill: c
  }), /*#__PURE__*/React.createElement("rect", {
    x: "9.6",
    y: "2.5",
    width: "3.2",
    height: "9.5",
    rx: "0.7",
    fill: c
  }), /*#__PURE__*/React.createElement("rect", {
    x: "14.4",
    y: "0",
    width: "3.2",
    height: "12",
    rx: "0.7",
    fill: c
  })), /*#__PURE__*/React.createElement("svg", {
    width: "17",
    height: "12",
    viewBox: "0 0 17 12"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M8.5 3.2C10.8 3.2 12.9 4.1 14.4 5.6L15.5 4.5C13.7 2.7 11.2 1.5 8.5 1.5C5.8 1.5 3.3 2.7 1.5 4.5L2.6 5.6C4.1 4.1 6.2 3.2 8.5 3.2Z",
    fill: c
  }), /*#__PURE__*/React.createElement("path", {
    d: "M8.5 6.8C9.9 6.8 11.1 7.3 12 8.2L13.1 7.1C11.8 5.9 10.2 5.1 8.5 5.1C6.8 5.1 5.2 5.9 3.9 7.1L5 8.2C5.9 7.3 7.1 6.8 8.5 6.8Z",
    fill: c
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "8.5",
    cy: "10.5",
    r: "1.5",
    fill: c
  })), /*#__PURE__*/React.createElement("svg", {
    width: "27",
    height: "13",
    viewBox: "0 0 27 13"
  }, /*#__PURE__*/React.createElement("rect", {
    x: "0.5",
    y: "0.5",
    width: "23",
    height: "12",
    rx: "3.5",
    stroke: c,
    strokeOpacity: "0.35",
    fill: "none"
  }), /*#__PURE__*/React.createElement("rect", {
    x: "2",
    y: "2",
    width: "20",
    height: "9",
    rx: "2",
    fill: c
  }), /*#__PURE__*/React.createElement("path", {
    d: "M25 4.5V8.5C25.8 8.2 26.5 7.2 26.5 6.5C26.5 5.8 25.8 4.8 25 4.5Z",
    fill: c,
    fillOpacity: "0.4"
  }))));
}

// ─────────────────────────────────────────────────────────────
// Liquid glass pill — blur + tint + shine
// ─────────────────────────────────────────────────────────────
function IOSGlassPill({
  children,
  dark = false,
  style = {}
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      height: 44,
      minWidth: 44,
      borderRadius: 9999,
      position: 'relative',
      overflow: 'hidden',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      boxShadow: dark ? '0 2px 6px rgba(0,0,0,0.35), 0 6px 16px rgba(0,0,0,0.2)' : '0 1px 3px rgba(0,0,0,0.07), 0 3px 10px rgba(0,0,0,0.06)',
      ...style
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      inset: 0,
      borderRadius: 9999,
      backdropFilter: 'blur(12px) saturate(180%)',
      WebkitBackdropFilter: 'blur(12px) saturate(180%)',
      background: dark ? 'rgba(120,120,128,0.28)' : 'rgba(255,255,255,0.5)'
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      inset: 0,
      borderRadius: 9999,
      boxShadow: dark ? 'inset 1.5px 1.5px 1px rgba(255,255,255,0.15), inset -1px -1px 1px rgba(255,255,255,0.08)' : 'inset 1.5px 1.5px 1px rgba(255,255,255,0.7), inset -1px -1px 1px rgba(255,255,255,0.4)',
      border: dark ? '0.5px solid rgba(255,255,255,0.15)' : '0.5px solid rgba(0,0,0,0.06)'
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'relative',
      zIndex: 1,
      display: 'flex',
      alignItems: 'center',
      padding: '0 4px'
    }
  }, children));
}

// ─────────────────────────────────────────────────────────────
// Navigation bar — glass pills + large title
// ─────────────────────────────────────────────────────────────
function IOSNavBar({
  title = 'Title',
  dark = false,
  trailingIcon = true
}) {
  const muted = dark ? 'rgba(255,255,255,0.6)' : '#404040';
  const text = dark ? '#fff' : '#000';
  const pillIcon = content => /*#__PURE__*/React.createElement(IOSGlassPill, {
    dark: dark
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      width: 36,
      height: 36,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center'
    }
  }, content));
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 10,
      paddingTop: 62,
      paddingBottom: 10,
      position: 'relative',
      zIndex: 5
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      padding: '0 16px'
    }
  }, pillIcon(/*#__PURE__*/React.createElement("svg", {
    width: "12",
    height: "20",
    viewBox: "0 0 12 20",
    fill: "none",
    style: {
      marginLeft: -1
    }
  }, /*#__PURE__*/React.createElement("path", {
    d: "M10 2L2 10l8 8",
    stroke: muted,
    strokeWidth: "2.5",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  }))), trailingIcon && pillIcon(/*#__PURE__*/React.createElement("svg", {
    width: "22",
    height: "6",
    viewBox: "0 0 22 6"
  }, /*#__PURE__*/React.createElement("circle", {
    cx: "3",
    cy: "3",
    r: "2.5",
    fill: muted
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "11",
    cy: "3",
    r: "2.5",
    fill: muted
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "19",
    cy: "3",
    r: "2.5",
    fill: muted
  })))), /*#__PURE__*/React.createElement("div", {
    style: {
      padding: '0 16px',
      fontFamily: '-apple-system, system-ui',
      fontSize: 34,
      fontWeight: 700,
      lineHeight: '41px',
      color: text,
      letterSpacing: 0.4
    }
  }, title));
}

// ─────────────────────────────────────────────────────────────
// Grouped list (inset card, r:26) + row (52px)
// ─────────────────────────────────────────────────────────────
function IOSListRow({
  title,
  detail,
  icon,
  chevron = true,
  isLast = false,
  dark = false
}) {
  const text = dark ? '#fff' : '#000';
  const sec = dark ? 'rgba(235,235,245,0.6)' : 'rgba(60,60,67,0.6)';
  const ter = dark ? 'rgba(235,235,245,0.3)' : 'rgba(60,60,67,0.3)';
  const sep = dark ? 'rgba(84,84,88,0.65)' : 'rgba(60,60,67,0.12)';
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      minHeight: 52,
      padding: '0 16px',
      position: 'relative',
      fontFamily: '-apple-system, system-ui',
      fontSize: 17,
      letterSpacing: -0.43
    }
  }, icon && /*#__PURE__*/React.createElement("div", {
    style: {
      width: 30,
      height: 30,
      borderRadius: 7,
      background: icon,
      marginRight: 12,
      flexShrink: 0
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      color: text
    }
  }, title), detail && /*#__PURE__*/React.createElement("span", {
    style: {
      color: sec,
      marginRight: 6
    }
  }, detail), chevron && /*#__PURE__*/React.createElement("svg", {
    width: "8",
    height: "14",
    viewBox: "0 0 8 14",
    style: {
      flexShrink: 0
    }
  }, /*#__PURE__*/React.createElement("path", {
    d: "M1 1l6 6-6 6",
    stroke: ter,
    strokeWidth: "2",
    fill: "none",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  })), !isLast && /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      bottom: 0,
      right: 0,
      left: icon ? 58 : 16,
      height: 0.5,
      background: sep
    }
  }));
}
function IOSList({
  header,
  children,
  dark = false
}) {
  const hc = dark ? 'rgba(235,235,245,0.6)' : 'rgba(60,60,67,0.6)';
  const bg = dark ? '#1C1C1E' : '#fff';
  return /*#__PURE__*/React.createElement("div", null, header && /*#__PURE__*/React.createElement("div", {
    style: {
      fontFamily: '-apple-system, system-ui',
      fontSize: 13,
      color: hc,
      textTransform: 'uppercase',
      padding: '8px 36px 6px',
      letterSpacing: -0.08
    }
  }, header), /*#__PURE__*/React.createElement("div", {
    style: {
      background: bg,
      borderRadius: 26,
      margin: '0 16px',
      overflow: 'hidden'
    }
  }, children));
}

// ─────────────────────────────────────────────────────────────
// Device frame
// ─────────────────────────────────────────────────────────────
function IOSDevice({
  children,
  width = 402,
  height = 874,
  dark = false,
  title,
  keyboard = false
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      width,
      height,
      borderRadius: 48,
      overflow: 'hidden',
      position: 'relative',
      background: dark ? '#000' : '#F2F2F7',
      boxShadow: '0 40px 80px rgba(0,0,0,0.18), 0 0 0 1px rgba(0,0,0,0.12)',
      fontFamily: '-apple-system, system-ui, sans-serif',
      WebkitFontSmoothing: 'antialiased'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: 11,
      left: '50%',
      transform: 'translateX(-50%)',
      width: 126,
      height: 37,
      borderRadius: 24,
      background: '#000',
      zIndex: 50
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: 0,
      left: 0,
      right: 0,
      zIndex: 10
    }
  }, /*#__PURE__*/React.createElement(IOSStatusBar, {
    dark: dark
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      height: '100%',
      display: 'flex',
      flexDirection: 'column'
    }
  }, title !== undefined && /*#__PURE__*/React.createElement(IOSNavBar, {
    title: title,
    dark: dark
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      overflow: 'auto'
    }
  }, children), keyboard && /*#__PURE__*/React.createElement(IOSKeyboard, {
    dark: dark
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      bottom: 0,
      left: 0,
      right: 0,
      zIndex: 60,
      height: 34,
      display: 'flex',
      justifyContent: 'center',
      alignItems: 'flex-end',
      paddingBottom: 8,
      pointerEvents: 'none'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      width: 139,
      height: 5,
      borderRadius: 100,
      background: dark ? 'rgba(255,255,255,0.7)' : 'rgba(0,0,0,0.25)'
    }
  })));
}

// ─────────────────────────────────────────────────────────────
// Keyboard — iOS 26 liquid glass
// ─────────────────────────────────────────────────────────────
function IOSKeyboard({
  dark = false
}) {
  const glyph = dark ? 'rgba(255,255,255,0.7)' : '#595959';
  const sugg = dark ? 'rgba(255,255,255,0.6)' : '#333';
  const keyBg = dark ? 'rgba(255,255,255,0.22)' : 'rgba(255,255,255,0.85)';

  // special-key icons
  const icons = {
    shift: /*#__PURE__*/React.createElement("svg", {
      width: "19",
      height: "17",
      viewBox: "0 0 19 17"
    }, /*#__PURE__*/React.createElement("path", {
      d: "M9.5 1L1 9.5h4.5V16h8V9.5H18L9.5 1z",
      fill: glyph
    })),
    del: /*#__PURE__*/React.createElement("svg", {
      width: "23",
      height: "17",
      viewBox: "0 0 23 17"
    }, /*#__PURE__*/React.createElement("path", {
      d: "M7 1h13a2 2 0 012 2v11a2 2 0 01-2 2H7l-6-7.5L7 1z",
      fill: "none",
      stroke: glyph,
      strokeWidth: "1.6",
      strokeLinejoin: "round"
    }), /*#__PURE__*/React.createElement("path", {
      d: "M10 5l7 7M17 5l-7 7",
      stroke: glyph,
      strokeWidth: "1.6",
      strokeLinecap: "round"
    })),
    ret: /*#__PURE__*/React.createElement("svg", {
      width: "20",
      height: "14",
      viewBox: "0 0 20 14"
    }, /*#__PURE__*/React.createElement("path", {
      d: "M18 1v6H4m0 0l4-4M4 7l4 4",
      fill: "none",
      stroke: "#fff",
      strokeWidth: "1.8",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }))
  };
  const key = (content, {
    w,
    flex,
    ret,
    fs = 25,
    k
  } = {}) => /*#__PURE__*/React.createElement("div", {
    key: k,
    style: {
      height: 42,
      borderRadius: 8.5,
      flex: flex ? 1 : undefined,
      width: w,
      minWidth: 0,
      background: ret ? '#08f' : keyBg,
      boxShadow: '0 1px 0 rgba(0,0,0,0.075)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      fontFamily: '-apple-system, "SF Compact", system-ui',
      fontSize: fs,
      fontWeight: 458,
      color: ret ? '#fff' : glyph
    }
  }, content);
  const row = (keys, pad = 0) => /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 6.5,
      justifyContent: 'center',
      padding: `0 ${pad}px`
    }
  }, keys.map(l => key(l, {
    flex: true,
    k: l
  })));
  return /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'relative',
      zIndex: 15,
      borderRadius: 27,
      overflow: 'hidden',
      padding: '11px 0 2px',
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      boxShadow: dark ? '0 -2px 20px rgba(0,0,0,0.09)' : '0 -1px 6px rgba(0,0,0,0.018), 0 -3px 20px rgba(0,0,0,0.012)'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      inset: 0,
      borderRadius: 27,
      backdropFilter: 'blur(12px) saturate(180%)',
      WebkitBackdropFilter: 'blur(12px) saturate(180%)',
      background: dark ? 'rgba(120,120,128,0.14)' : 'rgba(255,255,255,0.25)'
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      inset: 0,
      borderRadius: 27,
      boxShadow: dark ? 'inset 1.5px 1.5px 1px rgba(255,255,255,0.15)' : 'inset 1.5px 1.5px 1px rgba(255,255,255,0.7), inset -1px -1px 1px rgba(255,255,255,0.4)',
      border: dark ? '0.5px solid rgba(255,255,255,0.15)' : '0.5px solid rgba(0,0,0,0.06)',
      pointerEvents: 'none'
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 20,
      alignItems: 'center',
      padding: '8px 22px 13px',
      width: '100%',
      boxSizing: 'border-box',
      position: 'relative'
    }
  }, ['"The"', 'the', 'to'].map((w, i) => /*#__PURE__*/React.createElement(React.Fragment, {
    key: i
  }, i > 0 && /*#__PURE__*/React.createElement("div", {
    style: {
      width: 1,
      height: 25,
      background: '#ccc',
      opacity: 0.3
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      textAlign: 'center',
      fontFamily: '-apple-system, system-ui',
      fontSize: 17,
      color: sugg,
      letterSpacing: -0.43,
      lineHeight: '22px'
    }
  }, w)))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 13,
      padding: '0 6.5px',
      width: '100%',
      boxSizing: 'border-box',
      position: 'relative'
    }
  }, row(['q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p']), row(['a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l'], 20), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 14.25,
      alignItems: 'center'
    }
  }, key(icons.shift, {
    w: 45,
    k: 'shift'
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 6.5,
      flex: 1
    }
  }, ['z', 'x', 'c', 'v', 'b', 'n', 'm'].map(l => key(l, {
    flex: true,
    k: l
  }))), key(icons.del, {
    w: 45,
    k: 'del'
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 6,
      alignItems: 'center'
    }
  }, key('ABC', {
    w: 92.25,
    fs: 18,
    k: 'abc'
  }), key('', {
    flex: true,
    k: 'space'
  }), key(icons.ret, {
    w: 92.25,
    ret: true,
    k: 'ret'
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      height: 56,
      width: '100%',
      position: 'relative'
    }
  }));
}
Object.assign(window, {
  IOSDevice,
  IOSStatusBar,
  IOSNavBar,
  IOSGlassPill,
  IOSList,
  IOSListRow,
  IOSKeyboard
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/ios-frame.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/party-data.jsx
try { (() => {
/* party-data.jsx — mock data for the Party page.
   A "party" on zxart is a competition / demoparty / event. Structurally it is a
   set of PREDEFINED COMPOS (categories), and each compo holds ranked ENTRIES.
   An entry can be a prod, a tune or a picture:
     • medium "prod"    → demos, intros, games, wild  (rendered as ProdCard grid)
     • medium "music"   → AY / beeper tunes           (rendered as tune rows)
     • medium "picture" → graphics                    (rendered as PictureCard grid)

   Three presets exercise the range:
     • "dihalt" — DiHalt 2008: big multi-day demoparty, 7 compos across all media
     • "spl"    — Speccy.pl party 2026: medium online-ish party (from the brief)
     • "online" — Chiptune Compo: tiny 3-compo online event
*/

const PP_PALETTES = ["sunset", "cool", "forest", "night", "default"];
function paletteForP(id) {
  return PP_PALETTES[id % PP_PALETTES.length];
}

/* compo medium → icon + accent used in headers / index */
const COMPO_MEDIA = {
  demo: {
    icon: "demo",
    medium: "prod",
    label: "Демо"
  },
  intro: {
    icon: "intro",
    medium: "prod",
    label: "Интро"
  },
  game: {
    icon: "game",
    medium: "prod",
    label: "Игра"
  },
  wild: {
    icon: "wild",
    medium: "prod",
    label: "Wild"
  },
  ay: {
    icon: "music",
    medium: "music",
    label: "Музыка AY"
  },
  beeper: {
    icon: "beeper",
    medium: "music",
    label: "Музыка Beeper"
  },
  gfx: {
    icon: "gfx",
    medium: "picture",
    label: "Графика"
  }
};

/* ══════════════════════════════════════════════════════════════════════════
   PRESET 1 — DiHalt 2008 (big multi-day demoparty)
   ══════════════════════════════════════════════════════════════════════════ */
const DIHALT = {
  id: 4821,
  name: "DiHalt 2008",
  abbr: "dihalt2008",
  type: "Демопати",
  country: "Россия",
  city: "Нижний Новгород",
  year: 2008,
  summary: "Летняя демопати под Нижним Новгородом: три дня на берегу, живые конкурсы по " + "демо, интро, музыке и графике для ZX Spectrum, ночные выступления и большой экран.",
  links: [{
    label: "demozoo.org",
    icon: "D"
  }, {
    label: "pouet.net",
    icon: "P"
  }, {
    label: "Сайт пати",
    icon: "W"
  }],
  compos: [{
    id: 51,
    type: "demo",
    name: "ZX Демо",
    entries: [{
      id: 5101,
      title: "Across the Edge",
      by: ["Triebkraft"],
      place: 1,
      stars: 4.6,
      votes: 31,
      hw: ["Pentagon", "128"]
    }, {
      id: 5102,
      title: "Refresh",
      by: ["Skrju"],
      place: 2,
      stars: 4.3,
      votes: 27,
      hw: ["128"]
    }, {
      id: 5103,
      title: "Hardwired Redux",
      by: ["Thesuper"],
      place: 3,
      stars: 4.1,
      votes: 24,
      hw: ["Pentagon"]
    }, {
      id: 5104,
      title: "Sunrise",
      by: ["Placebo"],
      place: 4,
      stars: 3.7,
      votes: 19,
      hw: ["128"]
    }]
  }, {
    id: 52,
    type: "intro",
    name: "ZX 1K Интро",
    entries: [{
      id: 5201,
      title: "kotleta.1k",
      by: ["Introspec"],
      place: 1,
      stars: 4.4,
      votes: 22,
      hw: ["48"]
    }, {
      id: 5202,
      title: "spark",
      by: ["RST7"],
      place: 2,
      stars: 4.0,
      votes: 18,
      hw: ["48"]
    }, {
      id: 5203,
      title: "minimal",
      by: ["Alone Coder"],
      place: 3,
      stars: 3.6,
      votes: 15,
      hw: ["48"]
    }, {
      id: 5204,
      title: "blink",
      by: ["nyuk"],
      place: 4,
      stars: 3.2,
      votes: 11,
      hw: ["48"]
    }]
  }, {
    id: 53,
    type: "ay",
    name: "ZX Музыка AY",
    entries: [{
      id: 5301,
      title: "Summer Rain",
      by: ["Nik-O"],
      place: 1,
      stars: 4.7,
      votes: 29,
      chip: "AY",
      duration: "3:42"
    }, {
      id: 5302,
      title: "Night Drive",
      by: ["Yerzmyey"],
      place: 2,
      stars: 4.4,
      votes: 25,
      chip: "AY",
      duration: "2:58"
    }, {
      id: 5303,
      title: "Pixel Dreams",
      by: ["MmcM"],
      place: 3,
      stars: 4.2,
      votes: 22,
      chip: "AY",
      duration: "4:11"
    }, {
      id: 5304,
      title: "Lowlevel",
      by: ["Karbofos"],
      place: 4,
      stars: 3.9,
      votes: 17,
      chip: "AY",
      duration: "2:33"
    }, {
      id: 5305,
      title: "Gomel Groove",
      by: ["IMP"],
      place: 5,
      stars: 3.6,
      votes: 14,
      chip: "AY",
      duration: "3:05"
    }]
  }, {
    id: 54,
    type: "beeper",
    name: "ZX Музыка Beeper",
    entries: [{
      id: 5401,
      title: "1-bit Anthem",
      by: ["Shiru"],
      place: 1,
      stars: 4.5,
      votes: 20,
      chip: "Beeper",
      duration: "2:10"
    }, {
      id: 5402,
      title: "Square Up",
      by: ["Tufty"],
      place: 2,
      stars: 4.1,
      votes: 16,
      chip: "Beeper",
      duration: "1:58"
    }, {
      id: 5403,
      title: "Click Track",
      by: ["g0blinish"],
      place: 3,
      stars: 3.5,
      votes: 12,
      chip: "Beeper",
      duration: "2:24"
    }]
  }, {
    id: 55,
    type: "gfx",
    name: "ZX Графика",
    entries: [{
      id: 5501,
      title: "Iron Maiden",
      by: ["Diver"],
      place: 1,
      stars: 4.8,
      votes: 33,
      format: "Standard"
    }, {
      id: 5502,
      title: "Deep Forest",
      by: ["Mr.John"],
      place: 2,
      stars: 4.5,
      votes: 28,
      format: "Standard"
    }, {
      id: 5503,
      title: "Cyberpunk",
      by: ["Andy Fer"],
      place: 3,
      stars: 4.3,
      votes: 25,
      format: "Multicolor"
    }, {
      id: 5504,
      title: "Old Castle",
      by: ["Quiet"],
      place: 4,
      stars: 4.0,
      votes: 21,
      format: "Standard"
    }, {
      id: 5505,
      title: "Neon Night",
      by: ["Lobo"],
      place: 5,
      stars: 3.7,
      votes: 16,
      format: "Gigascreen"
    }, {
      id: 5506,
      title: "Portrait",
      by: ["Riskej"],
      place: 6,
      stars: 3.4,
      votes: 12,
      format: "Standard"
    }]
  }, {
    id: 56,
    type: "game",
    name: "ZX Игра",
    entries: [{
      id: 5601,
      title: "Cave Rush",
      by: ["RAID"],
      place: 1,
      stars: 4.2,
      votes: 18,
      hw: ["128"]
    }, {
      id: 5602,
      title: "Blockmania",
      by: ["Dimca"],
      place: 2,
      stars: 3.8,
      votes: 14,
      hw: ["48"]
    }, {
      id: 5603,
      title: "Robo Escape",
      by: ["Megus"],
      place: 3,
      stars: 3.4,
      votes: 10,
      hw: ["128"]
    }]
  }, {
    id: 57,
    type: "wild",
    name: "Wild / Combined",
    entries: [{
      id: 5701,
      title: "Real Speccy on Fire",
      by: ["Triebkraft"],
      place: 1,
      stars: 4.3,
      votes: 19,
      hw: ["video"]
    }, {
      id: 5702,
      title: "Beach Demo (live)",
      by: ["Placebo"],
      place: 2,
      stars: 3.9,
      votes: 15,
      hw: ["video"]
    }, {
      id: 5703,
      title: "TV Noise",
      by: ["Skrju"],
      place: 3,
      stars: 3.5,
      votes: 11,
      hw: ["video"]
    }]
  }],
  comments: [{
    id: 1,
    by: "introspec",
    date: "2008-07-08",
    compo: "ZX 1K Интро",
    workTitle: "kotleta.1k",
    body: "Уложить такое в килобайт — это праздник. Заслуженное первое."
  }, {
    id: 2,
    by: "diver4d",
    date: "2008-07-07",
    compo: "ZX Графика",
    workTitle: "Iron Maiden",
    body: "Бессмертная классика спектрумовской графики. Стандарт, а как звучит."
  }, {
    id: 3,
    by: "yerzmyey",
    date: "2008-07-09",
    compo: "ZX Музыка AY",
    workTitle: "Summer Rain",
    body: "Nik-O в своём репертуаре, мелодия не вылезает из головы неделю."
  }, {
    id: 4,
    by: "oldschool",
    date: "2008-07-10",
    compo: "ZX Демо",
    workTitle: "Across the Edge",
    body: "Triebkraft вывезли пати на себе. Последняя часть — мурашки."
  }],
  votes: [{
    id: 1,
    by: "introspec",
    date: "2008-07-08",
    compo: "1K Интро",
    workTitle: "kotleta.1k",
    score: 5
  }, {
    id: 2,
    by: "diver4d",
    date: "2008-07-07",
    compo: "Графика",
    workTitle: "Iron Maiden",
    score: 5
  }, {
    id: 3,
    by: "megus",
    date: "2008-07-07",
    compo: "Демо",
    workTitle: "Refresh",
    score: 4
  }, {
    id: 4,
    by: "shiru",
    date: "2008-07-09",
    compo: "Beeper",
    workTitle: "1-bit Anthem",
    score: 5
  }, {
    id: 5,
    by: "quiet",
    date: "2008-07-10",
    compo: "Демо",
    workTitle: "Across the Edge",
    score: 5
  }]
};

/* ══════════════════════════════════════════════════════════════════════════
   PRESET 2 — Speccy.pl party 2026 (from the brief)
   ══════════════════════════════════════════════════════════════════════════ */
const SPL = {
  id: 598486,
  name: "Speccy.pl party 2026",
  abbr: "spl2026",
  type: "Демопати",
  country: "Польша",
  city: "Варшава",
  year: 2026,
  summary: "Польская спектрумовская пати: компактная программа из интро, демо и графики " + "на 48K/TR-DOS и ZX Next, с международным составом участников.",
  links: [{
    label: "speccy.pl",
    icon: "S"
  }, {
    label: "demozoo.org",
    icon: "D"
  }],
  compos: [{
    id: 11,
    type: "intro",
    name: "256б интро",
    entries: [{
      id: 598823,
      title: "found.256",
      by: ["DKT"],
      place: 1,
      stars: 3.9,
      votes: 2,
      hw: ["TR-DOS", "48"]
    }, {
      id: 598819,
      title: "EXP LN 256",
      by: ["Domel"],
      place: 2,
      stars: 4.0,
      votes: 2,
      hw: ["TR-DOS", "48"]
    }, {
      id: 598813,
      title: "org.asm",
      by: ["FOYM"],
      place: 3,
      stars: 3.8,
      votes: 3,
      hw: ["TR-DOS", "48"]
    }, {
      id: 598809,
      title: "Not A QR code",
      by: ["Monster"],
      place: 4,
      stars: 4.0,
      votes: 2,
      hw: ["TR-DOS", "48"]
    }, {
      id: 598805,
      title: "Manifesto",
      by: ["Monster"],
      place: 5,
      stars: 3.9,
      votes: 1,
      hw: ["TR-DOS", "48"]
    }]
  }, {
    id: 12,
    type: "demo",
    name: "Расширенное демо",
    entries: [{
      id: 598585,
      title: "NullForm",
      by: ["Virtual Vision Group"],
      place: 1,
      stars: 4.4,
      votes: 9,
      hw: ["Next", "Next-DAC"]
    }, {
      id: 598802,
      title: "Rainbow 2",
      by: ["GDC"],
      place: 2,
      stars: 3.9,
      votes: 1,
      hw: ["Next"]
    }]
  }, {
    id: 13,
    type: "demo",
    name: "Демо",
    entries: [{
      id: 598832,
      title: "Invitro speccy.pl party 2026",
      by: ["varna"],
      place: 2,
      stars: 4.0,
      votes: 2,
      hw: ["TR-DOS", "48"]
    }, {
      id: 598487,
      title: "PowerRun",
      by: ["Dmitry_Milk"],
      place: 3,
      stars: 4.1,
      votes: 4,
      hw: ["48", "TR-DOS"]
    }, {
      id: 598827,
      title: "Z drugiej strony",
      by: ["CrapTeam"],
      place: 4,
      stars: 4.0,
      votes: 2,
      hw: ["TR-DOS", "48"]
    }]
  }, {
    id: 14,
    type: "gfx",
    name: "Wild (Графика)",
    entries: [{
      id: 598801,
      title: "No Microslop",
      by: ["Monster"],
      place: 3,
      stars: 4.0,
      votes: 2,
      format: "Standard"
    }, {
      id: 598860,
      title: "Warsaw Lights",
      by: ["Yerry"],
      place: 1,
      stars: 4.3,
      votes: 3,
      format: "Multicolor"
    }, {
      id: 598861,
      title: "Pixel Pierogi",
      by: ["Bocianu"],
      place: 2,
      stars: 4.1,
      votes: 2,
      format: "Standard"
    }, {
      id: 598862,
      title: "Vistula",
      by: ["KAaA"],
      place: 4,
      stars: 3.6,
      votes: 1,
      format: "Standard"
    }]
  }, {
    id: 15,
    type: "ay",
    name: "Музыка AY",
    entries: [{
      id: 598870,
      title: "Mazovia",
      by: ["Tygrys"],
      place: 1,
      stars: 4.2,
      votes: 3,
      chip: "AY",
      duration: "3:12"
    }, {
      id: 598871,
      title: "Warsaw Beat",
      by: ["C0ldness"],
      place: 2,
      stars: 3.9,
      votes: 2,
      chip: "AY",
      duration: "2:44"
    }, {
      id: 598872,
      title: "Retro Heart",
      by: ["Voyager"],
      place: 3,
      stars: 3.7,
      votes: 2,
      chip: "AY",
      duration: "3:30"
    }, {
      id: 598873,
      title: "Demoscener",
      by: ["DKT"],
      place: 4,
      stars: 3.4,
      votes: 1,
      chip: "AY",
      duration: "2:20"
    }]
  }],
  comments: [{
    id: 1,
    by: "monster",
    date: "2026-05-07",
    compo: "256б интро",
    workTitle: "Manifesto",
    body: "Два интро в одной компо — рискнул и не жалею. Спасибо за голоса!"
  }, {
    id: 2,
    by: "rcl",
    date: "2026-05-06",
    compo: "Расширенное демо",
    workTitle: "NullForm",
    body: "Next-DAC раскрывается полностью. Звук — отдельный респект Crash Complex."
  }],
  votes: [{
    id: 1,
    by: "dmitry_milk",
    date: "2026-05-05",
    compo: "Демо",
    workTitle: "PowerRun",
    score: 5
  }, {
    id: 2,
    by: "monster",
    date: "2026-05-07",
    compo: "256б интро",
    workTitle: "found.256",
    score: 4
  }, {
    id: 3,
    by: "voyager",
    date: "2026-05-06",
    compo: "Расш. демо",
    workTitle: "NullForm",
    score: 5
  }]
};

/* ══════════════════════════════════════════════════════════════════════════
   PRESET 3 — Chiptune Compo (tiny online event)
   ══════════════════════════════════════════════════════════════════════════ */
const ONLINE = {
  id: 71010,
  name: "ZX Online Chiptune Compo #4",
  abbr: "zoc4",
  type: "Онлайн-компо",
  country: "Интернет",
  city: "",
  year: 2024,
  summary: "Маленький онлайн-конкурс на три категории: лоу-рез графика, крошечное интро и " + "чиптюн. Голосование среди участников, без живой площадки.",
  links: [{
    label: "Стрим (VOD)",
    icon: "Y"
  }],
  compos: [{
    id: 31,
    type: "intro",
    name: "Combined Интро",
    entries: [{
      id: 71101,
      title: "spinner",
      by: ["nooly"],
      place: 1,
      stars: 4.1,
      votes: 7,
      hw: ["128"]
    }, {
      id: 71102,
      title: "twist",
      by: ["kost"],
      place: 2,
      stars: 3.8,
      votes: 5,
      hw: ["48"]
    }, {
      id: 71103,
      title: "dot",
      by: ["random"],
      place: 3,
      stars: 3.3,
      votes: 4,
      hw: ["48"]
    }]
  }, {
    id: 32,
    type: "ay",
    name: "Чиптюн",
    entries: [{
      id: 71201,
      title: "Modem Song",
      by: ["tq"],
      place: 1,
      stars: 4.4,
      votes: 9,
      chip: "AY",
      duration: "2:48"
    }, {
      id: 71202,
      title: "Dial Up",
      by: ["Beat"],
      place: 2,
      stars: 4.0,
      votes: 6,
      chip: "AY",
      duration: "2:12"
    }, {
      id: 71203,
      title: "Static",
      by: ["feen"],
      place: 3,
      stars: 3.5,
      votes: 4,
      chip: "Beeper",
      duration: "1:40"
    }]
  }, {
    id: 33,
    type: "gfx",
    name: "Лоу-рез Графика",
    entries: [{
      id: 71301,
      title: "Tiny Sunset",
      by: ["feen"],
      place: 1,
      stars: 4.3,
      votes: 8,
      format: "Standard"
    }, {
      id: 71302,
      title: "Mono Cat",
      by: ["q-bee"],
      place: 2,
      stars: 3.9,
      votes: 5,
      format: "Standard"
    }, {
      id: 71303,
      title: "Grid",
      by: ["kost"],
      place: 3,
      stars: 3.4,
      votes: 3,
      format: "Standard"
    }]
  }],
  comments: [{
    id: 1,
    by: "diver4d",
    date: "2024-03-18",
    compo: "Чиптюн",
    workTitle: "Modem Song",
    body: "Звук модема в качестве лида — гениально. tq не промахивается."
  }],
  votes: [{
    id: 1,
    by: "diver4d",
    date: "2024-03-18",
    compo: "Чиптюн",
    workTitle: "Modem Song",
    score: 5
  }, {
    id: 2,
    by: "nooly",
    date: "2024-03-17",
    compo: "Интро",
    workTitle: "spinner",
    score: 4
  }]
};
const PARTY_PRESETS = {
  dihalt: DIHALT,
  spl: SPL,
  online: ONLINE
};

/* Russian plural helper (party scope) */
function pluralRuP(n, [one, few, many]) {
  const m10 = n % 10,
    m100 = n % 100;
  if (m10 === 1 && m100 !== 11) return one;
  if (m10 >= 2 && m10 <= 4 && (m100 < 12 || m100 > 14)) return few;
  return many;
}

/* Deterministic engagement metrics per entry (decorrelated from place so that
   re-sorting actually reorders). views — for prods & pictures, launches — for
   prods, plays — for tunes. */
function entryMetric(e, name) {
  const id = e.id || 0,
    v = e.votes || 0;
  if (name === "views") return 140 + id % 900 + v * 45;
  if (name === "launches") return 30 + id % 240 + v * 14;
  if (name === "plays") return 60 + id % 600 + v * 30;
  return 0;
}

/* compact count formatting (1.2k) */
function fmtCount(n) {
  return n >= 1000 ? (n / 1000).toFixed(1).replace(/\.0$/, "") + "k" : String(n);
}
Object.assign(window, {
  PARTY_PRESETS,
  COMPO_MEDIA,
  paletteForP,
  pluralRuP,
  entryMetric,
  fmtCount
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/party-data.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/picture-data.jsx
try { (() => {
/* Data for a single ZX Spectrum picture page — "Night Run" by Diver / 4D + Andy / CFM.
   Picked because it is co-authored, sits inside a demo (so the "from the same
   prod" rail is populated), has a party placing, reference materials and a
   drawing-stages animation — i.e. it exercises every section of the page.
   The minimal-case props in PicturePage strip all of this back to image+meta. */

const PICTURE = {
  id: 30142,
  // shown as #30142
  title: "Night Run",
  authors: [{
    id: 41,
    name: "Diver / 4D"
  }, {
    id: 88,
    name: "Andy / CFM"
  }],
  year: 2021,
  format: "Standard",
  // ZX render mode
  resolution: "256×192",
  palette: "ZX Spectrum · 16 цветов",
  border: "чёрный",
  realtime: false,
  flickering: false,
  multicolor: false,
  party: {
    id: 5521,
    name: "Chaos Constructions 2021",
    compo: "ZX Realtime Graphics",
    place: 1
  },
  prod: {
    id: 9001,
    title: "Across the Edge",
    kind: "демо",
    year: 2021,
    by: "4D",
    palette: "cool"
  },
  tags: ["город", "ночь", "неон", "sci-fi", "дизеринг", "авто", "киберпанк"],
  rating: 4.6,
  votes: 38,
  views: 2417,
  myVote: 5,
  fav: false,
  addedBy: {
    id: 7,
    name: "g0blinish"
  },
  addedAt: "14 авг 2021",
  file: {
    name: "night_run.scr",
    bytes: 6912,
    attrW: 32,
    attrH: 24,
    depth: "монохром + atribute"
  }
};

/* Download formats — original ZX file + rasterised PC versions + print master */
const DOWNLOADS = [{
  id: "scr",
  kind: "zx",
  label: "Оригинал .scr",
  sub: "ZX Spectrum · 256×192 · 6912 байт",
  size: "6,75 КБ",
  ext: "SCR"
}, {
  id: "png1",
  kind: "png",
  label: "PNG · 1×",
  sub: "256×192 px · точные пиксели",
  size: "3,1 КБ",
  ext: "PNG"
}, {
  id: "png2",
  kind: "png",
  label: "PNG · 2×",
  sub: "512×384 px · без сглаживания",
  size: "7,8 КБ",
  ext: "PNG"
}, {
  id: "png3",
  kind: "png",
  label: "PNG · 3×",
  sub: "768×576 px · без сглаживания",
  size: "14,2 КБ",
  ext: "PNG"
}, {
  id: "a4",
  kind: "print",
  label: "Для печати · A4",
  sub: "2480×3508 px · 300 dpi · CMYK-safe",
  size: "1,3 МБ",
  ext: "PNG"
}];

/* Reference / inspiration materials — usually absent, present here. */
const MATERIALS = [{
  id: 1,
  kind: "photo",
  label: "Фотореференс",
  sub: "ночной город, неон",
  palette: "night"
}, {
  id: 2,
  kind: "sketch",
  label: "Карандашный набросок",
  sub: "композиция кадра",
  palette: "default"
}];
const STAGE_COUNT = 24; // this work captured 24 stages; range in archive is 5..50

const VOTES = [{
  date: "02.11.2021",
  user: "restorer",
  score: 5
}, {
  date: "28.10.2021",
  user: "Shiru",
  score: 5
}, {
  date: "19.10.2021",
  user: "Andy / CFM",
  score: 4
}, {
  date: "30.09.2021",
  user: "key-jee",
  score: 5
}, {
  date: "12.09.2021",
  user: "g0blinish",
  score: 4
}, {
  date: "05.09.2021",
  user: "Excess Team",
  score: 5
}];
const COMMENTS = [{
  id: 1,
  user: "restorer",
  date: "02 ноя 2021",
  body: "Дизеринг неба — отдельный вид искусства. На реальном Пентагоне смотрится ещё сочнее, чем в эмуляторе."
}, {
  id: 2,
  user: "Shiru",
  date: "28 окт 2021",
  body: "Как вы развели колор-клэш на отражениях в лужах? Там же по атрибутам должно было всё поплыть."
}, {
  id: 3,
  user: "Diver / 4D",
  date: "29 окт 2021",
  body: "Спасибо! Лужи рисовали по сетке 8×8, чтобы каждая блестка попадала в свой знакоместо. Заняло половину времени работы."
}];

/* Related rails — kept short on purpose. */
const FROM_PROD = [{
  id: 9011,
  title: "Across the Edge · загрузочный",
  authors: "Diver / 4D",
  year: 2021,
  palette: "cool",
  place: null
}, {
  id: 9012,
  title: "Тоннель",
  authors: "Andy / CFM",
  year: 2021,
  palette: "night",
  place: null
}, {
  id: 9013,
  title: "Финальный кадр",
  authors: "Diver / 4D",
  year: 2021,
  palette: "sunset",
  place: null
}];
const BY_AUTHOR = [{
  id: 8801,
  title: "Rainy Boulevard",
  authors: "Diver / 4D",
  year: 2020,
  palette: "cool",
  place: 2
}, {
  id: 8802,
  title: "Subway",
  authors: "Diver / 4D",
  year: 2019,
  palette: "night",
  place: 1
}, {
  id: 8803,
  title: "Static",
  authors: "Diver / 4D",
  year: 2022,
  palette: "default",
  place: null
}, {
  id: 8804,
  title: "Dawnfall",
  authors: "Diver / 4D",
  year: 2018,
  palette: "sunset",
  place: 3
}];
const BY_TAGS = [{
  id: 7001,
  title: "Neon Alley",
  authors: "wbc^iberia",
  year: 2020,
  palette: "sunset",
  place: null
}, {
  id: 7002,
  title: "Hovercar",
  authors: "Riskej",
  year: 2021,
  palette: "cool",
  place: null
}, {
  id: 7003,
  title: "Blade",
  authors: "Sergey Bo",
  year: 2022,
  palette: "night",
  place: 2
}, {
  id: 7004,
  title: "City Lights",
  authors: "g0th",
  year: 2019,
  palette: "default",
  place: null
}];

/* ─────────────────────────────────────────────────────────────
   makePixelArt — draws a deterministic 256×192 ZX-style night-city
   scene onto a canvas and returns a data URL. `stage` (0..3) controls
   how finished the picture is, so the same function feeds the hero and
   the drawing-stages animation:
     0 — pencil sketch (paper + ink outlines)
     1 — flat colour fills, no dithering, dark windows
     2 — + ordered dithering on sky & ground
     3 — + lit windows, stars, moon shading, neon (final)
   ───────────────────────────────────────────────────────────── */
const BAYER4 = [[0, 8, 2, 10], [12, 4, 14, 6], [3, 11, 1, 9], [15, 7, 13, 5]];
function _lcg(seed) {
  let x = seed >>> 0 || 1;
  return () => {
    x = x * 1664525 + 1013904223 >>> 0;
    return x / 0xffffffff;
  };
}
function _renderScene(stage = 3, W = 256, H = 192) {
  const cv = document.createElement("canvas");
  cv.width = W;
  cv.height = H;
  const g = cv.getContext("2d");
  const horizon = Math.round(H * 0.60);
  const C = {
    sky0: "#01030d",
    sky1: "#0c2f5e",
    glow: "#2a6fdb",
    star: "#ffffff",
    moon: "#fdf3cf",
    moonShade: "#cdbb8b",
    bld0: "#01060f",
    bld1: "#05132c",
    win: "#ffbd04",
    win2: "#4da9ff",
    winR: "#ff3b6b",
    ground0: "#020a14",
    ground1: "#0a1d3a",
    road: "#16305a",
    line: "#2a6fdb",
    paper: "#c7d0db",
    ink: "#3a4960"
  };

  // deterministic skyline
  const rnd = _lcg(20210914);
  const buildings = [];
  let bx = -6;
  while (bx < W + 6) {
    const bw = 10 + Math.floor(rnd() * 22);
    const bh = 18 + Math.floor(rnd() * 64);
    buildings.push({
      x: bx,
      w: bw,
      top: horizon - bh
    });
    bx += bw + (1 + Math.floor(rnd() * 3));
  }
  const moon = {
    x: 196,
    y: 40,
    r: 16
  };
  const px = (x, y, c) => {
    g.fillStyle = c;
    g.fillRect(x, y, 1, 1);
  };
  if (stage === 0) {
    // pencil sketch
    g.fillStyle = C.paper;
    g.fillRect(0, 0, W, H);
    g.strokeStyle = C.ink;
    g.lineWidth = 1;
    // horizon
    g.beginPath();
    g.moveTo(0, horizon + 0.5);
    g.lineTo(W, horizon + 0.5);
    g.stroke();
    // moon outline
    g.beginPath();
    g.arc(moon.x + 0.5, moon.y + 0.5, moon.r, 0, Math.PI * 2);
    g.stroke();
    // building outlines
    g.beginPath();
    buildings.forEach(b => {
      g.rect(b.x + 0.5, b.top + 0.5, b.w - 1, horizon - b.top);
    });
    g.stroke();
    // a few perspective road lines
    g.beginPath();
    for (let i = -2; i <= 2; i++) {
      g.moveTo(W / 2, horizon);
      g.lineTo(W / 2 + i * 90, H);
    }
    g.stroke();
    return cv;
  }
  const dith = (x, y, t) => t * 16 > BAYER4[y & 3][x & 3];

  // sky
  for (let y = 0; y < horizon; y++) {
    const t = y / horizon;
    for (let x = 0; x < W; x++) {
      let col;
      if (stage === 1) col = t < 0.55 ? C.sky0 : C.sky1;else col = dith(x, y, Math.pow(t, 1.1) * 0.95) ? C.sky1 : C.sky0;
      px(x, y, col);
    }
  }

  // moon
  for (let y = -moon.r; y <= moon.r; y++) {
    for (let x = -moon.r; x <= moon.r; x++) {
      if (x * x + y * y <= moon.r * moon.r) {
        const shade = stage >= 3 && x - y > moon.r * 0.45;
        px(moon.x + x, moon.y + y, shade ? C.moonShade : C.moon);
      }
    }
  }

  // stars
  if (stage >= 2) {
    const sr = _lcg(7777);
    const count = stage >= 3 ? 70 : 26;
    for (let i = 0; i < count; i++) {
      const x = Math.floor(sr() * W);
      const y = Math.floor(sr() * (horizon - 6));
      const dm = (moon.x - x) ** 2 + (moon.y - y) ** 2;
      if (dm > (moon.r + 6) ** 2) px(x, y, C.star);
    }
  }

  // horizon glow
  if (stage >= 2) {
    for (let x = 0; x < W; x++) {
      px(x, horizon - 1, dith(x, 1, 0.5) ? C.glow : C.sky1);
      px(x, horizon, C.glow);
    }
  }

  // buildings
  buildings.forEach((b, bi) => {
    const base = bi % 2 ? C.bld1 : C.bld0;
    for (let y = b.top; y < horizon; y++) {
      for (let x = b.x; x < b.x + b.w; x++) {
        if (x < 0 || x >= W) continue;
        px(x, y, base);
      }
    }
    // windows (only when finished)
    if (stage >= 3) {
      for (let wy = b.top + 3; wy < horizon - 2; wy += 4) {
        for (let wx = b.x + 2; wx < b.x + b.w - 2; wx += 4) {
          if (wx < 0 || wx >= W) continue;
          const r = _lcg(wx * 131 + wy * 17 + bi)();
          if (r < 0.42) {
            const c = r < 0.06 ? C.winR : r < 0.3 ? C.win : C.win2;
            px(wx, wy, c);
            px(wx + 1, wy, c);
          }
        }
      }
    }
  });

  // ground / road
  for (let y = horizon + 1; y < H; y++) {
    const t = (y - horizon) / (H - horizon);
    for (let x = 0; x < W; x++) {
      let col;
      if (stage === 1) col = C.ground0;else col = dith(x, y, 0.25 + t * 0.55) ? C.ground1 : C.ground0;
      px(x, y, col);
    }
  }
  // central road + perspective lane lines
  for (let y = horizon + 1; y < H; y++) {
    const t = (y - horizon) / (H - horizon);
    const halfw = 6 + t * 70;
    for (let x = Math.round(W / 2 - halfw); x < W / 2 + halfw; x++) {
      if (x < 0 || x >= W) continue;
      px(x, y, dith(x, y, 0.3 + t * 0.3) ? C.road : C.ground1);
    }
    // dashed centre line
    if (stage >= 2 && y % 6 < 3) {
      px(Math.round(W / 2), y, C.line);
      px(Math.round(W / 2) - 1, y, C.line);
    }
    // reflected window glints on the wet road
    if (stage >= 3 && y % 3 === 0) {
      const sr = _lcg(y * 53)();
      if (sr < 0.5) {
        const gx = Math.round(W / 2 + (sr - 0.25) * halfw * 2);
        px(gx, y, sr < 0.12 ? C.winR : C.win);
      }
    }
  }

  // neon taillights of a car heading away
  if (stage >= 3) {
    g.fillStyle = C.winR;
    g.fillRect(W / 2 - 5, H - 22, 3, 2);
    g.fillRect(W / 2 + 2, H - 22, 3, 2);
  }
  return cv;
}

/* Public: finished picture as a data URL (hero + thumbnails). */
function makePixelArt(stage = 3, W = 256, H = 192) {
  return _renderScene(stage, W, H).toDataURL();
}

/* Drawing-stages timelapse. Real works have anywhere from 5 to 50
   captured stages with no individual names, so we synthesise N frames
   from a single deterministic build: the finished scene is "painted"
   over the pencil sketch top-to-bottom as progress p goes 0→1, with a
   pencil frontier line at the paint edge. Works for any frameCount. */
let _sketchCv = null,
  _finalCv = null;
function makeStageFrame(p, W = 256, H = 192) {
  if (!_sketchCv) {
    _sketchCv = _renderScene(0, W, H);
    _finalCv = _renderScene(3, W, H);
  }
  const cv = document.createElement("canvas");
  cv.width = W;
  cv.height = H;
  const g = cv.getContext("2d");
  g.imageSmoothingEnabled = false;
  g.drawImage(_sketchCv, 0, 0);
  const frontier = Math.round(Math.max(0, Math.min(1, p)) * H);
  if (frontier > 0) g.drawImage(_finalCv, 0, 0, W, frontier, 0, 0, W, frontier);
  if (frontier > 0 && frontier < H) {
    g.fillStyle = "#3a4960";
    g.fillRect(0, frontier - 1, W, 1);
  }
  return cv.toDataURL();
}

/* Generate `n` evenly spaced timelapse frames (n between 5 and 50). */
function makeStageFrames(n) {
  n = Math.max(2, Math.min(50, n | 0));
  return Array.from({
    length: n
  }, (_, i) => makeStageFrame(i / (n - 1)));
}
window.PICTURE = PICTURE;
window.DOWNLOADS = DOWNLOADS;
window.MATERIALS = MATERIALS;
window.STAGE_COUNT = STAGE_COUNT;
window.VOTES = VOTES;
window.COMMENTS = COMMENTS;
window.FROM_PROD = FROM_PROD;
window.BY_AUTHOR = BY_AUTHOR;
window.BY_TAGS = BY_TAGS;
window.makePixelArt = makePixelArt;
window.makeStageFrames = makeStageFrames;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/picture-data.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/prod-data.jsx
try { (() => {
/* Real Crystal Kingdom Dizzy data, captured from zxart.ee */
const PROD = {
  title: "Crystal Kingdom Dizzy",
  alsoKnownAs: "Dizzy 7",
  category: ["Игры", "Приключения", "Квесты-головоломки"],
  langs: ["en"],
  status: "Распространение запрещено правообладателем",
  developer: "Visual Impact",
  publisher: "Code Masters Ltd",
  authors: ["Dave Thompson", "Jarrod Bentley"],
  music: "Reflective Designs",
  year: 1992,
  tags: ["Chalice", "Crystal Kingdom", "Grand Dizzy", "Storm", "Yolkfolk", "Деревня", "Диззи", "Дождь", "Корона", "Меч", "Храм", "Яйцо"],
  links: [{
    label: "Speccy Screenshot Maps",
    host: "speccy.pl"
  }, {
    label: "Spectrum Computing",
    host: "spectrumcomputing.co.uk"
  }, {
    label: "World Of Spectrum",
    host: "worldofspectrum.org"
  }, {
    label: "Virtual TR-DOS",
    host: "vtrd.in"
  }],
  rating: {
    score: 4.14,
    ofFive: 5,
    votes: 73
  },
  added: "18.11.2016",
  story: "«Crystal Kingdom Dizzy» — приключенческая игра-головоломка 1992 года, часть серии о Диззи. Диззи отправляется на поиски украденных сокровищ Йолкфолка: кристального меча, чаши и короны. Без них в Кристальном Королевстве происходят странные события. По пути герой решает головоломки, собирает предметы и встречает таких персонажей, как Game Genie, который выдаёт коды для перемещения между актами. Игра известна яркой графикой и насыщенным сюжетом; графику создал Джаррод Бентли, музыку — Reflective Designs.",
  series: {
    name: "Dizzy",
    count: 9
  }
};

/** All 18 releases. */
const RELEASES = [{
  id: 1,
  title: "Crystal Kingdom Dizzy",
  year: 1992,
  lang: "en",
  playOnline: true,
  type: "original",
  releasedBy: "Code Masters Ltd",
  hardware: ["ZX Spectrum 48K"],
  format: "TZX лента",
  downloads: 30,
  plays: 3,
  files: ["CrystalKingdomDizzy.txt", "CrystalKingdomDizzy(EN).pdf"],
  votes: 12,
  screens: ["a", "b", "c", "d", "e"]
}, {
  id: 2,
  title: "Crystal Kingdom Dizzy",
  year: 1992,
  lang: "en",
  playOnline: false,
  type: "unknown",
  releasedBy: "Code Masters Ltd",
  hardware: ["Интерфейс2 джойстик", "Кемпстон джойстик"],
  format: null,
  downloads: 0,
  plays: 0,
  files: ["CrystalKingdomDizzy.txt"],
  votes: 0,
  screens: []
}, {
  id: 3,
  title: "Dizzy 7: Crystal Kingdom",
  year: 1994,
  lang: "en",
  playOnline: true,
  type: "adaptation",
  releasedBy: "Studio 7",
  hardware: [],
  format: "SCL диск",
  downloads: 20,
  plays: 2,
  files: [],
  votes: 4,
  screens: ["a", "b"]
}, {
  id: 4,
  title: "Dizzy 7: Crystal Kingdom",
  year: 1994,
  lang: "en",
  playOnline: true,
  type: "adaptation",
  releasedBy: "ZSV",
  hardware: [],
  format: "SCL диск",
  downloads: 20,
  plays: 1,
  files: [],
  votes: 2,
  screens: ["a"]
}, {
  id: 5,
  title: "Dizzy 7: Crystal Kingdom",
  year: 1995,
  lang: "ru",
  playOnline: true,
  type: "translation",
  releasedBy: "Scorpion Soft",
  hardware: [],
  format: "SCL диск",
  downloads: 27,
  plays: 3,
  files: [],
  votes: 5,
  screens: ["a", "b", "c"]
}, {
  id: 6,
  title: "Dizzy 7: Crystal Kingdom",
  year: 1995,
  lang: "ru",
  playOnline: true,
  type: "translation",
  releasedBy: "Softstar, FFC Computers",
  hardware: [],
  format: "SCL диск",
  downloads: 20,
  plays: 1,
  files: [],
  votes: 1,
  screens: ["a"]
}, {
  id: 7,
  title: "Dizzy 7: Crystal Kingdom",
  year: 1995,
  lang: "ru",
  playOnline: true,
  type: "translation",
  releasedBy: "Madness Coders Group",
  hardware: [],
  format: "SCL диск",
  downloads: 18,
  plays: 1,
  files: [],
  votes: 1,
  screens: ["a"]
}, {
  id: 8,
  title: "Dizzy 7: Crystal Kingdom",
  year: 1995,
  lang: "en",
  playOnline: true,
  type: "adaptation",
  releasedBy: "Damage Inc",
  hardware: [],
  format: "SCL диск",
  downloads: 20,
  plays: 1,
  files: [],
  votes: 1,
  screens: ["a"]
}, {
  id: 9,
  title: "Dizzy 7: Crystal Kingdom",
  year: 1995,
  lang: "en",
  playOnline: true,
  type: "adaptation",
  releasedBy: "Studio Scorpion Group",
  hardware: [],
  format: "SCL диск",
  downloads: 20,
  plays: 1,
  files: [],
  votes: 1,
  screens: ["a"]
}, {
  id: 10,
  title: "Dizzy 7: Crystal Kingdom",
  year: 1995,
  lang: "en",
  playOnline: true,
  type: "adaptation",
  releasedBy: "Softstar",
  hardware: [],
  format: "SCL диск",
  downloads: 20,
  plays: 1,
  files: [],
  votes: 1,
  screens: ["a"]
}, {
  id: 11,
  title: "Dizzy 7: Crystal Kingdom",
  year: 1995,
  lang: "en",
  playOnline: true,
  type: "adaptation",
  releasedBy: "Dr. Bars",
  hardware: [],
  format: "SCL диск",
  downloads: 21,
  plays: 1,
  files: [],
  votes: 1,
  screens: ["a"]
}, {
  id: 12,
  title: "Dizzy 7: Crystal Kingdom",
  year: 1995,
  lang: "en",
  playOnline: true,
  type: "adaptation",
  releasedBy: "Владислав Кропачев",
  hardware: [],
  format: "SCL диск",
  downloads: 20,
  plays: 1,
  files: [],
  votes: 1,
  screens: ["a"]
}, {
  id: 13,
  title: "Crystal Kingdom Dizzy",
  year: 2009,
  lang: "en",
  playOnline: true,
  type: "modification",
  note: "new gfx",
  releasedBy: "Jarrod Bentley",
  hardware: [],
  format: "TZX лента",
  downloads: 22,
  plays: 2,
  files: [],
  votes: 6,
  screens: ["a", "b", "c"]
}, {
  id: 14,
  title: "Dizzy 7: Crystal Kingdom",
  year: null,
  lang: "ru",
  playOnline: true,
  type: "translation",
  releasedBy: "Prospekt",
  hardware: [],
  format: "SCL диск",
  downloads: 18,
  plays: 1,
  files: [],
  votes: 1,
  screens: ["a"]
}, {
  id: 15,
  title: "Dizzy 7: Crystal Kingdom",
  year: null,
  lang: "en",
  playOnline: true,
  type: "adaptation",
  releasedBy: null,
  hardware: ["AY-3-8910/12, YM2149F"],
  format: "SCL диск",
  downloads: 19,
  plays: 1,
  files: [],
  votes: 1,
  screens: ["a"],
  note: "+ Alternative AY Music"
}, {
  id: 16,
  title: "Crystal Kingdom Dizzy",
  year: null,
  lang: "ru",
  playOnline: true,
  type: "crack",
  releasedBy: "Prospekt",
  hardware: [],
  format: "TAP лента",
  downloads: 13,
  plays: 0,
  files: [],
  votes: 0,
  screens: []
}, {
  id: 17,
  title: "Crystal Kingdom Dizzy",
  year: null,
  lang: "en",
  playOnline: true,
  type: "crack",
  releasedBy: null,
  hardware: [],
  format: "TAP лента",
  downloads: 9,
  plays: 2,
  files: [],
  votes: 0,
  screens: []
}];
const RELEASE_TYPES = {
  original: {
    label: "Оригинальный",
    color: "var(--primary-500)"
  },
  adaptation: {
    label: "Адаптация",
    color: "var(--primary-700)"
  },
  translation: {
    label: "Перевод",
    color: "var(--warning-700)"
  },
  modification: {
    label: "Модификация",
    color: "var(--warning-500)"
  },
  crack: {
    label: "Взломанный",
    color: "var(--danger-500)"
  },
  unknown: {
    label: "Неизвестный",
    color: "var(--secondary-500)"
  }
};

/** 46 procedural screen seeds — stand in for the real .gif/.scr */
const SCREENS = Array.from({
  length: 46
}, (_, i) => ({
  id: 100 + i,
  palette: ["sunset", "cool", "forest", "night", "default"][i % 5]
}));

/** Music tracks */
const PROD_TUNES = [{
  id: 1,
  idx: 1,
  title: "128K Title 1",
  author: "David Whittaker",
  chip: "AY",
  year: 1992,
  plays: 921,
  stars: 3,
  duration: "0:46"
}, {
  id: 2,
  idx: 2,
  title: "128K In-Game 2",
  author: "David Whittaker",
  chip: "AY",
  year: 1992,
  plays: 221,
  stars: 5,
  duration: "2:11"
}, {
  id: 3,
  idx: 3,
  title: "128K Game Over 3",
  author: "David Whittaker",
  chip: "AY",
  year: 1992,
  plays: 8,
  stars: 3,
  duration: "0:18"
}, {
  id: 4,
  idx: 4,
  title: "128K Jingle 4",
  author: "David Whittaker",
  chip: "AY",
  year: 1992,
  plays: 29,
  stars: 3,
  duration: "0:11"
}];

/** Mentions in articles */
const MENTIONS = [{
  mag: "Spectrofon",
  issue: 13,
  year: 1995,
  section: "Обзор",
  body: "Обзор симуляторов и спортивных игр для ZX Spectrum, включая ‘F-19 Stealth Fighter’, ‘Carrier Command’, ‘Dizzy 7’, ‘Hudson Hawk’ и ‘Magic Johnson’s Basketball’."
}, {
  mag: "ZX Format",
  issue: 1,
  year: 1995,
  section: "Игрушки",
  body: "Подробное прохождение игры Crystal Kingdom Dizzy, включая решения головоломок и советы по продвижению в сюжете."
}];
const COMPILATIONS = [{
  format: "TR-DOS",
  title: "Dizzy Collection",
  by: "Flash Inc",
  count: 48,
  year: 1995
}, {
  format: "AY/YM",
  title: "Dizzy Compilation",
  by: null,
  count: null,
  year: null
}, {
  format: null,
  title: "Dizzy Super Adventure Codemasters' Kollektion",
  by: null,
  count: 5,
  year: 2010
}, {
  format: null,
  title: "Dizzy 1,2,3,3.5,4,5,6,7 + help",
  by: "The Legacy",
  count: 5,
  year: null
}];
const VOTES = [{
  user: "Jarrod Bentley",
  year: 1992,
  target: "Crystal Kingdom Dizzy",
  score: 5
}, {
  user: "Softstar",
  year: 1992,
  target: "Crystal Kingdom Dizzy",
  score: 3
}, {
  user: "Роман Таджиев",
  year: 1995,
  target: "Crystal Kingdom Dizzy",
  score: null
}, {
  user: "Jarrod Bentley",
  year: 2009,
  target: "Crystal Kingdom Dizzy 2009 version",
  score: 5
}];
const MAPS = [{
  author: "Tommy Pereira"
}];
const SAME_SERIES = [{
  title: "Dizzy",
  by: "The Oliver Twins, Code Masters Ltd",
  year: 1987,
  score: 5,
  hardware: ["48", "Кемп.", "AY/YM"]
}, {
  title: "Treasure Island Dizzy",
  by: "The Oliver Twins, Code Masters Ltd",
  year: 1988,
  score: 5,
  hardware: ["Кемп.", "48", "128+2", "128+3", "AY/YM", "DMA"]
}, {
  title: "Fantasy World Dizzy",
  by: "The Oliver Twins, Code Masters Ltd",
  year: 1989,
  score: 5,
  hardware: ["Кемп.", "48", "128+2", "128+3"]
}, {
  title: "Dizzy 3 and a Half",
  by: "Code Masters Ltd",
  year: 1991,
  score: 5,
  hardware: ["AY/YM", "Кемп.", "48"]
}, {
  title: "Magicland Dizzy",
  by: "Big Red Software Ltd",
  year: 1990,
  score: 5,
  hardware: ["Кемп.", "48"]
}, {
  title: "Spellbound Dizzy",
  by: "Big Red Software Ltd",
  year: 1991,
  score: 5,
  hardware: ["128", "48", "AY/YM", "Кемп."]
}, {
  title: "Dizzy, Prince of the YolkFolk",
  by: "Big Red Software Ltd",
  year: 1991,
  score: 5,
  hardware: ["GS", "128", "48", "AY/YM", "Кемп."]
}, {
  title: "Wonderful Dizzy",
  by: "The Oliver Twins",
  year: 2020,
  score: null,
  hardware: ["128", "TR-DOS", "AY/YM", "ULA+", "+3DOS", "128+3"]
}];
Object.assign(window, {
  PROD,
  RELEASES,
  RELEASE_TYPES,
  SCREENS,
  PROD_TUNES,
  MENTIONS,
  COMPILATIONS,
  VOTES,
  MAPS,
  SAME_SERIES
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/prod-data.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/release-data.jsx
try { (() => {
/* Data for a single release page — release #5 of "Crystal Kingdom Dizzy":
   the 1995 Russian translation by Scorpion Soft. Picked because it has
   non-trivial publishers, hardware, and a translation status. */

const RELEASE = {
  id: 547,
  // shown as #R-00547
  catalog: "R-00547",
  title: "Dizzy 7: Crystal Kingdom",
  prod: {
    id: 12871,
    title: "Crystal Kingdom Dizzy",
    alsoKnownAs: "Dizzy 7",
    year: 1992,
    authors: ["Dave Thompson", "Jarrod Bentley"],
    cover: "default" // ZxScreen palette
  },
  year: 1995,
  type: "translation",
  // original | adaptation | translation | modification | crack | unknown
  lang: "ru",
  status: {
    code: "unknown",
    label: "Статус распространения не определён",
    tone: "warn"
  },
  publishers: [{
    id: 211,
    name: "Scorpion Soft",
    role: "Издатель"
  }, {
    id: 412,
    name: "BridgeSoft",
    role: "Перевод"
  }],
  hardware: [{
    id: "zx128",
    name: "ZX Spectrum 128K"
  }, {
    id: "ay",
    name: "AY-3-8910 / YM2149F"
  }, {
    id: "kempston",
    name: "Кемпстон джойстик"
  }, {
    id: "trd",
    name: "TR-DOS"
  }],
  format: "SCL диск",
  size: "42 КБ",
  downloads: 27,
  plays: 3,
  addedAt: "12.03.2017",
  addedBy: "Roman Tadzhiev",
  votes: {
    score: 4.6,
    count: 5
  },
  description: "Русскоязычная адаптация финальной части Йолкфолк-цикла. Перевод текстов меню, диалогов с Game Genie и подсказок выполнен Scorpion Soft в сотрудничестве с BridgeSoft. Графика и музыка не изменены относительно оригинала Code Masters; раскладка управления адаптирована под джойстик Кемпстон."
};

/* Cassette / disk covers attached to this release. Each is a fake 3:4 image. */
const COVERS = [{
  id: 1,
  kind: "front",
  label: "Обложка кассеты (лицевая)",
  palette: "sunset",
  file: "ckd-scorpion-front.jpg",
  size: "0.4 МБ"
}, {
  id: 2,
  kind: "back",
  label: "Обложка кассеты (обратная)",
  palette: "cool",
  file: "ckd-scorpion-back.jpg",
  size: "0.3 МБ"
}, {
  id: 3,
  kind: "label",
  label: "Наклейка SCL-диска",
  palette: "forest",
  file: "ckd-scorpion-label.png",
  size: "0.1 МБ"
}];

/* Screenshots specific to this release (3 of them — Russian text visible). */
const REL_SCREENS = [{
  id: 11,
  palette: "forest",
  file: "screen-01.scr",
  size: "6.9 КБ"
}, {
  id: 12,
  palette: "sunset",
  file: "screen-02.scr",
  size: "6.9 КБ"
}, {
  id: 13,
  palette: "cool",
  file: "screen-03.scr",
  size: "6.9 КБ"
}, {
  id: 14,
  palette: "night",
  file: "screen-04.scr",
  size: "6.9 КБ"
}, {
  id: 15,
  palette: "default",
  file: "screen-05.scr",
  size: "6.9 КБ"
}];

/* Inline instructions (downloadable). */
const INSTRUCTIONS = [{
  lang: "ru",
  title: "Инструкция и прохождение",
  file: "CKD-Scorpion.RU.txt",
  size: "12 КБ"
}, {
  lang: "en",
  title: "Walkthrough (original)",
  file: "CKD-Walkthrough.EN.pdf",
  size: "0.8 МБ"
}];

/* Comments on this specific release. */
const REL_COMMENTS = [{
  id: 1,
  user: "Roman Tadzhiev",
  date: "13.03.2017",
  body: "Лучший русский перевод. Шрифт почти не отличается от оригинального, в отличие от ZSV."
}, {
  id: 2,
  user: "Дима К.",
  date: "02.07.2019",
  body: "На клонах с TR-DOS пробуксовывает на 3-м акте. На 128K в эмуляторе всё ок."
}, {
  id: 3,
  user: "BridgeSoft",
  date: "18.09.2020",
  body: "У нас сохранилась наклейка диска в HQ — могу прислать, если кто соберёт переиздание."
}];

/* Per-release vote history. */
const REL_VOTES = [{
  user: "Roman Tadzhiev",
  date: "13.03.2017",
  score: 5
}, {
  user: "Дима К.",
  date: "02.07.2019",
  score: 4
}, {
  user: "AY-3",
  date: "11.11.2019",
  score: 5
}, {
  user: "scolopendrum",
  date: "04.02.2021",
  score: 4
}, {
  user: "BridgeSoft",
  date: "18.09.2020",
  score: 5
}];
const REL_TYPES = {
  original: {
    label: "Оригинал",
    tone: "primary"
  },
  adaptation: {
    label: "Адаптация",
    tone: "primary-deep"
  },
  translation: {
    label: "Перевод",
    tone: "warn"
  },
  modification: {
    label: "Модификация",
    tone: "warn-soft"
  },
  crack: {
    label: "Взлом",
    tone: "danger"
  },
  unknown: {
    label: "Неизвестный",
    tone: "neutral"
  }
};

/* File tree inside the release archive. Always present.
   d = depth, kind = file|folder, viewable = text/image that has a viewer. */
const FILE_TREE = [{
  d: 0,
  name: "ckd-scorpion-1995.zip",
  size: 42068,
  kind: "zip"
}, {
  d: 1,
  name: "CKD-Scorpion-1995",
  size: 5,
  kind: "folder"
}, {
  d: 2,
  name: "DISK.scl",
  size: 40960,
  kind: "file",
  ext: "SCL образ"
}, {
  d: 2,
  name: "readme_ru.txt",
  size: 1247,
  kind: "file",
  ext: "Текст",
  viewable: true
}, {
  d: 2,
  name: "readme_en.txt",
  size: 892,
  kind: "file",
  ext: "Текст",
  viewable: true
}, {
  d: 2,
  name: "cover",
  size: 2,
  kind: "folder"
}, {
  d: 3,
  name: "front.png",
  size: 412531,
  kind: "file",
  ext: "Изображение",
  viewable: true
}, {
  d: 3,
  name: "back.png",
  size: 387204,
  kind: "file",
  ext: "Изображение",
  viewable: true
}];

/* Plain-text preview body used by the instruction modal. */
const README_RU = `CRYSTAL KINGDOM DIZZY (Dizzy 7) — русская версия
================================================
Перевод и сборка: Scorpion Soft, 1995
Перевод текстов: BridgeSoft

УПРАВЛЕНИЕ
  Q / A / O / P  — движение
  M              — взять / положить предмет
  N              — использовать предмет
  Space          — прыгать
  H              — переключить героя (Daisy)

СОВЕТЫ
  • Поговорите с Game Genie в начале каждого акта — он подскажет коды.
  • Кристальный меч можно найти в храме после того, как наполните чашу.
  • Перевод не меняет графику и музыку; всё работает на 128K с AY.

Если вы нашли опечатку — напишите на BBS Scorpion (095) xxx-xx-xx.`;
Object.assign(window, {
  RELEASE,
  COVERS,
  REL_SCREENS,
  INSTRUCTIONS,
  REL_COMMENTS,
  REL_VOTES,
  REL_TYPES,
  FILE_TREE,
  README_RU
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/release-data.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/tune-data.jsx
try { (() => {
/* Data for a single ZX Spectrum tune page — "Hibernation" by MmcM.
   Picked because it carries the full real-world metadata set (PT3 / AY YM2149F /
   Pentagon timings / Vortex Tracker II module / competition placing) and exercises
   every section of the page. Mirrors the structure of picture-data.jsx so the page
   stays consistent with the picture / release detail pages. */

const TUNE = {
  id: 84862,
  // shown as #84862 (matches the OGG id)
  title: "Hibernation",
  author: {
    id: 412,
    name: "MmcM"
  },
  year: 2016,
  format: "PT3",
  device: "Standard 3-канальный AY/YM",
  chip: "YM2149F",
  layout: "ABC",
  channels: 3,
  ayFreq: "1.75 МГц (Pentagon)",
  intFreq: "48.828125 Гц (Pentagon)",
  tracker: "Vortex Tracker II 1.0",
  duration: "1:36.74",
  durationSec: 96.74,
  metaTitle: "HibErNat!oN :: Re4Lt!mE DHLite16",
  metaAuthor: "MmcM^Sage 090120160459 ABC YM",
  filename: "2016_mmcm-hibernation_realtime.pt3",
  convertedBy: "ZXTune r4440",
  competition: {
    id: 6612,
    name: "DiHalt Lite 2016",
    compo: "Realtime AY",
    place: 1
  },
  tags: ["realtime", "AY", "ambient", "чилаут", "медитативное", "зима"],
  rating: 4.78,
  votes: 64,
  plays: 19853,
  myVote: 5,
  fav: false,
  addedBy: {
    id: 7,
    name: "diver"
  },
  addedAt: "10.01.2016 11:45"
};

/* Download formats — original tracker module + rendered audio.
   Most tunes ship only their native format; some additionally have an
   alternative original (e.g. .pt2 / .stc export) — none here, so it is omitted. */
const TUNE_DOWNLOADS = [{
  id: "pt3",
  kind: "zx",
  ext: "PT3",
  label: "Оригинал · .pt3",
  sub: "MmcM - Hibernation (2016) (DiHalt Lite 2016, 1).pt3",
  size: "3,1 КБ"
}, {
  id: "ogg",
  kind: "ogg",
  ext: "OGG",
  label: "Аудио · OGG Vorbis",
  sub: "84862_MmcM_Hibernation.ogg · отрендерено эмулятором",
  size: "2,1 МБ"
}];
const TUNE_VOTES = [{
  date: "21.02.2016",
  user: "nq",
  score: 5
}, {
  date: "03.02.2016",
  user: "Shiru",
  score: 5
}, {
  date: "27.01.2016",
  user: "Alex Rider",
  score: 4
}, {
  date: "18.01.2016",
  user: "key-jee",
  score: 5
}, {
  date: "12.01.2016",
  user: "diver",
  score: 5
}, {
  date: "10.01.2016",
  user: "g0blinish",
  score: 5
}];
const TUNE_COMMENTS = [{
  id: 1,
  user: "diver",
  date: "10 янв 2016",
  body: "Для реалтайма на DiHalt Lite — невероятно цельно. За полтора часа собрать такую атмосферу зимней спячки дорогого стоит. Заслуженное первое место."
}, {
  id: 2,
  user: "Shiru",
  date: "03 фев 2016",
  body: "Лид на канале C почти не движется, а напряжение держится за счёт огибающей шума. Красиво сделано, по-минималистски."
}, {
  id: 3,
  user: "MmcM^Sage",
  date: "04 фев 2016",
  body: "Спасибо! Half-step арпеджио на басу + длинный retrig по шуму — весь трек на трёх паттернах, чтобы успеть в регламент компо."
}];

/* The single production that uses this tune as its soundtrack.
   Only one prod references "Hibernation", so the page shows the whole card
   rather than a rail. */
const USED_IN = {
  id: 12044,
  title: "Sundown",
  kind: "Демо",
  authors: ["Sage", "MmcM", "diver", "g0blinish"],
  party: "DiHalt Lite 2016",
  place: 2,
  year: 2016,
  stars: 5,
  votes: 38,
  palette: "night"
};

/* Related rails — kept short on purpose, like the picture-page rails. */
const BY_AUTHOR_TUNES = [{
  id: 84410,
  title: "Frostbite",
  author: "MmcM",
  chip: "AY · PT3",
  duration: "2:18"
}, {
  id: 83992,
  title: "Polar Night",
  author: "MmcM",
  chip: "AY · PT3",
  duration: "1:47"
}, {
  id: 85230,
  title: "Deep Sleep",
  author: "MmcM",
  chip: "AY · PT3",
  duration: "3:02"
}, {
  id: 86001,
  title: "Awakening",
  author: "MmcM",
  chip: "AY · PT3",
  duration: "2:41"
}];
const BY_TAGS_TUNES = [{
  id: 71204,
  title: "Snowdrift",
  author: "Shiru",
  chip: "AY · PT3",
  duration: "2:55"
}, {
  id: 69880,
  title: "Still Air",
  author: "nq",
  chip: "AY · STC",
  duration: "1:33"
}, {
  id: 73551,
  title: "Glacier",
  author: "Alex Rider",
  chip: "AY · PT3",
  duration: "3:18"
}, {
  id: 70442,
  title: "White Noise EP",
  author: "key-jee",
  chip: "AY · PT3",
  duration: "2:09"
}];
const BY_TRACKER_TUNES = [{
  id: 84120,
  title: "Realtime DH16 #2",
  author: "g0blinish",
  chip: "AY · PT3",
  duration: "1:41"
}, {
  id: 82003,
  title: "Vortex Demo",
  author: "Sergey Bo",
  chip: "AY · PT3",
  duration: "2:27"
}, {
  id: 80991,
  title: "Module 7",
  author: "wbc",
  chip: "AY · PT3",
  duration: "1:58"
}, {
  id: 85777,
  title: "Pattern Study",
  author: "Riskej",
  chip: "AY · PT3",
  duration: "2:12"
}];
window.TUNE = TUNE;
window.USED_IN = USED_IN;
window.TUNE_DOWNLOADS = TUNE_DOWNLOADS;
window.TUNE_VOTES = TUNE_VOTES;
window.TUNE_COMMENTS = TUNE_COMMENTS;
window.BY_AUTHOR_TUNES = BY_AUTHOR_TUNES;
window.BY_TAGS_TUNES = BY_TAGS_TUNES;
window.BY_TRACKER_TUNES = BY_TRACKER_TUNES;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/tune-data.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/tweaks-panel.jsx
try { (() => {
// tweaks-panel.jsx
// Reusable Tweaks shell + form-control helpers.
//
// Owns the host protocol (listens for __activate_edit_mode / __deactivate_edit_mode,
// posts __edit_mode_available / __edit_mode_set_keys / __edit_mode_dismissed) so
// individual prototypes don't re-roll it. Ships a consistent set of controls so you
// don't hand-draw <input type="range">, segmented radios, steppers, etc.
//
// Usage (in an HTML file that loads React + Babel):
//
//   const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
//     "primaryColor": "#D97757",
//     "palette": ["#D97757", "#29261b", "#f6f4ef"],
//     "fontSize": 16,
//     "density": "regular",
//     "dark": false
//   }/*EDITMODE-END*/;
//
//   function App() {
//     const [t, setTweak] = useTweaks(TWEAK_DEFAULTS);
//     return (
//       <div style={{ fontSize: t.fontSize, color: t.primaryColor }}>
//         Hello
//         <TweaksPanel>
//           <TweakSection label="Typography" />
//           <TweakSlider label="Font size" value={t.fontSize} min={10} max={32} unit="px"
//                        onChange={(v) => setTweak('fontSize', v)} />
//           <TweakRadio  label="Density" value={t.density}
//                        options={['compact', 'regular', 'comfy']}
//                        onChange={(v) => setTweak('density', v)} />
//           <TweakSection label="Theme" />
//           <TweakColor  label="Primary" value={t.primaryColor}
//                        options={['#D97757', '#2A6FDB', '#1F8A5B', '#7A5AE0']}
//                        onChange={(v) => setTweak('primaryColor', v)} />
//           <TweakColor  label="Palette" value={t.palette}
//                        options={[['#D97757', '#29261b', '#f6f4ef'],
//                                  ['#475569', '#0f172a', '#f1f5f9']]}
//                        onChange={(v) => setTweak('palette', v)} />
//           <TweakToggle label="Dark mode" value={t.dark}
//                        onChange={(v) => setTweak('dark', v)} />
//         </TweaksPanel>
//       </div>
//     );
//   }
//
// ─────────────────────────────────────────────────────────────────────────────

const __TWEAKS_STYLE = `
  .twk-panel{position:fixed;right:16px;bottom:16px;z-index:2147483646;width:280px;
    max-height:calc(100vh - 32px);display:flex;flex-direction:column;
    transform:scale(var(--dc-inv-zoom,1));transform-origin:bottom right;
    background:rgba(250,249,247,.78);color:#29261b;
    -webkit-backdrop-filter:blur(24px) saturate(160%);backdrop-filter:blur(24px) saturate(160%);
    border:.5px solid rgba(255,255,255,.6);border-radius:14px;
    box-shadow:0 1px 0 rgba(255,255,255,.5) inset,0 12px 40px rgba(0,0,0,.18);
    font:11.5px/1.4 ui-sans-serif,system-ui,-apple-system,sans-serif;overflow:hidden}
  .twk-hd{display:flex;align-items:center;justify-content:space-between;
    padding:10px 8px 10px 14px;cursor:move;user-select:none}
  .twk-hd b{font-size:12px;font-weight:600;letter-spacing:.01em}
  .twk-x{appearance:none;border:0;background:transparent;color:rgba(41,38,27,.55);
    width:22px;height:22px;border-radius:6px;cursor:default;font-size:13px;line-height:1}
  .twk-x:hover{background:rgba(0,0,0,.06);color:#29261b}
  .twk-body{padding:2px 14px 14px;display:flex;flex-direction:column;gap:10px;
    overflow-y:auto;overflow-x:hidden;min-height:0;
    scrollbar-width:thin;scrollbar-color:rgba(0,0,0,.15) transparent}
  .twk-body::-webkit-scrollbar{width:8px}
  .twk-body::-webkit-scrollbar-track{background:transparent;margin:2px}
  .twk-body::-webkit-scrollbar-thumb{background:rgba(0,0,0,.15);border-radius:4px;
    border:2px solid transparent;background-clip:content-box}
  .twk-body::-webkit-scrollbar-thumb:hover{background:rgba(0,0,0,.25);
    border:2px solid transparent;background-clip:content-box}
  .twk-row{display:flex;flex-direction:column;gap:5px}
  .twk-row-h{flex-direction:row;align-items:center;justify-content:space-between;gap:10px}
  .twk-lbl{display:flex;justify-content:space-between;align-items:baseline;
    color:rgba(41,38,27,.72)}
  .twk-lbl>span:first-child{font-weight:500}
  .twk-val{color:rgba(41,38,27,.5);font-variant-numeric:tabular-nums}

  .twk-sect{font-size:10px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;
    color:rgba(41,38,27,.45);padding:10px 0 0}
  .twk-sect:first-child{padding-top:0}

  .twk-field{appearance:none;box-sizing:border-box;width:100%;min-width:0;height:26px;padding:0 8px;
    border:.5px solid rgba(0,0,0,.1);border-radius:7px;
    background:rgba(255,255,255,.6);color:inherit;font:inherit;outline:none}
  .twk-field:focus{border-color:rgba(0,0,0,.25);background:rgba(255,255,255,.85)}
  select.twk-field{padding-right:22px;
    background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'><path fill='rgba(0,0,0,.5)' d='M0 0h10L5 6z'/></svg>");
    background-repeat:no-repeat;background-position:right 8px center}

  .twk-slider{appearance:none;-webkit-appearance:none;width:100%;height:4px;margin:6px 0;
    border-radius:999px;background:rgba(0,0,0,.12);outline:none}
  .twk-slider::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;
    width:14px;height:14px;border-radius:50%;background:#fff;
    border:.5px solid rgba(0,0,0,.12);box-shadow:0 1px 3px rgba(0,0,0,.2);cursor:default}
  .twk-slider::-moz-range-thumb{width:14px;height:14px;border-radius:50%;
    background:#fff;border:.5px solid rgba(0,0,0,.12);box-shadow:0 1px 3px rgba(0,0,0,.2);cursor:default}

  .twk-seg{position:relative;display:flex;padding:2px;border-radius:8px;
    background:rgba(0,0,0,.06);user-select:none}
  .twk-seg-thumb{position:absolute;top:2px;bottom:2px;border-radius:6px;
    background:rgba(255,255,255,.9);box-shadow:0 1px 2px rgba(0,0,0,.12);
    transition:left .15s cubic-bezier(.3,.7,.4,1),width .15s}
  .twk-seg.dragging .twk-seg-thumb{transition:none}
  .twk-seg button{appearance:none;position:relative;z-index:1;flex:1;border:0;
    background:transparent;color:inherit;font:inherit;font-weight:500;min-height:22px;
    border-radius:6px;cursor:default;padding:4px 6px;line-height:1.2;
    overflow-wrap:anywhere}

  .twk-toggle{position:relative;width:32px;height:18px;border:0;border-radius:999px;
    background:rgba(0,0,0,.15);transition:background .15s;cursor:default;padding:0}
  .twk-toggle[data-on="1"]{background:#34c759}
  .twk-toggle i{position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;
    background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.25);transition:transform .15s}
  .twk-toggle[data-on="1"] i{transform:translateX(14px)}

  .twk-num{display:flex;align-items:center;box-sizing:border-box;min-width:0;height:26px;padding:0 0 0 8px;
    border:.5px solid rgba(0,0,0,.1);border-radius:7px;background:rgba(255,255,255,.6)}
  .twk-num-lbl{font-weight:500;color:rgba(41,38,27,.6);cursor:ew-resize;
    user-select:none;padding-right:8px}
  .twk-num input{flex:1;min-width:0;height:100%;border:0;background:transparent;
    font:inherit;font-variant-numeric:tabular-nums;text-align:right;padding:0 8px 0 0;
    outline:none;color:inherit;-moz-appearance:textfield}
  .twk-num input::-webkit-inner-spin-button,.twk-num input::-webkit-outer-spin-button{
    -webkit-appearance:none;margin:0}
  .twk-num-unit{padding-right:8px;color:rgba(41,38,27,.45)}

  .twk-btn{appearance:none;height:26px;padding:0 12px;border:0;border-radius:7px;
    background:rgba(0,0,0,.78);color:#fff;font:inherit;font-weight:500;cursor:default}
  .twk-btn:hover{background:rgba(0,0,0,.88)}
  .twk-btn.secondary{background:rgba(0,0,0,.06);color:inherit}
  .twk-btn.secondary:hover{background:rgba(0,0,0,.1)}

  .twk-swatch{appearance:none;-webkit-appearance:none;width:56px;height:22px;
    border:.5px solid rgba(0,0,0,.1);border-radius:6px;padding:0;cursor:default;
    background:transparent;flex-shrink:0}
  .twk-swatch::-webkit-color-swatch-wrapper{padding:0}
  .twk-swatch::-webkit-color-swatch{border:0;border-radius:5.5px}
  .twk-swatch::-moz-color-swatch{border:0;border-radius:5.5px}

  .twk-chips{display:flex;gap:6px}
  .twk-chip{position:relative;appearance:none;flex:1;min-width:0;height:46px;
    padding:0;border:0;border-radius:6px;overflow:hidden;cursor:default;
    box-shadow:0 0 0 .5px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.06);
    transition:transform .12s cubic-bezier(.3,.7,.4,1),box-shadow .12s}
  .twk-chip:hover{transform:translateY(-1px);
    box-shadow:0 0 0 .5px rgba(0,0,0,.18),0 4px 10px rgba(0,0,0,.12)}
  .twk-chip[data-on="1"]{box-shadow:0 0 0 1.5px rgba(0,0,0,.85),
    0 2px 6px rgba(0,0,0,.15)}
  .twk-chip>span{position:absolute;top:0;bottom:0;right:0;width:34%;
    display:flex;flex-direction:column;box-shadow:-1px 0 0 rgba(0,0,0,.1)}
  .twk-chip>span>i{flex:1;box-shadow:0 -1px 0 rgba(0,0,0,.1)}
  .twk-chip>span>i:first-child{box-shadow:none}
  .twk-chip svg{position:absolute;top:6px;left:6px;width:13px;height:13px;
    filter:drop-shadow(0 1px 1px rgba(0,0,0,.3))}
`;

// ── useTweaks ───────────────────────────────────────────────────────────────
// Single source of truth for tweak values. setTweak persists via the host
// (__edit_mode_set_keys → host rewrites the EDITMODE block on disk).
function useTweaks(defaults) {
  const [values, setValues] = React.useState(defaults);
  // Accepts either setTweak('key', value) or setTweak({ key: value, ... }) so a
  // useState-style call doesn't write a "[object Object]" key into the persisted
  // JSON block.
  const setTweak = React.useCallback((keyOrEdits, val) => {
    const edits = typeof keyOrEdits === 'object' && keyOrEdits !== null ? keyOrEdits : {
      [keyOrEdits]: val
    };
    setValues(prev => ({
      ...prev,
      ...edits
    }));
    window.parent.postMessage({
      type: '__edit_mode_set_keys',
      edits
    }, '*');
    // Same-window signal so in-page listeners (deck-stage rail thumbnails)
    // can react — the parent message only reaches the host, not peers.
    window.dispatchEvent(new CustomEvent('tweakchange', {
      detail: edits
    }));
  }, []);
  return [values, setTweak];
}

// ── TweaksPanel ─────────────────────────────────────────────────────────────
// Floating shell. Registers the protocol listener BEFORE announcing
// availability — if the announce ran first, the host's activate could land
// before our handler exists and the toolbar toggle would silently no-op.
// The close button posts __edit_mode_dismissed so the host's toolbar toggle
// flips off in lockstep; the host echoes __deactivate_edit_mode back which
// is what actually hides the panel.
function TweaksPanel({
  title = 'Tweaks',
  children
}) {
  const [open, setOpen] = React.useState(false);
  const dragRef = React.useRef(null);
  const offsetRef = React.useRef({
    x: 16,
    y: 16
  });
  const PAD = 16;
  const clampToViewport = React.useCallback(() => {
    const panel = dragRef.current;
    if (!panel) return;
    const w = panel.offsetWidth,
      h = panel.offsetHeight;
    const maxRight = Math.max(PAD, window.innerWidth - w - PAD);
    const maxBottom = Math.max(PAD, window.innerHeight - h - PAD);
    offsetRef.current = {
      x: Math.min(maxRight, Math.max(PAD, offsetRef.current.x)),
      y: Math.min(maxBottom, Math.max(PAD, offsetRef.current.y))
    };
    panel.style.right = offsetRef.current.x + 'px';
    panel.style.bottom = offsetRef.current.y + 'px';
  }, []);
  React.useEffect(() => {
    if (!open) return;
    clampToViewport();
    if (typeof ResizeObserver === 'undefined') {
      window.addEventListener('resize', clampToViewport);
      return () => window.removeEventListener('resize', clampToViewport);
    }
    const ro = new ResizeObserver(clampToViewport);
    ro.observe(document.documentElement);
    return () => ro.disconnect();
  }, [open, clampToViewport]);
  React.useEffect(() => {
    const onMsg = e => {
      const t = e?.data?.type;
      if (t === '__activate_edit_mode') setOpen(true);else if (t === '__deactivate_edit_mode') setOpen(false);
    };
    window.addEventListener('message', onMsg);
    window.parent.postMessage({
      type: '__edit_mode_available'
    }, '*');
    return () => window.removeEventListener('message', onMsg);
  }, []);
  const dismiss = () => {
    setOpen(false);
    window.parent.postMessage({
      type: '__edit_mode_dismissed'
    }, '*');
  };
  const onDragStart = e => {
    const panel = dragRef.current;
    if (!panel) return;
    const r = panel.getBoundingClientRect();
    const sx = e.clientX,
      sy = e.clientY;
    const startRight = window.innerWidth - r.right;
    const startBottom = window.innerHeight - r.bottom;
    const move = ev => {
      offsetRef.current = {
        x: startRight - (ev.clientX - sx),
        y: startBottom - (ev.clientY - sy)
      };
      clampToViewport();
    };
    const up = () => {
      window.removeEventListener('mousemove', move);
      window.removeEventListener('mouseup', up);
    };
    window.addEventListener('mousemove', move);
    window.addEventListener('mouseup', up);
  };
  if (!open) return null;
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("style", null, __TWEAKS_STYLE), /*#__PURE__*/React.createElement("div", {
    ref: dragRef,
    className: "twk-panel",
    "data-omelette-chrome": "",
    style: {
      right: offsetRef.current.x,
      bottom: offsetRef.current.y
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "twk-hd",
    onMouseDown: onDragStart
  }, /*#__PURE__*/React.createElement("b", null, title), /*#__PURE__*/React.createElement("button", {
    className: "twk-x",
    "aria-label": "Close tweaks",
    onMouseDown: e => e.stopPropagation(),
    onClick: dismiss
  }, "\u2715")), /*#__PURE__*/React.createElement("div", {
    className: "twk-body"
  }, children)));
}

// ── Layout helpers ──────────────────────────────────────────────────────────

function TweakSection({
  label,
  children
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "twk-sect"
  }, label), children);
}
function TweakRow({
  label,
  value,
  children,
  inline = false
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: inline ? 'twk-row twk-row-h' : 'twk-row'
  }, /*#__PURE__*/React.createElement("div", {
    className: "twk-lbl"
  }, /*#__PURE__*/React.createElement("span", null, label), value != null && /*#__PURE__*/React.createElement("span", {
    className: "twk-val"
  }, value)), children);
}

// ── Controls ────────────────────────────────────────────────────────────────

function TweakSlider({
  label,
  value,
  min = 0,
  max = 100,
  step = 1,
  unit = '',
  onChange
}) {
  return /*#__PURE__*/React.createElement(TweakRow, {
    label: label,
    value: `${value}${unit}`
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    className: "twk-slider",
    min: min,
    max: max,
    step: step,
    value: value,
    onChange: e => onChange(Number(e.target.value))
  }));
}
function TweakToggle({
  label,
  value,
  onChange
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "twk-row twk-row-h"
  }, /*#__PURE__*/React.createElement("div", {
    className: "twk-lbl"
  }, /*#__PURE__*/React.createElement("span", null, label)), /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "twk-toggle",
    "data-on": value ? '1' : '0',
    role: "switch",
    "aria-checked": !!value,
    onClick: () => onChange(!value)
  }, /*#__PURE__*/React.createElement("i", null)));
}
function TweakRadio({
  label,
  value,
  options,
  onChange
}) {
  const trackRef = React.useRef(null);
  const [dragging, setDragging] = React.useState(false);
  // The active value is read by pointer-move handlers attached for the lifetime
  // of a drag — ref it so a stale closure doesn't fire onChange for every move.
  const valueRef = React.useRef(value);
  valueRef.current = value;

  // Segments wrap mid-word once per-segment width runs out. The track is
  // ~248px (280 panel − 28 body pad − 4 seg pad), each button loses 12px
  // to its own padding, and 11.5px system-ui averages ~6.3px/char — so 2
  // options fit ~16 chars each, 3 fit ~10. Past that (or >3 options), fall
  // back to a dropdown rather than wrap.
  const labelLen = o => String(typeof o === 'object' ? o.label : o).length;
  const maxLen = options.reduce((m, o) => Math.max(m, labelLen(o)), 0);
  const fitsAsSegments = maxLen <= ({
    2: 16,
    3: 10
  }[options.length] ?? 0);
  if (!fitsAsSegments) {
    // <select> emits strings — map back to the original option value so the
    // fallback stays type-preserving (numbers, booleans) like the segment path.
    const resolve = s => {
      const m = options.find(o => String(typeof o === 'object' ? o.value : o) === s);
      return m === undefined ? s : typeof m === 'object' ? m.value : m;
    };
    return /*#__PURE__*/React.createElement(TweakSelect, {
      label: label,
      value: value,
      options: options,
      onChange: s => onChange(resolve(s))
    });
  }
  const opts = options.map(o => typeof o === 'object' ? o : {
    value: o,
    label: o
  });
  const idx = Math.max(0, opts.findIndex(o => o.value === value));
  const n = opts.length;
  const segAt = clientX => {
    const r = trackRef.current.getBoundingClientRect();
    const inner = r.width - 4;
    const i = Math.floor((clientX - r.left - 2) / inner * n);
    return opts[Math.max(0, Math.min(n - 1, i))].value;
  };
  const onPointerDown = e => {
    setDragging(true);
    const v0 = segAt(e.clientX);
    if (v0 !== valueRef.current) onChange(v0);
    const move = ev => {
      if (!trackRef.current) return;
      const v = segAt(ev.clientX);
      if (v !== valueRef.current) onChange(v);
    };
    const up = () => {
      setDragging(false);
      window.removeEventListener('pointermove', move);
      window.removeEventListener('pointerup', up);
    };
    window.addEventListener('pointermove', move);
    window.addEventListener('pointerup', up);
  };
  return /*#__PURE__*/React.createElement(TweakRow, {
    label: label
  }, /*#__PURE__*/React.createElement("div", {
    ref: trackRef,
    role: "radiogroup",
    onPointerDown: onPointerDown,
    className: dragging ? 'twk-seg dragging' : 'twk-seg'
  }, /*#__PURE__*/React.createElement("div", {
    className: "twk-seg-thumb",
    style: {
      left: `calc(2px + ${idx} * (100% - 4px) / ${n})`,
      width: `calc((100% - 4px) / ${n})`
    }
  }), opts.map(o => /*#__PURE__*/React.createElement("button", {
    key: o.value,
    type: "button",
    role: "radio",
    "aria-checked": o.value === value
  }, o.label))));
}
function TweakSelect({
  label,
  value,
  options,
  onChange
}) {
  return /*#__PURE__*/React.createElement(TweakRow, {
    label: label
  }, /*#__PURE__*/React.createElement("select", {
    className: "twk-field",
    value: value,
    onChange: e => onChange(e.target.value)
  }, options.map(o => {
    const v = typeof o === 'object' ? o.value : o;
    const l = typeof o === 'object' ? o.label : o;
    return /*#__PURE__*/React.createElement("option", {
      key: v,
      value: v
    }, l);
  })));
}
function TweakText({
  label,
  value,
  placeholder,
  onChange
}) {
  return /*#__PURE__*/React.createElement(TweakRow, {
    label: label
  }, /*#__PURE__*/React.createElement("input", {
    className: "twk-field",
    type: "text",
    value: value,
    placeholder: placeholder,
    onChange: e => onChange(e.target.value)
  }));
}
function TweakNumber({
  label,
  value,
  min,
  max,
  step = 1,
  unit = '',
  onChange
}) {
  const clamp = n => {
    if (min != null && n < min) return min;
    if (max != null && n > max) return max;
    return n;
  };
  const startRef = React.useRef({
    x: 0,
    val: 0
  });
  const onScrubStart = e => {
    e.preventDefault();
    startRef.current = {
      x: e.clientX,
      val: value
    };
    const decimals = (String(step).split('.')[1] || '').length;
    const move = ev => {
      const dx = ev.clientX - startRef.current.x;
      const raw = startRef.current.val + dx * step;
      const snapped = Math.round(raw / step) * step;
      onChange(clamp(Number(snapped.toFixed(decimals))));
    };
    const up = () => {
      window.removeEventListener('pointermove', move);
      window.removeEventListener('pointerup', up);
    };
    window.addEventListener('pointermove', move);
    window.addEventListener('pointerup', up);
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "twk-num"
  }, /*#__PURE__*/React.createElement("span", {
    className: "twk-num-lbl",
    onPointerDown: onScrubStart
  }, label), /*#__PURE__*/React.createElement("input", {
    type: "number",
    value: value,
    min: min,
    max: max,
    step: step,
    onChange: e => onChange(clamp(Number(e.target.value)))
  }), unit && /*#__PURE__*/React.createElement("span", {
    className: "twk-num-unit"
  }, unit));
}

// Relative-luminance contrast pick — checkmarks drawn over a swatch need to
// read on both #111 and #fafafa without per-option configuration. Hex input
// only (#rgb / #rrggbb); named or rgb()/hsl() colors fall through to "light".
function __twkIsLight(hex) {
  const h = String(hex).replace('#', '');
  const x = h.length === 3 ? h.replace(/./g, c => c + c) : h.padEnd(6, '0');
  const n = parseInt(x.slice(0, 6), 16);
  if (Number.isNaN(n)) return true;
  const r = n >> 16 & 255,
    g = n >> 8 & 255,
    b = n & 255;
  return r * 299 + g * 587 + b * 114 > 148000;
}
const __TwkCheck = ({
  light
}) => /*#__PURE__*/React.createElement("svg", {
  viewBox: "0 0 14 14",
  "aria-hidden": "true"
}, /*#__PURE__*/React.createElement("path", {
  d: "M3 7.2 5.8 10 11 4.2",
  fill: "none",
  strokeWidth: "2.2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  stroke: light ? 'rgba(0,0,0,.78)' : '#fff'
}));

// TweakColor — curated color/palette picker. Each option is either a single
// hex string or an array of 1-5 hex strings; the card adapts — a lone color
// renders solid, a palette renders colors[0] as the hero (left ~2/3) with the
// rest stacked in a sharp column on the right. onChange emits the
// option in the shape it was passed (string stays string, array stays array).
// Without options it falls back to the native color input for back-compat.
function TweakColor({
  label,
  value,
  options,
  onChange
}) {
  if (!options || !options.length) {
    return /*#__PURE__*/React.createElement("div", {
      className: "twk-row twk-row-h"
    }, /*#__PURE__*/React.createElement("div", {
      className: "twk-lbl"
    }, /*#__PURE__*/React.createElement("span", null, label)), /*#__PURE__*/React.createElement("input", {
      type: "color",
      className: "twk-swatch",
      value: value,
      onChange: e => onChange(e.target.value)
    }));
  }
  // Native <input type=color> emits lowercase hex per the HTML spec, so
  // compare case-insensitively. String() guards JSON.stringify(undefined),
  // which returns the primitive undefined (no .toLowerCase).
  const key = o => String(JSON.stringify(o)).toLowerCase();
  const cur = key(value);
  return /*#__PURE__*/React.createElement(TweakRow, {
    label: label
  }, /*#__PURE__*/React.createElement("div", {
    className: "twk-chips",
    role: "radiogroup"
  }, options.map((o, i) => {
    const colors = Array.isArray(o) ? o : [o];
    const [hero, ...rest] = colors;
    const sup = rest.slice(0, 4);
    const on = key(o) === cur;
    return /*#__PURE__*/React.createElement("button", {
      key: i,
      type: "button",
      className: "twk-chip",
      role: "radio",
      "aria-checked": on,
      "data-on": on ? '1' : '0',
      "aria-label": colors.join(', '),
      title: colors.join(' · '),
      style: {
        background: hero
      },
      onClick: () => onChange(o)
    }, sup.length > 0 && /*#__PURE__*/React.createElement("span", null, sup.map((c, j) => /*#__PURE__*/React.createElement("i", {
      key: j,
      style: {
        background: c
      }
    }))), on && /*#__PURE__*/React.createElement(__TwkCheck, {
      light: __twkIsLight(hero)
    }));
  })));
}
function TweakButton({
  label,
  onClick,
  secondary = false
}) {
  return /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: secondary ? 'twk-btn secondary' : 'twk-btn',
    onClick: onClick
  }, label);
}
Object.assign(window, {
  useTweaks,
  TweaksPanel,
  TweakSection,
  TweakRow,
  TweakSlider,
  TweakToggle,
  TweakRadio,
  TweakSelect,
  TweakText,
  TweakNumber,
  TweakColor,
  TweakButton
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/tweaks-panel.jsx", error: String((e && e.message) || e) }); }

__ds_ns.ZxBadge = __ds_scope.ZxBadge;

__ds_ns.ZxButton = __ds_scope.ZxButton;

__ds_ns.ZxMedal = __ds_scope.ZxMedal;

__ds_ns.ZxStars = __ds_scope.ZxStars;

})();
