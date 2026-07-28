import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {Dialog} from '@angular/cdk/dialog';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-search-trigger',
  standalone: true,
  imports: [
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
  ],
  templateUrl: './search-trigger.component.html',
  styleUrls: ['./search-trigger.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SearchTriggerComponent implements OnInit {
  constructor(
    private dialog: Dialog,
    private iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}search.svg`, 'search')?.subscribe();
  }

  async openSearch(): Promise<void> {
    const {SearchDialogComponent} = await import('../search-dialog/search-dialog.component');
    this.dialog.open(SearchDialogComponent, {
      panelClass: 'zx-dialog',
      backdropClass: 'zx-dialog-backdrop',
      width: '560px',
      height: '80vh',
    });
  }
}
