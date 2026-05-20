/* Data for a single release page — release #5 of "Crystal Kingdom Dizzy":
   the 1995 Russian translation by Scorpion Soft. Picked because it has
   non-trivial publishers, hardware, and a translation status. */

const RELEASE = {
  id: 547,                     // shown as #R-00547
  catalog: "R-00547",
  title: "Dizzy 7: Crystal Kingdom",
  prod: {
    id: 12871,
    title: "Crystal Kingdom Dizzy",
    alsoKnownAs: "Dizzy 7",
    year: 1992,
    authors: ["Dave Thompson", "Jarrod Bentley"],
    cover: "default",          // ZxScreen palette
  },
  year: 1995,
  type: "translation",         // original | adaptation | translation | modification | crack | unknown
  lang: "ru",
  status: { code: "unknown", label: "Статус распространения не определён", tone: "warn" },
  publishers: [
    { id: 211, name: "Scorpion Soft", role: "Издатель" },
    { id: 412, name: "BridgeSoft",    role: "Перевод" },
  ],
  hardware: [
    { id: "zx128",    name: "ZX Spectrum 128K" },
    { id: "ay",       name: "AY-3-8910 / YM2149F" },
    { id: "kempston", name: "Кемпстон джойстик" },
    { id: "trd",      name: "TR-DOS" },
  ],
  format: "SCL диск",
  size: "42 КБ",
  downloads: 27,
  plays: 3,
  addedAt: "12.03.2017",
  addedBy: "Roman Tadzhiev",
  votes: { score: 4.6, count: 5 },
  description:
    "Русскоязычная адаптация финальной части Йолкфолк-цикла. Перевод текстов меню, диалогов с Game Genie и подсказок выполнен Scorpion Soft в сотрудничестве с BridgeSoft. Графика и музыка не изменены относительно оригинала Code Masters; раскладка управления адаптирована под джойстик Кемпстон.",
};

/* Cassette / disk covers attached to this release. Each is a fake 3:4 image. */
const COVERS = [
  { id: 1, kind: "front", label: "Обложка кассеты (лицевая)", palette: "sunset", file: "ckd-scorpion-front.jpg", size: "0.4 МБ" },
  { id: 2, kind: "back",  label: "Обложка кассеты (обратная)", palette: "cool",  file: "ckd-scorpion-back.jpg",  size: "0.3 МБ" },
  { id: 3, kind: "label", label: "Наклейка SCL-диска",         palette: "forest", file: "ckd-scorpion-label.png", size: "0.1 МБ" },
];

/* Screenshots specific to this release (3 of them — Russian text visible). */
const REL_SCREENS = [
  { id: 11, palette: "forest", file: "screen-01.scr", size: "6.9 КБ" },
  { id: 12, palette: "sunset", file: "screen-02.scr", size: "6.9 КБ" },
  { id: 13, palette: "cool",   file: "screen-03.scr", size: "6.9 КБ" },
  { id: 14, palette: "night",  file: "screen-04.scr", size: "6.9 КБ" },
  { id: 15, palette: "default",file: "screen-05.scr", size: "6.9 КБ" },
];

/* Inline instructions (downloadable). */
const INSTRUCTIONS = [
  { lang: "ru", title: "Инструкция и прохождение",  file: "CKD-Scorpion.RU.txt", size: "12 КБ" },
  { lang: "en", title: "Walkthrough (original)",     file: "CKD-Walkthrough.EN.pdf", size: "0.8 МБ" },
];

/* Comments on this specific release. */
const REL_COMMENTS = [
  { id: 1, user: "Roman Tadzhiev", date: "13.03.2017", body: "Лучший русский перевод. Шрифт почти не отличается от оригинального, в отличие от ZSV." },
  { id: 2, user: "Дима К.",        date: "02.07.2019", body: "На клонах с TR-DOS пробуксовывает на 3-м акте. На 128K в эмуляторе всё ок." },
  { id: 3, user: "BridgeSoft",     date: "18.09.2020", body: "У нас сохранилась наклейка диска в HQ — могу прислать, если кто соберёт переиздание." },
];

/* Per-release vote history. */
const REL_VOTES = [
  { user: "Roman Tadzhiev", date: "13.03.2017", score: 5 },
  { user: "Дима К.",        date: "02.07.2019", score: 4 },
  { user: "AY-3",           date: "11.11.2019", score: 5 },
  { user: "scolopendrum",   date: "04.02.2021", score: 4 },
  { user: "BridgeSoft",     date: "18.09.2020", score: 5 },
];

const REL_TYPES = {
  original:     { label: "Оригинал",   tone: "primary" },
  adaptation:   { label: "Адаптация",  tone: "primary-deep" },
  translation:  { label: "Перевод",    tone: "warn" },
  modification: { label: "Модификация",tone: "warn-soft" },
  crack:        { label: "Взлом",      tone: "danger" },
  unknown:      { label: "Неизвестный",tone: "neutral" },
};

/* File tree inside the release archive. Always present.
   d = depth, kind = file|folder, viewable = text/image that has a viewer. */
const FILE_TREE = [
  { d: 0, name: "ckd-scorpion-1995.zip", size: 42068,  kind: "zip"    },
  { d: 1, name: "CKD-Scorpion-1995",     size: 5,      kind: "folder" },
  { d: 2, name: "DISK.scl",              size: 40960,  kind: "file",   ext: "SCL образ" },
  { d: 2, name: "readme_ru.txt",         size: 1247,   kind: "file",   ext: "Текст", viewable: true },
  { d: 2, name: "readme_en.txt",         size: 892,    kind: "file",   ext: "Текст", viewable: true },
  { d: 2, name: "cover",                 size: 2,      kind: "folder" },
  { d: 3, name: "front.png",             size: 412531, kind: "file",   ext: "Изображение", viewable: true },
  { d: 3, name: "back.png",              size: 387204, kind: "file",   ext: "Изображение", viewable: true },
];

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
  RELEASE, COVERS, REL_SCREENS, INSTRUCTIONS, REL_COMMENTS, REL_VOTES, REL_TYPES,
  FILE_TREE, README_RU,
});
