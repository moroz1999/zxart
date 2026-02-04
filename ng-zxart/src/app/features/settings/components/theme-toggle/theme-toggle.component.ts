import {Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MatButtonToggleModule} from '@angular/material/button-toggle';
import {TranslateModule} from '@ngx-translate/core';
import {ThemeService} from '../../services/theme.service';
import {Theme} from '../../models/preference.dto';

@Component({
  selector: 'app-theme-toggle',
  standalone: true,
  imports: [
    CommonModule,
    MatButtonToggleModule,
    TranslateModule
  ],
  templateUrl: './theme-toggle.component.html',
  styleUrls: ['./theme-toggle.component.scss']
})
export class ThemeToggleComponent {
  constructor(private themeService: ThemeService) {}

  get currentTheme(): Theme {
    return this.themeService.currentTheme;
  }

  onThemeChange(theme: Theme): void {
    this.themeService.setTheme(theme);
  }
}
