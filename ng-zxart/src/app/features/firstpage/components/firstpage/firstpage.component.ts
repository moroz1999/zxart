import {Component, Injector, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MatIconModule} from '@angular/material/icon';
import {MatDialog} from '@angular/material/dialog';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {FirstpageConfigService} from '../../services/firstpage-config.service';
import {FirstpageConfig, ModuleConfig} from '../../models/firstpage-config';
import {MODULE_COMPONENTS} from '../../services/module-registry';
import {MODULE_SETTINGS} from '../../models/module-settings.token';
import {FirstpageConfigDialogComponent} from '../firstpage-config-dialog/firstpage-config-dialog.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxHeading1Directive} from '../../../../shared/directives/typography/typography.directives';
import {
  PictureGalleryHostComponent
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';

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
    MatIconModule,
    TranslateModule,
    ZxStackComponent,
    ZxButtonComponent,
    ZxHeading1Directive,
    PictureGalleryHostComponent,
  ],
  templateUrl: './firstpage.component.html',
  styleUrls: ['./firstpage.component.scss']
})
export class FirstpageComponent implements OnInit, OnDestroy {
  modules: ModuleEntry[] = [];
  private configSub?: Subscription;

  constructor(
    private configService: FirstpageConfigService,
    private dialog: MatDialog,
    private parentInjector: Injector,
  ) {}

  ngOnInit(): void {
    this.configService.reload();
    this.configSub = this.configService.getConfig().subscribe(config => {
      this.buildModules(config);
    });
  }

  ngOnDestroy(): void {
    this.configSub?.unsubscribe();
  }

  openConfig(): void {
    this.dialog.open(FirstpageConfigDialogComponent, {
      width: '600px',
      maxHeight: '80vh',
      panelClass: 'zx-dialog',
    });
  }

  private buildModules(config: FirstpageConfig): void {
    this.modules = config.modules
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
