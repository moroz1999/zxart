/* author-data.jsx — mock data for AuthorPage.
   Two presets: "moroz" (chudovischny all-rounder, mostly graphics) and
   "newbie" (two works, no rating yet). */

const AUTHOR_PRESETS = {
  moroz: {
    handle: "moroz1999",
    realName: "Дмитрий",
    location: ["Таллин", "Эстония"],
    roles: ["artist", "musician", "coder"], // for the role chips at top
    groups: [
      { name: "dibiliki", parent: null, years: "1996–наст." },
      { name: "FREEgroup",  parent: null, years: "1998–2004" },
      { name: "RAZOR 1911", parent: null, years: "2000–2003" },
      { name: "ZX-Spec sub", parent: "FREEgroup", years: "1999–2001" },
    ],
    aliases: [
      "Moroz", "Dead Moroz", "Jose Luis Pendejo", "mr. bungle", "Мальчик Дима",
      "Отец хлеба", "Juan González Pendejo Ibáñez", "Автор Работы",
      "Cosmic stranger of love", "kolbasa-style", "Vitalino", "PharaO",
      "Абсолютный Мастер Всея Некопии", "Владис", "Vivan", "D.Ivan",
      "Vovka", "Nurgo", "Antonius", "Borgee", "8bit fan", "TruarT",
      "Longshooter", "Автор всех работ", "сеньор кшишлав не покупает нашу рыбу",
      "Well-known Gribofairy"
    ],
    links: [
      { site: "zxaaa.net",        label: "Страница на zxaaa.net",          icon: "Z" },
      { site: "spectrumcomputing.co.uk", label: "Страница на Spectrum Computing", icon: "S" },
      { site: "speccywiki.org",   label: "Страница на SpeccyWiki",         icon: "W" },
    ],
    tech: {
      palette: "sRGB",
      ayChip: "AY-3-8910 / YM2149F",
      ayChannels: "ACB",
      ayClock: "1.75 МГц (Пентагон)",
      intFreq: "48.828125 Гц (Пентагон)",
    },
    ratings: { artist: 457.45, musician: 28.12 },
    counters: { pictures: 187, tunes: 42, prods: 11, comments: 318 },
    badges: ["VIP-спонсор", "Волонтёр"],
    avatar: "pixel", // pixelized photo
    siteUser: "moroz1999",
    joined: "2008-03-14",
  },
  newbie: {
    handle: "bytekid",
    realName: "—",
    location: null,
    roles: ["musician"],
    groups: [],
    aliases: ["bk", "byte-kid"],
    links: [
      { site: "speccywiki.org", label: "Страница на SpeccyWiki", icon: "W" },
    ],
    tech: {
      palette: "sRGB",
      ayChip: "AY-3-8910",
      ayChannels: "ABC",
      ayClock: "1.7734 МГц (Спектрум)",
      intFreq: "50.08 Гц (Спектрум)",
    },
    ratings: { artist: 0, musician: 0 },
    counters: { pictures: 0, tunes: 2, prods: 0, comments: 0 },
    badges: [],
    avatar: "none",
    siteUser: "bytekid",
    joined: "2026-04-11",
  },
};

/* ── deterministic generators (so pages stay stable across reloads) ── */
function _rnd(seed) {
  let s = seed * 9301 + 49297;
  return () => { s = (s * 9301 + 49297) % 233280; return s / 233280; };
}
const PARTIES = [
  "Chaos Constructions", "DiHalt", "Forever", "Multimatograf", "CAFePARTY",
  "Outline", "ArtField", "ZX-Spectrum Demo", "Yandex.Demoscene", null, null,
];
const PALETTES = ["sunset", "cool", "forest", "night", "default"];
const PIC_FORMATS = ["original", "gigascreen", "multicolor", "sam coupe", "atm2"];
const PIC_TITLES = [
  "Eternal Flame", "Ten Years After", "Cyber Night", "Pixel Tears",
  "Old Soldier", "Frozen Land", "Speccy Dreams", "Beyond The Walls",
  "Star Voyager", "Magic Cube", "Code of Honor", "Cyrillic Spring",
  "Mr. Boombastic", "Snow Queen", "Robocop", "Last Bastion",
  "Crystal Garden", "Iron Gate", "Lone Wolf", "Heat Wave",
  "Twilight", "Sunset Boulevard", "Bittersweet Symphony",
  "Vector Field", "Polygon Soul", "Bitmap Heart", "Pixel Storm",
  "Echo", "Resonance", "Stochastic", "Glitch in the Matrix",
  "Static Noise", "Static Beauty", "Stained Glass", "Untitled",
  "Untitled II", "Untitled III", "Untitled IV", "Untitled V",
];
const TUNE_CHIPS = ["AY", "AY", "AY", "Beeper", "Turbosound"];
const PROD_KINDS = ["Игра", "Демо", "Интро", "Утилита", "Музыкальный диск"];
/* Sub-categories: each top-level kind may have a 2nd-level tag.
   Filter UI shows a tree of top-level and sub-categories. */
