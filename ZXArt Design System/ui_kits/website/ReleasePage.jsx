/* ReleasePage.jsx — v2.
   • Inline prod-style meta strings (no dl sidebar).
   • Reworded parent anchor — short "К программе" arrow.
   • File-tree section, always present.
   • Instructions open in a modal preview.
   • Graceful minimum-case rendering (no description / no screens / no
     covers / no instructions). */

const { useState } = React;

function I({ name, size = 16 }) {
  const p = {
    play:       "M8 5v14l11-7z",
    fullscreen: "M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z",
    download:   "M5 20h14v-2H5v2zm7-18l-5.5 5.5h3.5V14h4V7.5h3.5L12 2z",
    warn:       "M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z",
    eye:        "M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5C21.3 7.6 17 4.5 12 4.5zm0 12.5a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z",
    close:      "M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z",
    folder:     "M10 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-8l-2-2z",
    file:       "M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z",
    zip:        "M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-3 17h-2v-2h2v2zm0-4h-2v-2h2v2zm0-4h-2V9h2v2zm0-4h-2V5h2v2z",
    plus:       "M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6z",
  };
  return (
    <svg viewBox="0 0 24 24" width={size} height={size} fill="currentColor" aria-hidden="true" style={{ flexShrink: 0 }}>
      <path d={p[name]}></path>
    </svg>
  );
}

function CassetteCover({ cover }) {
  const palettes = {
    sunset: { top: "#3d0000", bot: "#1a0000", band: "#bb0000" },
    cool:   { top: "#001d38", bot: "#000f1f", band: "#1a90ff" },
    forest: { top: "#0d3b66", bot: "#000",    band: "#2d8659" },
    night:  { top: "#262626", bot: "#000",    band: "#404040" },
  };
  const p = palettes[cover.palette] || palettes.cool;
  const isLabel = cover.kind === "label";
  return (
    <div
      className={"rp-cassette" + (isLabel ? " rp-cassette--label" : "")}
      style={isLabel ? {} : { "--cassette-bg-top": p.top, "--cassette-bg-bot": p.bot, "--cassette-band": p.band }}
    >
      <div className="rp-cassette__band">
        <span className="rp-cassette__title">CRYSTAL KINGDOM DIZZY</span>
      </div>
      <div className="rp-cassette__art">
        <ZxScreen seed={cover.id * 71 + 9} palette={cover.palette}/>
      </div>
      <div className="rp-cassette__foot">
        <span>SCORPION SOFT</span>
        <span>{isLabel ? "SCL · 1995" : "TR-DOS · 1995"}</span>
      </div>
    </div>
  );
}

function MediaTile({ kind, label, size, children }) {
  return (
    <a className={"rp-tile rp-tile--" + kind} href="#" onClick={(e)=>e.preventDefault()}>
      <div className="rp-tile__img">{children}</div>
      <div className="rp-tile__meta">
        <span className="rp-tile__label">{label}</span>
        <span className="rp-tile__size">{size}</span>
      </div>
      <div className="rp-tile__hover">
        <button title="Открыть в просмотрщике"><I name="eye" size={12}/>Открыть</button>
        <button title="Скачать оригинал"><I name="download" size={12}/></button>
      </div>
    </a>
  );
}

/* ── File tree row ── */
function fmtBytes(n) {
  if (n == null) return "—";
  if (n < 1024) return n.toLocaleString("ru-RU");
  if (n < 1024 * 1024) return (n / 1024).toFixed(1).replace(".0", "") + " КБ";
  return (n / (1024 * 1024)).toFixed(2).replace(/\.?0+$/, "") + " МБ";
}
function FileTreeRow({ row, onPreview }) {
  const indents = Array.from({ length: row.d });
  const isFolder = row.kind === "folder";
  const isZip = row.kind === "zip";
  const kindLabel = isZip ? "ZIP архив" : isFolder ? "Папка" : (row.ext || "Файл");
  const icon = isZip ? "zip" : isFolder ? "folder" : "file";
  return (
    <div className="rp-tree__row">
      <div className="rp-tree__name">
        {indents.map((_, i) => <span key={i} className="rp-tree__indent-rule"/>)}
        <span className="rp-tree__icon"><I name={icon}/></span>
        {row.kind === "file" ? (
          <span className="rp-tree__name--file">
            {row.viewable
              ? <a href="#" onClick={(e)=>{e.preventDefault(); onPreview && onPreview(row);}}>{row.name}</a>
              : <a href="#" onClick={(e)=>e.preventDefault()}>{row.name}</a>}
          </span>
        ) : (
          <span className={isZip ? "rp-tree__name--zip" : "rp-tree__name--folder"}>{row.name}</span>
        )}
      </div>
      <div className="rp-tree__size">{fmtBytes(row.size)}</div>
      <div className="rp-tree__type">{kindLabel}</div>
      <div className="rp-tree__actions">
        {(row.kind === "file" || row.kind === "zip") && (
          <a href="#" onClick={(e)=>e.preventDefault()}><I name="download" size={14}/>Скачать</a>
        )}
        {row.kind === "file" && row.viewable && (
          <a href="#" onClick={(e)=>{e.preventDefault(); onPreview && onPreview(row);}}><I name="eye" size={14}/>Просмотреть</a>
        )}
      </div>
    </div>
  );
}

