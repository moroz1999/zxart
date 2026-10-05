import {CUSTOM_ELEMENTS_SCHEMA} from '@angular/core';
import {TestBed} from '@angular/core/testing';
import {CommonModule} from '@angular/common';
import {provideRouter} from '@angular/router';
import {Dialog, DialogRef} from '@angular/cdk/dialog';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconRegistryService} from 'angular-svg-icon';
import {describe, expect, it, vi} from 'vitest';
import {MobileNavDrawerComponent} from './mobile-nav-drawer.component';
import {ZxPopoverMenuItemComponent} from '../../../../shared/ui/zx-popover-menu-item/zx-popover-menu-item.component';
import {CurrentRouteService} from '../../services/current-route.service';

describe('MobileNavDrawerComponent', () => {
  it('closes the drawer as soon as a menu item is clicked', () => {
    const dialogRef = {close: vi.fn()};
    TestBed.configureTestingModule({
      imports: [MobileNavDrawerComponent, TranslateModule.forRoot()],
      providers: [
        provideRouter([{path: '**', children: []}]),
        {provide: DialogRef, useValue: dialogRef},
        {provide: Dialog, useValue: {open: vi.fn()}},
        {provide: SvgIconRegistryService, useValue: {loadSvg: vi.fn()}},
        {provide: CurrentRouteService, useValue: {isActive: () => false}},
      ],
    });
    TestBed.overrideComponent(MobileNavDrawerComponent, {
      set: {
        imports: [CommonModule, TranslateModule, ZxPopoverMenuItemComponent],
        schemas: [CUSTOM_ELEMENTS_SCHEMA],
      },
    });
    const fixture = TestBed.createComponent(MobileNavDrawerComponent);
    fixture.detectChanges();

    const link = fixture.nativeElement.querySelector('.mn-items a') as HTMLAnchorElement;
    link.click();

    expect(dialogRef.close).toHaveBeenCalled();
  });
});
