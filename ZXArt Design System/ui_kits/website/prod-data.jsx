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
  tags: ["Chalice","Crystal Kingdom","Grand Dizzy","Storm","Yolkfolk","Деревня","Диззи","Дождь","Корона","Меч","Храм","Яйцо"],
  links: [
    { label: "Speccy Screenshot Maps", host: "speccy.pl" },
    { label: "Spectrum Computing",     host: "spectrumcomputing.co.uk" },
    { label: "World Of Spectrum",      host: "worldofspectrum.org" },
    { label: "Virtual TR-DOS",         host: "vtrd.in" },
  ],
  rating: { score: 4.14, ofFive: 5, votes: 73 },
  added: "18.11.2016",
  story: "«Crystal Kingdom Dizzy» — приключенческая игра-головоломка 1992 года, часть серии о Диззи. Диззи отправляется на поиски украденных сокровищ Йолкфолка: кристального меча, чаши и короны. Без них в Кристальном Королевстве происходят странные события. По пути герой решает головоломки, собирает предметы и встречает таких персонажей, как Game Genie, который выдаёт коды для перемещения между актами. Игра известна яркой графикой и насыщенным сюжетом; графику создал Джаррод Бентли, музыку — Reflective Designs.",
  series: { name: "Dizzy", count: 9 },
};