const PROD_SUBCATS = {
  "Игра":  ["Стрелялка", "Командер", "Квест", "Аркада", "Платформер"],
  "Демо":  ["Megademo", "Тех-демо"],
  "Интро": ["64K", "256B", "Cracktro"],
};
const PROD_TITLES = [
  "Crystal Kingdom Dizzy", "Black Raven 2", "Mighty Final Fight",
  "Inferno", "Refresh 2", "Wolfenstein 3D Speccy", "Star Heritage",
  "Black Adder", "Tundra", "ARM-tan", "Dizzy Quest",
];
const ROLE_TYPES = {
  music:    { label: "Музыка",  icon: "music-note", color: "music"  },
  gfx:      { label: "Графика", icon: "image",      color: "gfx"    },
  code:     { label: "Код",     icon: "code",       color: "code"   },
  intro:    { label: "Интро к релизу", icon: "videogame-asset", color: "intro" },
  sfx:      { label: "Звук",    icon: "music-note", color: "music"  },
  design:   { label: "Гейм-дизайн", icon: "settings", color: "code" },
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
      plays: 200 + Math.floor(r() * 4800),     /* «запуски» = просмотры для картины */
      downloads: 30 + Math.floor(r() * 400),
      party: PARTIES[partyIx],
      place: PARTIES[partyIx] ? (r() > 0.5 ? Math.ceil(r() * 3) : null) : null,
      added: year + "-0" + (1 + Math.floor(r() * 9)) + "-1" + Math.floor(r() * 9),
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
      duration: (1 + Math.floor(r() * 4)) + ":" + String(Math.floor(r() * 60)).padStart(2, "0"),
      stars: 3 + Math.floor(r() * 3),
      votes: 5 + Math.floor(r() * 50),
      plays: 100 + Math.floor(r() * 2400),     /* «запуски» = прослушивания */
      downloads: 30 + Math.floor(r() * 350),
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
      roles,                  // global roles in the prod
      introRelease,           // if non-null, additionally intro author for this release
      stars: 3 + Math.floor(r() * 3),
      votes: 5 + Math.floor(r() * 80),
      downloads: 50 + Math.floor(r() * 4900),
      plays: 200 + Math.floor(r() * 6000),
      coAuthors: ["nq", "tiboh", "Skrju", "Diver/4D"].slice(0, 1 + Math.floor(r() * 3)),
    });
  }
  return out;
}

const COLLABORATORS = [
  { handle: "nq",        groups: "Skrju · RetroSouls", joint: { pictures: 14, tunes: 8, prods: 4 }, years: "1999–2018" },
  { handle: "tiboh",     groups: "debris · AAABand",   joint: { pictures: 7,  tunes: 12, prods: 2 }, years: "2005–2023" },
  { handle: "Diver/4D",  groups: "4D",                 joint: { pictures: 22, tunes: 0,  prods: 5 }, years: "1998–2012" },
  { handle: "Riskej",    groups: "RetroSouls",         joint: { pictures: 4,  tunes: 6,  prods: 3 }, years: "2014–2022" },
  { handle: "Sergey",    groups: "FREEgroup",          joint: { pictures: 11, tunes: 0,  prods: 1 }, years: "1998–2004" },
  { handle: "Karbofos",  groups: "Antares · Kabardin", joint: { pictures: 3,  tunes: 9,  prods: 2 }, years: "2008–2021" },
  { handle: "Lethargeek",groups: "Skrju",              joint: { pictures: 0,  tunes: 5,  prods: 4 }, years: "2011–2019" },
  { handle: "MmcM",      groups: "Sage",               joint: { pictures: 6,  tunes: 0,  prods: 1 }, years: "2007–2015" },
];

const COLLAB_GROUPS = [
  { name: "Skrju",      members: 11, ourWorks: 28, years: "1998–2024", releases: 41 },
  { name: "RetroSouls", members: 6,  ourWorks: 14, years: "2013–2024", releases: 22 },
  { name: "Outsiders",  members: 4,  ourWorks: 7,  years: "2002–2009", releases: 11 },
  { name: "Stardust",   members: 9,  ourWorks: 6,  years: "2005–2014", releases: 9  },
  { name: "Sibcrew",    members: 5,  ourWorks: 4,  years: "2010–2016", releases: 6  },
];

