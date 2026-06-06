# Tune (Music) Details page — rebuild to match the `tune-page.html` prototype

## Context

The single-tune ("страница музыки") page is currently rendered by legacy Smarty
(`project/templates/public/zxMusic.details.tpl` + `component.musicinfo.tpl`) and only
embeds a few Angular web-components. The prototype
`ZXArt Design System/ui_kits/website/TunePage.jsx` (+ `tune-data.jsx`, `tune-page.css`)
deliberately mirrors `PicturePage`, so the tune page should read identically to the
picture / prod / release detail pages.

**Goal:** rebuild the music page from the prototype's *structure*, built 100% from the
**existing** design-system / layout / typography components — no new **atomic**
(`shared/ui`) components — making it uniform with the freshly-built `picture-details`,
`prod-details` and `release-details` features. Full-stack mirror of `picture-details`.

**Decisions:**
- Backend: full-stack mirror — new `/tune-details/` REST endpoint
  (`TuneDetailsService` + `TuneDetailsRestDto` + `TuneDetails` controller) and the legacy
  `zxMusic.details.tpl` body replaced with `<zx-tune-details element-id="…">`.
- Hero left "player" column (oscilloscope + transport + progress) is a **static stub**
  for now (the spectral analyser is wired separately later).

---

## Target structure (from `TunePage.jsx`)

1. Breadcrumbs (Author → Title)
2. Hero (2-col): left = **player stub**; right = head meta (title + `#id`, authors + year,
   context box with medal/competition/compo/place + chip row, "В модуле" internal
   title/author, rating + votes/plays, added-by)
3. Tags band
4. Сведения + Скачать (2-col): meta `<dl>` + collapsible "Технические данные"; downloads
5. Голоса + Комментарии (50/50)
6. Related rails (3): author / tags / tracker

---

## Execution checklist

- [x] Backend: `TuneDetailsDto` (+ nested `TuneTagDto`, `TunePartyContextDto`, `TuneDownloadDto`, `TuneSubmitterDto`) & matching `Rest\*RestDto`
- [x] Backend: `TuneDetailsService` + `TuneDetailsException`
- [x] Backend: `Controllers/TuneDetails.php` (`/tune-details/`)
- [x] Backend: related rails on `Musiclist` controller + `MusicListService` (author/tags/tracker) + `TunesRepository` queries
- [x] Backend: OpenAPI spec updated (`api/tunes.yaml`)
- [x] FE: `tune-details.dto.ts` + `tune-details-api.service.ts`
- [x] FE: `zx-tune-details` root + skeleton; register custom element in `app.module.ts`
- [x] FE: `zx-tune-player` STATIC STUB (oscilloscope + transport + progress placeholder)
- [x] FE: hero head inline (title/#id/edit, authors+year, context box, "В модуле", rating, added-by)
- [x] FE: `zx-tune-meta-panel` (dl + collapsible tech) via `zx-item-data-item` + `zx-collapsible-section`
- [x] FE: `zx-tune-downloads-panel`
- [x] FE: tags band (`zx-tags-list` in `zx-panel`)
- [x] FE: votes/comments grid (`zx-ratings-list-view` + `zx-comments-list-view`)
- [x] FE: `zx-tune-related-section` (3 lazy rails, compact mini-rows)
- [x] FE: i18n keys (en/ru/es)
- [x] Legacy: swap `zxMusic.details.tpl` body to `<zx-tune-details>`
- [x] Build: `composer run build` clean; `php -l` clean on new PHP
- [x] Verify with one backend agent + one frontend agent vs the docs (both reported full compliance, no fixes needed)

---

## Verification

1. `composer run build` (never `ng build`) clean; `php -l` clean on new PHP; `composer psalm` on new backend.
2. Runtime: open a tune details URL — all sections render; left player shows as a labelled stub; rating/playlist/comments work.
3. Visual compare vs `tune-page.html` and side-by-side with picture/prod/release pages.
4. Two review subagents (backend + frontend) compare against project docs and fix divergences.
