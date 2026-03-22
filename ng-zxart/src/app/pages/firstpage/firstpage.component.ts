import {ChangeDetectionStrategy, Component, Injector, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Dialog} from '@angular/cdk/dialog';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../environments/environment';
import {TranslateModule} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {FirstpageConfigService} from '../../features/firstpage/services/firstpage-config.service';
import {FirstpageConfig, ModuleConfig} from '../../features/firstpage/models/firstpage-config';
import {MODULE_COMPONENTS} from '../../features/firstpage/services/module-registry';
import {MODULE_SETTINGS} from '../../features/firstpage/models/module-settings.token';
import {
  FirstpageConfigDialogComponent
} from '../../features/firstpage/components/firstpage-config-dialog/firstpage-config-dialog.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxHeading1Directive} from '../../shared/directives/typography/typography.directives';
import {
  PictureGalleryHostComponent
} from '../../features/picture-gallery/components/picture-gallery-host/picture-gallery-host.component';

interface ModuleEntry {
  config: ModuleConfig;
  component: any;
  injector: Injector;
}

@Component({
  selector: 'zx-firstpage',
  standalone: true,
  imports: [
    CommonModule,
    SvgIconComponent,
    TranslateModule,
    ZxStackComponent,
    ZxButtonComponent,
    ZxHeading1Directive,
    PictureGalleryHostComponent,

  ],
  templateUrl: './firstpage.component.html',
  styleUrls: ['./firstpage.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FirstpageComponent implements OnInit {
  readonly modules$: Observable<ModuleEntry[]> = this.configService.getConfig().pipe(
    map(config => this.buildModules(config))
  );

  constructor(
    private configService: FirstpageConfigService,
    private dialog: Dialog,
    private parentInjector: Injector,
    private iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}settings.svg`, 'settings')?.subscribe();
  }

  openConfig(): void {
    this.dialog.open(FirstpageConfigDialogComponent, {
      width: '600px',
      maxHeight: '80vh',
      panelClass: 'zx-dialog',
    });
  }

  private buildModules(config: FirstpageConfig): ModuleEntry[] {
    return config.modules
      .filter(m => m.enabled)
      .sort((a, b) => a.order - b.order)
      .map(moduleConfig => ({
        config: moduleConfig,
        component: MODULE_COMPONENTS[moduleConfig.type],
        injector: Injector.create({
          providers: [{provide: MODULE_SETTINGS, useValue: moduleConfig.settings}],
          parent: this.parentInjector,
        }),
      }));
  }
}