const RECENT_COMMENTS_RICH = [
  { id:1, by: "diver4d",  date: "2026-05-19", workType: "picture", workTitle: "Eternal Flame", body: "Это просто шедевр. Зачем ты так с нами?" },
  { id:2, by: "voxel",    date: "2026-05-18", workType: "tune",    workTitle: "Crystal Garden", body: "Чип точно AY? Мне кажется, под YM2149 звучит не так." },
  { id:3, by: "Riskej",   date: "2026-05-15", workType: "prod",    workTitle: "Crystal Kingdom Dizzy", role: "Музыка", body: "Лучшая музыка в Diz-серии. Подскажешь pt3?" },
  { id:4, by: "g0blin",   date: "2026-05-13", workType: "picture", workTitle: "Cyber Night",   body: "На моей CGA-карте всё хорошо отрисовывается, спасибо." },
  { id:5, by: "Lethargeek",date:"2026-05-09", workType: "picture", workTitle: "Snow Queen",    body: "Был мульт по мотивам? Очень в стиле." },
  { id:6, by: "anonimno", date: "2026-05-08", workType: "tune",    workTitle: "Frozen Land",   body: "→ комментарий удалён модератором" },
  { id:7, by: "tiboh",    date: "2026-05-04", workType: "picture", workTitle: "Mr. Boombastic", body: "Пиксели — лучшее, что было в твоей графике в 2013." },
  { id:8, by: "Karbofos", date: "2026-05-02", workType: "prod",    workTitle: "Inferno", role: "Графика + Код", body: "Перепрошёл первый раз. Финал жесть." },
];

const RECENT_VOTES_RICH = [
  { id:1, by: "diver4d",   date: "2026-05-19", workTitle: "Eternal Flame",       workType: "picture", score: 5 },
  { id:2, by: "Riskej",    date: "2026-05-19", workTitle: "Crystal Garden",      workType: "tune",    score: 5 },
  { id:3, by: "voxel",     date: "2026-05-18", workTitle: "Cyber Night",         workType: "picture", score: 4 },
  { id:4, by: "tiboh",     date: "2026-05-17", workTitle: "Mr. Boombastic",      workType: "picture", score: 5 },
  { id:5, by: "g0blin",    date: "2026-05-15", workTitle: "Inferno",             workType: "prod",    score: 5 },
  { id:6, by: "Karbofos",  date: "2026-05-13", workTitle: "Lone Wolf",           workType: "picture", score: 3 },
  { id:7, by: "Sergey",    date: "2026-05-11", workTitle: "Twilight",            workType: "picture", score: 4 },
  { id:8, by: "Lethargeek",date: "2026-05-08", workTitle: "Glitch in the Matrix",workType: "picture", score: 5 },
];

const AUTHOR_WALL_RICH = [
  { id: 1, by: "diver4d",   date: "2026-05-20", body: "Слушай, опять видел в SCENE Magazine упоминание твоего интро к Inferno. Поздравляю — заметили!" },
  { id: 2, by: "tiboh",     date: "2026-05-12", body: "Помнишь Forever 2009? Я наконец нашёл fly-by-кассету. Если интересно — могу прислать рип." },
  { id: 3, by: "g0blin",    date: "2026-04-30", body: "Спасибо за консультацию по AY-каналам в личке, всё разложилось. У меня теперь чище звучит." },
  { id: 4, by: "Karbofos",  date: "2026-04-22", body: "Брат, ты живой? Давно тебя не было на DiHalt. Ждём." },
  { id: 5, by: "newuser92", date: "2026-04-15", body: "Я тут только начал, посмотрел всю твою графику за вечер. Это ОЧЕНЬ круто. Спасибо." },
  { id: 6, by: "Riskej",    date: "2026-03-28", body: "За «Eternal Flame» ставлю отдельный респект. Думал, такого тайминга вообще нельзя добиться на 48k." },
];

const AUTHOR_WALL_NEWBIE = [];

/* Build the full data set per preset */
function buildAuthorData(presetKey) {
  const profile = AUTHOR_PRESETS[presetKey];
  return {
    profile,
    pictures: genPictures(profile.handle, profile.counters.pictures),
    tunes:    genTunes(profile.handle, profile.counters.tunes),
    prods:    genProds(profile.handle, profile.counters.prods),
    collaborators: presetKey === "moroz" ? COLLABORATORS : [],
    collabGroups:  presetKey === "moroz" ? COLLAB_GROUPS : [],
    comments:      presetKey === "moroz" ? RECENT_COMMENTS_RICH : [],
    votes:         presetKey === "moroz" ? RECENT_VOTES_RICH : [],
    wall:          presetKey === "moroz" ? AUTHOR_WALL_RICH : AUTHOR_WALL_NEWBIE,
  };
}

Object.assign(window, {
  AUTHOR_PRESETS, ROLE_TYPES, PALETTES, PROD_KINDS, PROD_SUBCATS, buildAuthorData,
});