/** All 18 releases. */
const RELEASES = [
  { id: 1,  title: "Crystal Kingdom Dizzy", year: 1992, lang: "en", playOnline: true, type: "original",   releasedBy: "Code Masters Ltd",       hardware: ["ZX Spectrum 48K"],         format: "TZX лента",  downloads: 30, plays: 3, files: ["CrystalKingdomDizzy.txt","CrystalKingdomDizzy(EN).pdf"], votes: 12, screens: ["a","b","c","d","e"] },
  { id: 2,  title: "Crystal Kingdom Dizzy", year: 1992, lang: "en", playOnline: false, type: "unknown",   releasedBy: "Code Masters Ltd",       hardware: ["Интерфейс2 джойстик","Кемпстон джойстик"], format: null,         downloads: 0,  plays: 0, files: ["CrystalKingdomDizzy.txt"], votes: 0,   screens: [] },
  { id: 3,  title: "Dizzy 7: Crystal Kingdom", year: 1994, lang: "en", playOnline: true, type: "adaptation", releasedBy: "Studio 7",             hardware: [],                          format: "SCL диск",   downloads: 20, plays: 2, files: [], votes: 4, screens: ["a","b"] },
  { id: 4,  title: "Dizzy 7: Crystal Kingdom", year: 1994, lang: "en", playOnline: true, type: "adaptation", releasedBy: "ZSV",                  hardware: [],                          format: "SCL диск",   downloads: 20, plays: 1, files: [], votes: 2, screens: ["a"] },
  { id: 5,  title: "Dizzy 7: Crystal Kingdom", year: 1995, lang: "ru", playOnline: true, type: "translation", releasedBy: "Scorpion Soft",       hardware: [],                          format: "SCL диск",   downloads: 27, plays: 3, files: [], votes: 5, screens: ["a","b","c"] },
  { id: 6,  title: "Dizzy 7: Crystal Kingdom", year: 1995, lang: "ru", playOnline: true, type: "translation", releasedBy: "Softstar, FFC Computers", hardware: [],                       format: "SCL диск",   downloads: 20, plays: 1, files: [], votes: 1, screens: ["a"] },
  { id: 7,  title: "Dizzy 7: Crystal Kingdom", year: 1995, lang: "ru", playOnline: true, type: "translation", releasedBy: "Madness Coders Group", hardware: [],                          format: "SCL диск",   downloads: 18, plays: 1, files: [], votes: 1, screens: ["a"] },
  { id: 8,  title: "Dizzy 7: Crystal Kingdom", year: 1995, lang: "en", playOnline: true, type: "adaptation", releasedBy: "Damage Inc",          hardware: [],                          format: "SCL диск",   downloads: 20, plays: 1, files: [], votes: 1, screens: ["a"] },
  { id: 9,  title: "Dizzy 7: Crystal Kingdom", year: 1995, lang: "en", playOnline: true, type: "adaptation", releasedBy: "Studio Scorpion Group", hardware: [],                         format: "SCL диск",   downloads: 20, plays: 1, files: [], votes: 1, screens: ["a"] },
  { id: 10, title: "Dizzy 7: Crystal Kingdom", year: 1995, lang: "en", playOnline: true, type: "adaptation", releasedBy: "Softstar",            hardware: [],                          format: "SCL диск",   downloads: 20, plays: 1, files: [], votes: 1, screens: ["a"] },
  { id: 11, title: "Dizzy 7: Crystal Kingdom", year: 1995, lang: "en", playOnline: true, type: "adaptation", releasedBy: "Dr. Bars",            hardware: [],                          format: "SCL диск",   downloads: 21, plays: 1, files: [], votes: 1, screens: ["a"] },
  { id: 12, title: "Dizzy 7: Crystal Kingdom", year: 1995, lang: "en", playOnline: true, type: "adaptation", releasedBy: "Владислав Кропачев",  hardware: [],                          format: "SCL диск",   downloads: 20, plays: 1, files: [], votes: 1, screens: ["a"] },
  { id: 13, title: "Crystal Kingdom Dizzy",   year: 2009, lang: "en", playOnline: true, type: "modification", note: "new gfx", releasedBy: "Jarrod Bentley", hardware: [],              format: "TZX лента",  downloads: 22, plays: 2, files: [], votes: 6, screens: ["a","b","c"] },
  { id: 14, title: "Dizzy 7: Crystal Kingdom", year: null, lang: "ru", playOnline: true, type: "translation", releasedBy: "Prospekt",           hardware: [],                          format: "SCL диск",   downloads: 18, plays: 1, files: [], votes: 1, screens: ["a"] },
  { id: 15, title: "Dizzy 7: Crystal Kingdom", year: null, lang: "en", playOnline: true, type: "adaptation",  releasedBy: null,                 hardware: ["AY-3-8910/12, YM2149F"],   format: "SCL диск",   downloads: 19, plays: 1, files: [], votes: 1, screens: ["a"], note: "+ Alternative AY Music" },
  { id: 16, title: "Crystal Kingdom Dizzy",   year: null, lang: "ru", playOnline: true, type: "crack",        releasedBy: "Prospekt",           hardware: [],                          format: "TAP лента",  downloads: 13, plays: 0, files: [], votes: 0, screens: [] },
  { id: 17, title: "Crystal Kingdom Dizzy",   year: null, lang: "en", playOnline: true, type: "crack",        releasedBy: null,                 hardware: [],                          format: "TAP лента",  downloads: 9,  plays: 2, files: [], votes: 0, screens: [] },
];

const RELEASE_TYPES = {
  original:     { label: "Оригинальный", color: "var(--primary-500)" },
  adaptation:   { label: "Адаптация",     color: "var(--primary-700)" },
  translation:  { label: "Перевод",       color: "var(--warning-700)" },
  modification: { label: "Модификация",   color: "var(--warning-500)" },
  crack:        { label: "Взломанный",    color: "var(--danger-500)" },
  unknown:      { label: "Неизвестный",   color: "var(--secondary-500)" },
};

/** 46 procedural screen seeds — stand in for the real .gif/.scr */
const SCREENS = Array.from({ length: 46 }, (_, i) => ({
  id: 100 + i,
  palette: ["sunset","cool","forest","night","default"][i % 5],
}));

