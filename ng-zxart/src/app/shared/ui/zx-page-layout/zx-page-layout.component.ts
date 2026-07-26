import {ChangeDetectionStrategy, Component} from '@angular/core';

@Component({
  selector: 'zx-page-layout',
  standalone: true,
  templateUrl: './zx-page-layout.component.html',
  styleUrl: './zx-page-layout.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPageLayoutComponent {}
