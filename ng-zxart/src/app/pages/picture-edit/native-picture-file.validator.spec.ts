import {describe, expect, it} from 'vitest';
import {
  NativePictureFormatRule,
  validateNativePictureFiles,
} from './native-picture-file.validator';

describe('validateNativePictureFiles', () => {
  const standardRule: NativePictureFormatRule = {
    extensions: ['scr', 'bin', 'c'],
    sizes: [6912],
  };

  it.each(['screen.scr', 'screen.BIN', 'screen.C'])('accepts a classic screen named %s', name => {
    expect(validateNativePictureFiles([file(name, 6912)], standardRule)).toBeNull();
  });

  it('rejects an unknown extension and lists every accepted extension and size', () => {
    expect(validateNativePictureFiles([file('screen.raw', 6912)], standardRule)).toEqual({
      key: 'picture-form.error-native-file',
      params: {
        extensions: '.scr, .bin, .c',
        sizes: '6912 bytes',
      },
    });
  });

  it('rejects a wrong size and lists every accepted extension and size', () => {
    expect(validateNativePictureFiles([file('screen.scr', 6911)], standardRule)).toEqual({
      key: 'picture-form.error-native-file',
      params: {
        extensions: '.scr, .bin, .c',
        sizes: '6912 bytes',
      },
    });
  });

  it.each(['screen.png', 'screen.gif', 'screen.jpg', 'screen.jpeg', 'screen.bmp'])(
    'rejects PC image file %s for a classic screen even when its size matches',
    name => {
      expect(validateNativePictureFiles([file(name, 6912, 'image/test')], standardRule)).not.toBeNull();
    },
  );

  it('rejects the whole batch when one file does not follow the selected format', () => {
    expect(validateNativePictureFiles([
      file('first.scr', 6912),
      file('second.raw', 6912),
    ], standardRule)).not.toBeNull();
  });

  it('allows BMP only when the selected rule explicitly requires it', () => {
    const zxEvoRule: NativePictureFormatRule = {extensions: ['bmp'], sizes: []};

    expect(validateNativePictureFiles([file('screen.bmp', 32118, 'image/bmp')], zxEvoRule)).toBeNull();
  });
});

function file(name: string, size: number, type = 'application/octet-stream'): File {
  return {name, size, type} as File;
}
