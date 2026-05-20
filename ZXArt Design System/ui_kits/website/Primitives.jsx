/** zx-button — port of ng-zxart/src/app/shared/ui/zx-button.component */
const ZxButton = ({
  children,
  size = "md",
  variant = "primary",
  shape, // "square" | "round" | undefined
  disabled = false,
  href,
  onClick,
  ariaLabel,
  style,
}) => {
  const classes = [
    "zx-button",
    `zx-button--${size}`,
    `zx-button--${variant}`,
    shape ? `zx-button--${shape}` : "",
  ].filter(Boolean).join(" ");
  const Tag = href ? "a" : "button";
  return (
    <Tag
      className={classes}
      href={href}
      onClick={(e) => { if (!href) e.preventDefault?.(); onClick && onClick(e); }}
      disabled={disabled}
      aria-label={ariaLabel}
      style={style}
    >
      {children}
    </Tag>
  );
};

const ZxBadge = ({ children, variant = "secondary" }) => (
  <span className={`zx-badge zx-badge--${variant}`}>{children}</span>
);

const ZxMedal = ({ place }) => {
  const klass = place === 1 ? "gold" : place === 2 ? "silver" : "bronze";
  return <span className={`zx-medal zx-medal--${klass}`}>{place}</span>;
};

const ZxStars = ({ value = 0, count = 0 }) => (
  <span className="zx-vote">
    {[1,2,3,4,5].map(i => (
      <svg key={i} className={`star ${i > value ? "star--off" : ""}`} viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
        <path d="M12 2l3.09 6.26 6.91 1-5 4.87L18.18 22 12 18.27 5.82 22l1.18-7.87-5-4.87 6.91-1z"/>
      </svg>
    ))}
    {count > 0 && <span className="count">{count}</span>}
  </span>
);

Object.assign(window, { ZxButton, ZxBadge, ZxMedal, ZxStars });
