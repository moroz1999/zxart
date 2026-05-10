/** Header — top nav (desktop). Mirrors ng-zxart header layout. */
const Header = ({ active, onNavigate, theme, onToggleTheme }) => {
  const items = [
    { id: "pictures", label: "Pictures" },
    { id: "music",    label: "Music" },
    { id: "prods",    label: "Prods" },
    { id: "authors",  label: "Authors" },
    { id: "groups",   label: "Groups" },
    { id: "parties",  label: "Parties" },
  ];
  return (
    <header className="zx-header">
      <div className="zx-header__inner">
        <a className="zx-header__logo" href="#" onClick={(e)=>{e.preventDefault(); onNavigate("home");}}>
          <img src="../../assets/logo.png" alt="zx-art" />
        </a>
        <nav className="zx-header__menu">
          {items.map(it => (
            <a key={it.id} href="#" className={active === it.id ? "active" : ""}
               onClick={(e)=>{ e.preventDefault(); onNavigate(it.id); }}>{it.label}</a>
          ))}
        </nav>
        <div className="zx-header__column">
          <ZxButton size="sm" variant="transparent" shape="square" ariaLabel="Search">
            <Icon name="search" size={18} />
          </ZxButton>
          <ZxButton size="sm" variant="transparent" shape="square" ariaLabel="Toggle theme" onClick={onToggleTheme}>
            <Icon name="theme" size={18} />
          </ZxButton>
          <ZxButton size="sm" variant="outlined">EN</ZxButton>
          <ZxButton size="sm" variant="primary">Sign in</ZxButton>
        </div>
      </div>
    </header>
  );
};

window.Header = Header;
