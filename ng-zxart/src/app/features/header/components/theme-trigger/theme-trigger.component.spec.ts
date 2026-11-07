import {CUSTOM_ELEMENTS_SCHEMA} from '@angular/core';
import {TestBed} from '@angular/core/testing';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconRegistryService} from 'angular-svg-icon';
import {of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {ThemeService} from '../../../settings/services/theme.service';
import {ThemeTriggerComponent} from './theme-trigger.component';

describe('ThemeTriggerComponent', () => {
  it('cycles automatic, dark, and light modes', () => {
    const themeService = {
      currentTheme: null as 'light' | 'dark' | null,
      preference$: of(null),
      setTheme: vi.fn((theme: 'light' | 'dark' | null) => themeService.currentTheme = theme),
    };
    const component = new ThemeTriggerComponent(themeService as unknown as ThemeService, {
      loadSvg: vi.fn(),
    } as never);

    component.toggle();
    expect(themeService.setTheme).toHaveBeenLastCalledWith('dark');

    component.toggle();
    expect(themeService.setTheme).toHaveBeenLastCalledWith('light');

    component.toggle();
    expect(themeService.setTheme).toHaveBeenLastCalledWith(null);
  });

  it('shows the automatic theme icon in automatic mode', () => {
    TestBed.configureTestingModule({
      imports: [ThemeTriggerComponent, TranslateModule.forRoot()],
      providers: [{
        provide: ThemeService,
        useValue: {
          currentTheme: null,
          preference$: of(null),
          initialize: vi.fn(),
          setTheme: vi.fn(),
        },
      }, {
        provide: SvgIconRegistryService,
        useValue: {loadSvg: vi.fn(() => of(null))},
      }],
    });
    TestBed.overrideComponent(ThemeTriggerComponent, {
      set: {
        imports: [TranslateModule],
        schemas: [CUSTOM_ELEMENTS_SCHEMA],
      },
    });
    const fixture = TestBed.createComponent(ThemeTriggerComponent);

    fixture.detectChanges();

    expect(fixture.nativeElement.querySelector('svg-icon[name="theme-auto"]')).not.toBeNull();
  });
});
