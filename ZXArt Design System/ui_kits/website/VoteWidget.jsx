function VoteWidget({ myVote = 0, fav = false }) {
  return (
    <span className="vote-widget" aria-label="Голосовать">
      <button className="vote-widget__clear" type="button" aria-label="Снять голос" disabled={!myVote}>✕</button>
      {[1,2,3,4,5].map(s => (
        <button key={s} type="button"
          className={"vote-widget__star" + (s <= myVote ? " vote-widget__star--filled" : "")}
          aria-label={`${s} звёзд`}>★</button>
      ))}
      {myVote ? <span className="vote-widget__my">{myVote}</span> : null}
      <button type="button" className={"vote-widget__heart" + (fav ? " vote-widget__heart--on" : "")} aria-label={fav ? "В избранном" : "В избранное"}>♥</button>
    </span>
  );
}
window.VoteWidget = VoteWidget;
