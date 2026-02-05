import {Component, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonModule} from '@angular/material/button';
import {MatDialog, MatDialogModule} from '@angular/material/dialog';
import {SettingsDialogComponent} from '../settings-dialog/settings-dialog.component';
import {ThemeService} from '../../services/theme.service';

@Component({
  selector: 'app-settings-trigger',
  standalone: true,
  imports: [
    CommonModule,
    MatIconModule,
    MatButtonModule,
    MatDialogModule
  ],
  templateUrl: './settings-trigger.component.html',
  styleUrls: ['./settings-trigger.component.scss']
})
export class SettingsTriggerComponent implements OnInit {
  constructor(
    private dialog: MatDialog,
    private themeService: ThemeService
  ) {}

  ngOnInit(): void {
    this.themeService.initialize();
  }

  openSettings(): void {
    this.dialog.open(SettingsDialogComponent, {
      panelClass: 'zx-dialog'
    });
  }
}