/** Music tracks */
const PROD_TUNES = [
  { id: 1, idx: 1, title: "128K Title 1",   author: "David Whittaker", chip: "AY", year: 1992, plays: 921, stars: 3, duration: "0:46" },
  { id: 2, idx: 2, title: "128K In-Game 2", author: "David Whittaker", chip: "AY", year: 1992, plays: 221, stars: 5, duration: "2:11" },
  { id: 3, idx: 3, title: "128K Game Over 3", author: "David Whittaker", chip: "AY", year: 1992, plays: 8,  stars: 3, duration: "0:18" },
  { id: 4, idx: 4, title: "128K Jingle 4",  author: "David Whittaker", chip: "AY", year: 1992, plays: 29, stars: 3, duration: "0:11" },
];

/** Mentions in articles */
const MENTIONS = [
  { mag: "Spectrofon", issue: 13, year: 1995, section: "Обзор",    body: "Обзор симуляторов и спортивных игр для ZX Spectrum, включая ‘F-19 Stealth Fighter’, ‘Carrier Command’, ‘Dizzy 7’, ‘Hudson Hawk’ и ‘Magic Johnson’s Basketball’." },
  { mag: "ZX Format",  issue: 1,  year: 1995, section: "Игрушки",  body: "Подробное прохождение игры Crystal Kingdom Dizzy, включая решения головоломок и советы по продвижению в сюжете." },
];

const COMPILATIONS = [
  { format: "TR-DOS", title: "Dizzy Collection",                            by: "Flash Inc",  count: 48, year: 1995 },
  { format: "AY/YM",  title: "Dizzy Compilation",                           by: null,         count: null, year: null },
  { format: null,     title: "Dizzy Super Adventure Codemasters' Kollektion", by: null,       count: 5, year: 2010 },
  { format: null,     title: "Dizzy 1,2,3,3.5,4,5,6,7 + help",              by: "The Legacy", count: 5, year: null },
];

const VOTES = [
  { user: "Jarrod Bentley", year: 1992, target: "Crystal Kingdom Dizzy",              score: 5 },
  { user: "Softstar",       year: 1992, target: "Crystal Kingdom Dizzy",              score: 3 },
  { user: "Роман Таджиев",  year: 1995, target: "Crystal Kingdom Dizzy",              score: null },
  { user: "Jarrod Bentley", year: 2009, target: "Crystal Kingdom Dizzy 2009 version", score: 5 },
];

const MAPS = [{ author: "Tommy Pereira" }];

const SAME_SERIES = [
  { title: "Dizzy",                             by: "The Oliver Twins, Code Masters Ltd", year: 1987, score: 5, hardware: ["48","Кемп.","AY/YM"] },
  { title: "Treasure Island Dizzy",             by: "The Oliver Twins, Code Masters Ltd", year: 1988, score: 5, hardware: ["Кемп.","48","128+2","128+3","AY/YM","DMA"] },
  { title: "Fantasy World Dizzy",               by: "The Oliver Twins, Code Masters Ltd", year: 1989, score: 5, hardware: ["Кемп.","48","128+2","128+3"] },
  { title: "Dizzy 3 and a Half",                by: "Code Masters Ltd",                   year: 1991, score: 5, hardware: ["AY/YM","Кемп.","48"] },
  { title: "Magicland Dizzy",                   by: "Big Red Software Ltd",               year: 1990, score: 5, hardware: ["Кемп.","48"] },
  { title: "Spellbound Dizzy",                  by: "Big Red Software Ltd",               year: 1991, score: 5, hardware: ["128","48","AY/YM","Кемп."] },
  { title: "Dizzy, Prince of the YolkFolk",     by: "Big Red Software Ltd",               year: 1991, score: 5, hardware: ["GS","128","48","AY/YM","Кемп."] },
  { title: "Wonderful Dizzy",                   by: "The Oliver Twins",                   year: 2020, score: null, hardware: ["128","TR-DOS","AY/YM","ULA+","+3DOS","128+3"] },
];

Object.assign(window, { PROD, RELEASES, RELEASE_TYPES, SCREENS, PROD_TUNES, MENTIONS, COMPILATIONS, VOTES, MAPS, SAME_SERIES });
