/** Generic placeholder — for routes that the kit hasn't built out yet. */
const PlaceholderScreen = ({ name }) => (
  <>
    <div className="crumbs">
      <a href="#">Home</a><span className="sep">›</span> <span style={{ textTransform: "capitalize" }}>{name}</span>
    </div>
    <div className="empty-screen">"{name}" — this section isn't part of the UI kit yet.</div>
  </>
);

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

  const handleNavigate = (id) => {
    setOpenPicture(null);
    setRoute(id);
    window.scrollTo({ top: 0 });
  };

  const handleOpenPicture = (p) => {
    setOpenPicture(p);
    window.scrollTo({ top: 0 });
  };

  const handlePlayTune = (tune) => {
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
    screen = <PictureDetail picture={openPicture} onBack={() => setOpenPicture(null)} />;
  } else if (route === "home") {
    screen = <HomeScreen onPlayTune={handlePlayTune} currentTuneId={currentTune?.id} isPlaying={isPlaying} onOpenPicture={handleOpenPicture} onNavigate={handleNavigate} />;
  } else if (route === "pictures") {
    screen = <PicturesScreen onOpenPicture={handleOpenPicture} />;
  } else if (route === "music") {
    screen = <MusicScreen onPlayTune={handlePlayTune} currentTuneId={currentTune?.id} isPlaying={isPlaying} />;
  } else {
    screen = <PlaceholderScreen name={route} />;
  }

  return (
    <div className="app-shell">
      <Header active={openPicture ? "pictures" : route} onNavigate={handleNavigate} theme={theme}
              onToggleTheme={() => setTheme(t => t === "light" ? "dark" : "light")} />
      <main className="main">
        {screen}
      </main>
      {currentTune && <div className="player-spacer"></div>}
      {currentTune && (
        <div className="player-fixed">
          <Player tune={currentTune} isPlaying={isPlaying}
                  onTogglePlay={() => setIsPlaying(p => !p)}
                  onNext={handleNext} onPrev={handlePrev} />
        </div>
      )}
    </div>
  );
};

window.App = App;

ReactDOM.createRoot(document.getElementById("root")).render(<App />);
