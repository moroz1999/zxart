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

export const PREF_CODE_PICTURE_MODE = 'picture_mode';
export const PREF_CODE_PICTURE_BORDER = 'picture_border';
export const PREF_CODE_PICTURE_HIDDEN = 'picture_hidden';
