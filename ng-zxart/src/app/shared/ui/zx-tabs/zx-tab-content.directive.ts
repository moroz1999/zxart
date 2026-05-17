import { Directive, TemplateRef } from '@angular/core';

@Directive({
  selector: '[zxTabContent]',
  standalone: true,
})
export class ZxTabContentDirective {
  constructor(readonly templateRef: TemplateRef<unknown>) {}
}
