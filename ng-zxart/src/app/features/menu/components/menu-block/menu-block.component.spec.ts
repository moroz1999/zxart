import {TestBed} from '@angular/core/testing';
import {provideRouter, Router} from '@angular/router';
import {provideNoopAnimations} from '@angular/platform-browser/animations';
import {TranslateModule} from '@ngx-translate/core';
import {afterEach, describe, expect, it, vi} from 'vitest';
import {MenuBlockComponent} from './menu-block.component';
import {CurrentRouteService} from '../../../header/services/current-route.service';
import {MAIN_MENU} from '../../../../shared/navigation/menu.config';

/** Stubs the `(hover: hover)` media query the menu decides its input mode by. */
function stubHover(canHover: boolean): void {
  vi.stubGlobal('matchMedia', (query: string) => ({
    matches: query === '(hover: hover)' ? canHover : false,
    media: query,
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
  }));
}

function createMenu() {
  TestBed.configureTestingModule({
    imports: [MenuBlockComponent, TranslateModule.forRoot()],
    providers: [
      provideRouter([{path: '**', children: []}]),
      provideNoopAnimations(),
      {provide: CurrentRouteService, useValue: {isActive: () => false}},
    ],
  });
  const fixture = TestBed.createComponent(MenuBlockComponent);
  fixture.detectChanges();
  const parentIndex = MAIN_MENU.findIndex(item => item.children.length > 0);
  const trigger = (): HTMLElement => fixture.nativeElement.querySelectorAll('nav > zx-button')[parentIndex]
    .querySelector('a, button') as HTMLElement;
  return {fixture, component: fixture.componentInstance, parent: MAIN_MENU[parentIndex], trigger};
}

describe('MenuBlockComponent', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
    TestBed.resetTestingModule();
  });

  it('opens a submenu on the first tap of a touch screen instead of leaving the page', async () => {
    stubHover(false);
    const {fixture, component, parent, trigger} = createMenu();
    const router = TestBed.inject(Router);
    const navigate = vi.spyOn(router, 'navigateByUrl');

    trigger().click();
    fixture.detectChanges();
    await fixture.whenStable();

    expect(component.isPopoverOpen(parent)).toBe(true);
    expect(navigate).not.toHaveBeenCalled();
  });

  it('follows the section link on a tap of an open submenu trigger', async () => {
    stubHover(false);
    const {fixture, parent, trigger} = createMenu();
    const router = TestBed.inject(Router);
    const navigate = vi.spyOn(router, 'navigateByUrl');

    trigger().click();
    fixture.detectChanges();
    trigger().click();
    await fixture.whenStable();

    expect(navigate).toHaveBeenCalled();
    expect(String(navigate.mock.calls[0][0])).toBe(parent.url);
  });

  it('ignores the pointer entering a trigger on a touch screen', () => {
    stubHover(false);
    const {component, parent, trigger} = createMenu();

    trigger().closest('zx-button')!.dispatchEvent(new Event('pointerenter'));

    expect(component.isPopoverOpen(parent)).toBe(false);
  });

  it('opens a submenu when a mouse hovers its trigger', () => {
    stubHover(true);
    const {component, parent, trigger} = createMenu();

    trigger().closest('zx-button')!.dispatchEvent(new Event('pointerenter'));

    expect(component.isPopoverOpen(parent)).toBe(true);
  });
});
