/** Sample data for the UI kit. Names are real ZX-scene fixtures used illustratively. */
const SAMPLE_PICTURES = [
  { id: 1, title: "Eternal Flame", authors: ["Diver/4D"], party: "Chaos Constructions 2003", place: 1, year: 2003, format: ".SCR", stars: 5, votes: 42, palette: "sunset" },
  { id: 2, title: "Black Sea Dawn", authors: ["Sergey Frolov"], party: "DiHalt 2014", place: 2, year: 2014, format: ".SCR", stars: 4, votes: 28, palette: "cool" },
  { id: 3, title: "Neon Avenue", authors: ["Andy/CFM"], party: "Forever 2008", place: 3, year: 2008, format: ".MC", stars: 4, votes: 17, palette: "night" },
  { id: 4, title: "Last Battle", authors: ["Riskej/4th Dimension"], party: "Chaos Constructions 1999", place: 1, year: 1999, format: ".SCR", flickering: true, stars: 5, votes: 64, palette: "sunset" },
  { id: 5, title: "Forest Whisper", authors: ["g0blinish"], year: 2019, format: ".SCR", stars: 3, votes: 9, palette: "forest" },
  { id: 6, title: "Spectrum Skyline", authors: ["Demiurge Ash", "Frog/CPU"], party: "Multimatograf 2017", place: 2, year: 2017, format: ".SCR", realtime: true, stars: 4, votes: 22, palette: "cool" },
  { id: 7, title: "Magnetic Fields", authors: ["Diver/4D"], year: 2005, format: ".SCR", stars: 5, votes: 31, palette: "night" },
  { id: 8, title: "Cosmonaut #7", authors: ["Hellboj"], party: "Chaos Constructions 2010", place: 1, year: 2010, format: ".SCR", stars: 4, votes: 18, palette: "default" },
];

const SAMPLE_TUNES = [
  { id: 1, title: "Robocop", author: "Tim Follin", chip: "AY", duration: "2:14" },
  { id: 2, title: "Last V8", author: "David Whittaker", chip: "Beeper", duration: "1:48" },
  { id: 3, title: "Aquaplane", author: "Rob Hubbard", chip: "AY", duration: "3:02" },
  { id: 4, title: "Cobra", author: "Jonathan Dunn", chip: "Beeper", duration: "2:21" },
  { id: 5, title: "Storm Lord", author: "Tim Follin", chip: "AY", duration: "3:48" },
  { id: 6, title: "Chronos", author: "Bjarne Christensen", chip: "AY", duration: "2:55" },
  { id: 7, title: "Game Over", author: "Marc Wilding", chip: "Beeper", duration: "1:32" },
  { id: 8, title: "Savage", author: "Adam Gilmore", chip: "AY", duration: "2:08" },
];

const SAMPLE_PRODS = [
  { id: 101, title: "Forever 96k", authors: ["DiHalt", "Outsiders"], kind: "demo", party: "Chaos Constructions 2018", place: 1, year: 2018, stars: 5, votes: 73, palette: "night" },
  { id: 102, title: "Castlevania ZX", authors: ["g0blinish"], kind: "game", year: 2021, stars: 4, votes: 19, palette: "sunset" },
  { id: 103, title: "AY-Tracker 0.91", authors: ["Sergey Bulba"], kind: "tool", year: 2007, stars: 5, votes: 41, palette: "cool" },
  { id: 104, title: "Eka 4k Intro", authors: ["Riskej"], kind: "intro", party: "Multimatograf 2016", place: 2, year: 2016, stars: 4, votes: 12, palette: "forest" },
];

Object.assign(window, { SAMPLE_PICTURES, SAMPLE_TUNES, SAMPLE_PRODS });
