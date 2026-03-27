import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {Dialog} from '@angular/cdk/dialog';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {SearchDialogComponent} from '../search-dialog/search-dialog.component';
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

  openSearch(): void {
    this.dialog.open(SearchDialogComponent, {
      panelClass: 'zx-search-dialog',
      backdropClass: 'zx-dialog-backdrop',
    });
  }
}
