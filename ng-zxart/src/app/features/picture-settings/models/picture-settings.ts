export type PictureMode = 'mix' | 'flicker' | 'interlace1' | 'interlace2';

export interface PictureSettings {
  mode: PictureMode;
  border: boolean;
  hidden: boolean;
}

export const PICTURE_SETTINGS_DEFAULTS: PictureSettings = {
  mode: 'mix',
  border: true,
  hidden: false,
};

/** Zoom of the picture details viewer. */
export type PictureScale = 'wide' | '1' | '2' | '3';

export const PICTURE_SCALE_VALUES: readonly PictureScale[] = ['wide', '1', '2', '3'];

/**
 * Widest zoom a desktop column fits; narrower devices reduce it themselves, so
 * this doubles as the "wide on tablet and phone" default.
 */
export const PICTURE_SCALE_DEFAULT: PictureScale = '3';

export const PREF_CODE_PICTURE_MODE = 'picture_mode';
export const PREF_CODE_PICTURE_BORDER = 'picture_border';
export const PREF_CODE_PICTURE_HIDDEN = 'picture_hidden';
export const PREF_CODE_PICTURE_SCALE = 'picture_scale';