/* ── Instruction file row (opens modal) ── */
function InstructionRow({ file, onPreview }) {
  const ext = file.file.split(".").pop().toUpperCase();
  return (
    <div className="rp-file" onClick={()=>onPreview && onPreview(file)}>
      <div className="rp-file__icon">{ext}</div>
      <div className="rp-file__body">
        <div className="rp-file__title">{file.title}</div>
        <div className="rp-file__meta">{file.file} · {file.size}</div>
      </div>
      <span className="rp-file__lang">{file.lang}</span>
      <div className="rp-file__act">
        <a href="#" onClick={(e)=>{e.preventDefault(); onPreview && onPreview(file);}}><I name="eye" size={14}/>Просмотреть</a>
        <a href="#" onClick={(e)=>e.preventDefault()}><I name="download" size={14}/>Скачать</a>
      </div>
    </div>
  );
}

/* ── Instruction preview modal ── */
function InstructionModal({ file, onClose }) {
  return (
    <div className="rp-modal-backdrop" onClick={onClose}>
      <div className="rp-modal" onClick={(e)=>e.stopPropagation()}>
        <div className="rp-modal__head">
          <span className="rp-modal__title">{file.name || file.file}</span>
          <span className="rp-modal__meta">{file.size ? (typeof file.size === "string" ? file.size : fmtBytes(file.size)) : ""}</span>
          <button className="rp-modal__close zx-button zx-button--transparent zx-button--sm zx-button--square" onClick={onClose} title="Закрыть"><I name="close"/></button>
        </div>
        <div className="rp-modal__body">
          <pre className="rp-modal__pre">{file.body || README_RU}</pre>
        </div>
        <div className="rp-modal__foot">
          <span className="left">Текст · UTF-8 · моноширинный шрифт</span>
          <div className="zx-button-controls zx-button-controls--align-end">
            <button className="zx-button zx-button--outlined zx-button--sm" type="button"><I name="download" size={14}/>Скачать</button>
            <button className="zx-button zx-button--primary zx-button--sm" type="button" onClick={onClose}>Закрыть</button>
          </div>
        </div>
      </div>
    </div>
  );
}

