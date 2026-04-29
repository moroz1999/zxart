import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {TagChipItem} from '../../models/tag-chip-item';

@Component({
  selector: 'zx-tags-chips',
  standalone: true,
  imports: [
    CommonModule,
    SvgIconComponent,
  ],
  templateUrl: './zx-tags-chips.component.html',
  styleUrl: './zx-tags-chips.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTagsChipsComponent implements OnInit {
  @Input() tags: ReadonlyArray<TagChipItem> = [];
  @Input() removable = false;
  @Input() disabled = false;
  @Input() removeButtonAriaLabel = '';
  @Output() tagRemoved = new EventEmitter<TagChipItem>();

  constructor(private iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    if (this.removable) {
      this.iconReg.loadSvg(`${environment.svgUrl}cancel.svg`, 'cancel')?.subscribe();
    }
  }

  onRemove(tag: TagChipItem): void {
    this.tagRemoved.emit(tag);
  }
}
