import {describe, expect, it} from 'vitest';
import {integerFitScale} from './integer-fit-scale';

describe('integerFitScale', () => {
  it('takes the largest whole multiple of the native size that fits with the margin', () => {
    // 1000 - 2*16 = 968 wide → 3×256 fits, 4×256 does not
    expect(integerFitScale({width: 256, height: 192}, {width: 1000, height: 800}, 16)).toBe(3);
  });

  it('is limited by the tighter side', () => {
    expect(integerFitScale({width: 256, height: 192}, {width: 2000, height: 600}, 16)).toBe(2);
  });

  it('keeps a picture that fits exactly at that multiple', () => {
    expect(integerFitScale({width: 256, height: 192}, {width: 544, height: 416}, 16)).toBe(2);
  });

  it('shrinks a picture larger than the stage just enough to fit', () => {
    expect(integerFitScale({width: 1000, height: 500}, {width: 532, height: 900}, 16)).toBeCloseTo(0.5);
  });

  it('shows nothing for an unknown size', () => {
    expect(integerFitScale({width: 0, height: 0}, {width: 800, height: 600}, 16)).toBe(0);
  });
});
