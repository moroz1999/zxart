import {Component, HostBinding, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonModule} from '@angular/material/button';
import {animate, state, style, transition, trigger} from '@angular/animations';
import {SettingsPanelComponent} from '../settings-panel/settings-panel.component';
import {ThemeService} from '../../services/theme.service';

@Component({
  selector: 'app-settings-trigger',
  standalone: true,
  imports: [
    CommonModule,
    MatIconModule,
    MatButtonModule,
    SettingsPanelComponent
  ],
  templateUrl: './settings-trigger.component.html',
  styleUrls: ['./settings-trigger.component.scss'],
  animations: [
    trigger('slidePanel', [
      state('closed', style({
        transform: 'translateX(100%)'
      })),
      state('open', style({
        transform: 'translateX(0)'
      })),
      transition('closed <=> open', [
        animate('200ms ease-in-out')
      ])
    ]),
    trigger('fadeOverlay', [
      state('closed', style({
        opacity: 0,
        visibility: 'hidden'
      })),
      state('open', style({
        opacity: 1,
        visibility: 'visible'
      })),
      transition('closed <=> open', [
        animate('200ms ease-in-out')
      ])
    ])
  ]
})
export class SettingsTriggerComponent implements OnInit {
  isOpen = false;

  constructor(private themeService: ThemeService) {}

  @HostBinding('class.is-open')
  get hostIsOpen(): boolean {
    return this.isOpen;
  }

  ngOnInit(): void {
    this.themeService.initialize();
  }

  toggle(): void {
    this.isOpen = !this.isOpen;
  }

  close(): void {
    this.isOpen = false;
  }

  onOverlayClick(event: MouseEvent): void {
    if ((event.target as HTMLElement).classList.contains('settings-overlay')) {
      this.close();
    }
  }
}
