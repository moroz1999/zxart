/** Tune row + sticky bottom player. Mirrors ng-zxart/src/app/features/player/ */
const TuneRow = ({ tune, isPlaying, isCurrent, onPlay }) => {
  return (
    <div className="zx-tune-row" style={isCurrent ? { background: "var(--secondary-100)" } : null}>
      <button className="zx-tune-row__play" aria-label={isPlaying ? "Pause" : "Play"} onClick={() => onPlay(tune)}>
        <Icon name={isPlaying ? "pause" : "play"} size={14} />
      </button>
      <span className="zx-tune-row__title">{tune.title}</span>
      <span className="zx-tune-row__author">{tune.author}</span>
      <span className="zx-tune-row__chip">{tune.chip} · {tune.duration}</span>
      <ZxButton size="xs" variant="transparent" shape="square" ariaLabel="Add to favourites">
        <Icon name="heartO" size={14} />
      </ZxButton>
      <ZxButton size="xs" variant="transparent" shape="square" ariaLabel="Download">
        <Icon name="download" size={14} />
      </ZxButton>
    </div>
  );
};

const Player = ({ tune, isPlaying, onTogglePlay, onNext, onPrev, progress = 0.42 }) => {
  if (!tune) return null;
  const totalSec = parseDuration(tune.duration);
  const curSec = Math.floor(totalSec * progress);
  return (
    <div className="zx-player">
      <div className="zx-player__controls">
        <ZxButton size="sm" variant="transparent" shape="round" ariaLabel="Previous" onClick={onPrev}>
          <Icon name="prev" size={16} />
        </ZxButton>
        <ZxButton size="md" variant="primary" shape="round" ariaLabel={isPlaying ? "Pause" : "Play"} onClick={onTogglePlay}>
          <Icon name={isPlaying ? "pause" : "play"} size={18} />
        </ZxButton>
        <ZxButton size="sm" variant="transparent" shape="round" ariaLabel="Next" onClick={onNext}>
          <Icon name="next" size={16} />
        </ZxButton>
      </div>
      <div className="zx-player__progress">
        <div className="zx-player__fill" style={{ width: `${progress * 100}%` }}></div>
        <span className="zx-player__title">"{tune.title}" — {tune.author}</span>
        <span className="zx-player__time">{formatTime(curSec)} / {tune.duration}</span>
      </div>
      <ZxButton size="sm" variant="transparent" shape="round" ariaLabel="Shuffle">
        <Icon name="shuffle" size={16} />
      </ZxButton>
      <ZxButton size="sm" variant="transparent" shape="round" ariaLabel="Repeat">
        <Icon name="repeat" size={16} />
      </ZxButton>
    </div>
  );
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

Object.assign(window, { TuneRow, Player });
