import {enumDefaultValue} from './enum-default';

describe('enumDefaultValue', () => {
  const pictureTypes = [
    {value: 'standard', label: 'Standard'},
    {value: 'gigascreen', label: 'Gigascreen'},
  ];

  it('starts a required enum on its first option', () => {
    expect(enumDefaultValue(pictureTypes, '')).toBe('standard');
  });

  it('keeps the stored value', () => {
    expect(enumDefaultValue(pictureTypes, 'gigascreen')).toBe('gigascreen');
  });

  it('keeps the empty value of a list that has an empty option', () => {
    expect(enumDefaultValue([{value: '', label: ''}, ...pictureTypes], '')).toBe('');
  });

  it('stays empty until the options are loaded', () => {
    expect(enumDefaultValue(undefined, '')).toBe('');
  });
});
