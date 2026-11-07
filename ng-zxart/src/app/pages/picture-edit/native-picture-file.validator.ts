import {PictureFileFormatRule} from '../../shared/models/form-data-response';

export type NativePictureFormatRule = PictureFileFormatRule;

export interface NativePictureFileValidationMessage {
  key: string;
  params: {
    extensions: string;
    sizes?: string;
  };
}

const PC_IMAGE_TYPES = new Set(['image/png', 'image/gif', 'image/jpeg', 'image/bmp']);

export function validateNativePictureFiles(
  files: readonly File[],
  rule: NativePictureFormatRule,
): NativePictureFileValidationMessage | null {
  for (const file of files) {
    const extension = file.name.includes('.') ? file.name.split('.').pop()?.toLowerCase() ?? '' : '';
    const hasAllowedExtension = rule.extensions.includes(extension);
    const bmpIsExpected = extension === 'bmp' && rule.extensions.length === 1 && rule.extensions[0] === 'bmp';
    const hasForbiddenImageType = PC_IMAGE_TYPES.has(file.type.toLowerCase()) && !bmpIsExpected;
    const hasAllowedSize = rule.sizes.length === 0 || rule.sizes.includes(file.size);
    if (!hasAllowedExtension || hasForbiddenImageType || !hasAllowedSize) {
      return nativePictureFileRequirements(rule);
    }
  }

  return null;
}

export function nativePictureFileRequirements(
  rule: NativePictureFormatRule,
): NativePictureFileValidationMessage {
  const extensions = rule.extensions.map(extension => `.${extension}`).join(', ');
  if (rule.sizes.length === 0) {
    return {
      key: 'picture-form.error-native-file-variable',
      params: {extensions},
    };
  }

  return {
    key: 'picture-form.error-native-file',
    params: {
      extensions,
      sizes: rule.sizes.map(size => `${size} bytes`).join(', '),
    },
  };
}
