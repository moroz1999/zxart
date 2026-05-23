# zx-release-type-badge

`zx-release-type-badge` displays a release type as a compact colored badge.

Use it for real release type values from `ReleaseTypes`: `unknown`, `original`, `rerelease`, `adaptation`, `localization`, `bugfix`, `mod`, `crack`, `mia`, `corrupted`, `compilation`, `incomplete`, and `demoversion`. The legacy `demo` value is supported as a demo-version alias.

Geometry follows the ZXArt Design System release pill: 10px text, 1px/6px padding, and 8px radius.

The colors follow the ZXArt Design System release pills:
- `original`, `rerelease`: `primary-500`
- `adaptation`, `bugfix`, `demo`, `demoversion`: `primary-700`
- `localization`: `warning-700`
- `mod`: `warning-500` with dark text
- `crack`: `danger-600`
- `unknown`, `mia`, `corrupted`, `compilation`, `incomplete`: `secondary-500`
