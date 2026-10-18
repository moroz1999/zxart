import {TestBed} from '@angular/core/testing';
import {TranslateModule} from '@ngx-translate/core';
import {of} from 'rxjs';
import {afterEach, describe, expect, it} from 'vitest';
import {ZxProdInstructionsComponent} from './zx-prod-instructions.component';
import {ProdDescriptionApiService} from '../../services/prod-description-api.service';
import {ProdDescriptionDto} from '../../models/prod-description.dto';

function render(dto: ProdDescriptionDto): HTMLElement {
  TestBed.configureTestingModule({
    imports: [ZxProdInstructionsComponent, TranslateModule.forRoot()],
    providers: [{provide: ProdDescriptionApiService, useValue: {getDescription: () => of(dto)}}],
  });
  const fixture = TestBed.createComponent(ZxProdInstructionsComponent);
  fixture.componentRef.setInput('elementId', 1);
  fixture.detectChanges();
  return fixture.nativeElement as HTMLElement;
}

describe('ZxProdInstructionsComponent', () => {
  afterEach(() => TestBed.resetTestingModule());

  it('shows plain instructions on the fixed-width grid, as written', () => {
    const element = render({description: '', htmlDescription: true, htmlInstructions: false, instructions: 'Q - up\n<A> - down'});

    const preformatted = element.querySelector('zx-preformatted');
    expect(preformatted).not.toBeNull();
    expect(preformatted!.textContent).toContain('<A> - down');
  });

  it('shows formatted instructions as markup', () => {
    const element = render({description: '', htmlDescription: false, htmlInstructions: true, instructions: 'Press <b>fire</b>'});

    expect(element.querySelector('zx-preformatted')).toBeNull();
    expect(element.querySelector('b')?.textContent).toBe('fire');
  });
});