function ReleasePage({
  minimal = false,
  showInstructionModal = false,
}) {
  const t = REL_TYPES[RELEASE.type];

  const hasDescription = !minimal;
  const hasScreens     = !minimal && REL_SCREENS.length > 0;
  const hasCovers      = !minimal && COVERS.length > 0;
  const hasInstructions= !minimal && INSTRUCTIONS.length > 0;
  const votes          = minimal ? [] : REL_VOTES;
  const comments       = minimal ? [] : REL_COMMENTS;

  const [previewFile, setPreviewFile] = useState(
    showInstructionModal ? { name: INSTRUCTIONS[0].file, size: INSTRUCTIONS[0].size, body: README_RU } : null
  );

  return (
    <div className="rp-root">
      {/* breadcrumb */}
      <nav className="zx-breadcrumbs" aria-label="breadcrumb">
        <a href="#">Главная</a> <span className="sep">/</span> <a href="#">Софт</a> <span className="sep">/</span> <a href="#">Игры</a> <span className="sep">/</span>{" "}
        <a href="#">{RELEASE.prod.title}</a> <span className="sep">/</span>{" "}
        <span className="here">Релиз — {RELEASE.publishers[0].name}, {RELEASE.year}</span>
      </nav>

      {/* parent-prod anchor — short, elegant */}
      <a className="rp-anchor" href="#" onClick={(e)=>e.preventDefault()}>
        <span className="rp-anchor__arrow">←</span>
        <span className="rp-anchor__thumb"><ZxScreen seed={42} palette={RELEASE.prod.cover}/></span>
        <span className="rp-anchor__body">
          <span className="rp-anchor__label">к программе</span>
          <span className="rp-anchor__title">{RELEASE.prod.title}</span>
        </span>
        <span className="rp-anchor__meta">{RELEASE.prod.year} · {RELEASE.prod.authors[0]}</span>
      </a>

      {/* header */}
      <header className="rp-head">
        <div className="rp-head__title-row">
          <h1 className="rp-head__title">{RELEASE.title}</h1>
          <span className="rp-head__year"><a href="#">{RELEASE.year}</a></span>
          <span className={"zx-release-type-badge zx-release-type-badge--" + RELEASE.type}>{t.label}</span>
          <span className="rp-status"><I name="warn" size={12}/>{RELEASE.status.label}</span>
        </div>

        <div className="rp-people">
          <b>Издатели:</b>{" "}
          {RELEASE.publishers.map((p, i) => (
            <React.Fragment key={p.id}>
              {i > 0 && ", "}
              <a href="#">{p.name}</a>
              <span style={{ color:"var(--text-light-color)" }}> ({p.role.toLowerCase()})</span>
            </React.Fragment>
          ))}
          <span className="dot">·</span>
          <b>Формат:</b> <span style={{ fontFamily:"var(--font-mono)" }}>{RELEASE.format}</span>
          <span style={{ color:"var(--secondary-400)" }}> · {RELEASE.size}</span>
        </div>

        <div className="rp-hw">
          <span className="rp-hw__label">Железо:</span>
          {RELEASE.hardware.map(h => (
            <a key={h.id} href="#">{h.name}</a>
          ))}
        </div>

        <div className="rp-bar">
          {votes.length > 0 ? (
            <>
              <div className="rp-bar__rating">
                <span className="num">{RELEASE.votes.score}</span>
                <span className="of">/ 5</span>
                <span className="votes">· {RELEASE.votes.count} голосов</span>
              </div>
              <VoteWidget myVote={5} fav={false}/>
            </>
          ) : (
            <div className="rp-bar__rating" style={{ color:"var(--text-light-color)" }}>
              <span className="of">Голосов пока нет.</span>
              <VoteWidget myVote={0} fav={false}/>
            </div>
          )}

          <div className="rp-bar__counters">
            <span><b>{RELEASE.downloads}</b> скачиваний</span>
            <span><b>{RELEASE.plays}</b> запусков</span>
            <span>добавлен <b>{RELEASE.addedAt}</b> <a href="#" style={{color:"var(--primary-600)",textDecoration:"none"}}>{RELEASE.addedBy}</a></span>
          </div>

          <div className="rp-bar__actions zx-button-controls zx-button-controls--align-end">
            <button className="zx-button zx-button--primary zx-button--md" type="button">
              <I name="play" size={18}/>Запустить
              <span className="rp-action__hint">в эмуляторе</span>
            </button>
            <button className="zx-button zx-button--outlined zx-button--md zx-button--square" type="button" title="Полноэкранный режим эмулятора">
              <I name="fullscreen" size={18}/>
            </button>
            <button className="zx-button zx-button--outlined zx-button--md" type="button">
              <I name="download" size={18}/>{RELEASE.format.split(" ")[0]}
              <span className="rp-action__hint">{RELEASE.size}</span>
            </button>
          </div>
        </div>
      </header>

      {/* content */}
      <div className="rp-content">

        {/* Description — inline empty fallback */}
        <section>
          <div className="rp-section__h">
            <h2>Описание</h2>
          </div>
          {hasDescription ? (
            <p className="rp-desc">{RELEASE.description}</p>
          ) : (
            <p className="rp-inline-empty">
              Описание этого релиза не добавлено. <a href="#">Помочь архиву — предложить текст ›</a>
            </p>
          )}
        </section>

        {/* Screens — hidden entirely when empty */}
        {hasScreens && (
          <section>
            <div className="rp-section__h">
              <h2>Скрины релиза</h2>
              <span className="count">{REL_SCREENS.length}</span>
              <span className="right"><a href="#">все скрины ›</a></span>
            </div>
            <div className="rp-tiles rp-tiles--screens">
              {REL_SCREENS.map(s => (
                <MediaTile key={s.id} kind="screen" label={s.file} size={s.size}>
                  <ZxScreen seed={s.id} palette={s.palette}/>
                </MediaTile>
              ))}
            </div>
          </section>
        )}

        {/* Covers — hidden entirely when empty */}
        {hasCovers && (
          <section>
            <div className="rp-section__h">
              <h2>Обложки</h2>
              <span className="count">{COVERS.length}</span>
              <span className="right"><a href="#">скачать архивом ›</a></span>
            </div>
            <div className="rp-tiles rp-tiles--covers">
              {COVERS.map(c => (
                <MediaTile key={c.id} kind="cover" label={c.label} size={c.size}>
                  <CassetteCover cover={c}/>
                </MediaTile>
              ))}
            </div>
          </section>
        )}

        {/* File tree — ALWAYS present, anchors the page in the minimum case */}
        <section>
          <div className="rp-section__h">
            <h2>Структура файлов и папок</h2>
            <span className="count">{FILE_TREE.length}</span>
            <span className="right"><a href="#">скачать всё ›</a></span>
          </div>
          <div className="rp-tree">
            {FILE_TREE.map((row, i) => (
              <FileTreeRow key={i} row={row} onPreview={(r)=>setPreviewFile({ name:r.name, size:r.size, body:README_RU })}/>
            ))}
          </div>
        </section>

        {/* Instructions — hidden when empty */}
        {hasInstructions && (
          <section>
            <div className="rp-section__h">
              <h2>Инструкция</h2>
              <span className="count">{INSTRUCTIONS.length}</span>
            </div>
            <div className="rp-files">
              {INSTRUCTIONS.map((f, i) => (
                <InstructionRow key={i} file={f} onPreview={(file)=>setPreviewFile({ name:file.file, size:file.size, body:README_RU })}/>
              ))}
            </div>
          </section>
        )}

        {/* Contribute CTA — shown only in the minimal case */}
        {minimal && (
          <div className="rp-contribute">
            <div>
              <div className="rp-contribute__title">Помогите дополнить релиз</div>
              <div className="rp-contribute__hint">
                В архиве нет описания, обложек, скринов и инструкций для этой версии.
                Если у вас сохранилась кассета или диск — поделитесь.
              </div>
            </div>
            <div className="rp-contribute__actions zx-button-controls zx-button-controls--align-end">
              <button className="zx-button zx-button--outlined zx-button--sm" type="button"><I name="plus" size={14}/>Описание</button>
              <button className="zx-button zx-button--outlined zx-button--sm" type="button"><I name="plus" size={14}/>Обложки</button>
              <button className="zx-button zx-button--outlined zx-button--sm" type="button"><I name="plus" size={14}/>Скрины</button>
              <button className="zx-button zx-button--outlined zx-button--sm" type="button"><I name="plus" size={14}/>Инструкцию</button>
            </div>
          </div>
        )}

        {/* Vote history */}
        <section>
          <div className="rp-section__h">
            <h2>История голосов</h2>
            <span className="count">{votes.length}</span>
            {votes.length > 0 && (
              <span className="right">средняя <b style={{color:"var(--warning-700)"}}>★ {RELEASE.votes.score}</b></span>
            )}
          </div>
          {votes.length > 0 ? (
            <div className="rp-list">
              {votes.map((v, i) => (
                <div key={i} className="rp-list__row">
                  <span className="rp-list__date">{v.date}</span>
                  <span className="rp-list__user"><a href="#" style={{color:"inherit"}}>{v.user}</a></span>
                  <span className="rp-list__score">{"★".repeat(v.score)}{"☆".repeat(5 - v.score)}</span>
                </div>
              ))}
            </div>
          ) : (
            <p className="rp-comment--empty">Этот релиз ещё никто не оценивал.</p>
          )}
        </section>

        {/* Comments */}
        <section>
          <div className="rp-section__h">
            <h2>Комментарии</h2>
            <span className="count">{comments.length}</span>
          </div>
          {comments.length > 0 ? (
            <div>
              {comments.map(c => (
                <div key={c.id} className="rp-comment">
                  <div className="rp-comment__head">
                    <span className="rp-comment__user"><a href="#" style={{color:"inherit"}}>{c.user}</a></span>
                    <span className="rp-comment__date">{c.date}</span>
                  </div>
                  <div className="rp-comment__body">{c.body}</div>
                </div>
              ))}
            </div>
          ) : (
            <p className="rp-comment--empty">Будьте первым, кто напишет о релизе.</p>
          )}

          <div className="rp-add">
            <div className="rp-add__h">Добавить комментарий</div>
            <textarea placeholder="Поделиться наблюдением о релизе…"></textarea>
            <div className="rp-add__foot">
              <span className="rp-add__hint">Markdown · комментарий привязывается к релизу, не к программе</span>
              <button className="zx-button zx-button--primary zx-button--sm" type="button">Отправить</button>
            </div>
          </div>
        </section>
      </div>

      {/* Instruction-preview modal (overlay) */}
      {previewFile && (
        <InstructionModal file={previewFile} onClose={()=>setPreviewFile(null)}/>
      )}
    </div>
  );
}

window.ReleasePage = ReleasePage;
