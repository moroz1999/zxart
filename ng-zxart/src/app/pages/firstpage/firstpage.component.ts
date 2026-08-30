import {ChangeDetectionStrategy, Component, Injector, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Dialog} from '@angular/cdk/dialog';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../environments/environment';
import {TranslateModule} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {FirstpageConfigService} from '../../features/firstpage/services/firstpage-config.service';
import {FirstpageConfig, ModuleConfig, ModuleType} from '../../features/firstpage/models/firstpage-config';
import {MODULE_COMPONENTS} from '../../features/firstpage/services/module-registry';
import {MODULE_SETTINGS} from '../../features/firstpage/models/module-settings.token';
import {
  FirstpageConfigDialogComponent
} from '../../features/firstpage/components/firstpage-config-dialog/firstpage-config-dialog.component';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
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
    ZxButtonComponent,
    HeadingDirective,
    PictureGalleryHostComponent,
    ZxPageLayoutComponent,
  ],
  templateUrl: './firstpage.component.html',
  styleUrls: ['./firstpage.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FirstpageComponent implements OnInit {
  /**
   * `ngComponentOutlet` recreates its component whenever the injector identity
   * changes, so a module keeps the injector it was built with until its own
   * settings change. Together with `trackByType` a config emission leaves the
   * untouched modules mounted instead of refetching the whole page.
   */
  private readonly injectors = new Map<ModuleType, {settingsKey: string; injector: Injector}>();

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

  trackByType(_index: number, entry: ModuleEntry): ModuleType {
    return entry.config.type;
  }

  openConfig(): void {
    this.dialog.open(FirstpageConfigDialogComponent, {
      width: '600px',
      height: '80vh',
      panelClass: 'zx-dialog',
      backdropClass: 'zx-dialog-backdrop',
    });
  }

  private buildModules(config: FirstpageConfig): ModuleEntry[] {
    return config.modules
      .filter(m => m.enabled)
      .sort((a, b) => a.order - b.order)
      .map(moduleConfig => ({
        config: moduleConfig,
        component: MODULE_COMPONENTS[moduleConfig.type],
        injector: this.resolveInjector(moduleConfig),
      }));
  }

  private resolveInjector(moduleConfig: ModuleConfig): Injector {
    const settingsKey = JSON.stringify(moduleConfig.settings);
    const cached = this.injectors.get(moduleConfig.type);
    if (cached && cached.settingsKey === settingsKey) {
      return cached.injector;
    }

    const injector = Injector.create({
      providers: [{provide: MODULE_SETTINGS, useValue: moduleConfig.settings}],
      parent: this.parentInjector,
    });
    this.injectors.set(moduleConfig.type, {settingsKey, injector});
    return injector;
  }
}
