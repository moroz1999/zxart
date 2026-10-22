import {describe, expect, it} from 'vitest';
import {ZxPixelZoomComponent} from './zx-pixel-zoom.component';

describe('ZxPixelZoomComponent', () => {
  function create(width: number, height: number, image = '/a.png'): ZxPixelZoomComponent {
    const component = new ZxPixelZoomComponent();
    component.image = image;
    component.width = width;
    component.height = height;
    return component;
  }

  it('builds one magnifier while the size is known', () => {
    expect(create(640, 480).instances).toHaveLength(1);
    expect(create(0, 0).instances).toHaveLength(0);
  });

  it('rebuilds the magnifier when the size or the image changes, as the library measures it once', () => {
    const [first] = create(640, 480).instances;

    expect(create(960, 720).instances[0]).not.toBe(first);
    expect(create(640, 480, '/b.png').instances[0]).not.toBe(first);
    expect(create(640, 480).instances[0]).toBe(first);
  });
});
