import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {FileSelectorItem} from '../../models/form-data-response';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxCloseButtonComponent} from '../zx-close-button/zx-close-button.component';

/** A request to move an existing file one step within its list. */
export interface FileMove {
  fileId: number;
  direction: 'left' | 'right';
}

/**
 * Manager for a multi-file selector (screenshots, inlays, maps, …). Existing
 * files render as uniform thumbnails (or a filename row for non-images) with an
 * overlay delete, and — when `reorderable` — ◀/▶ controls that emit `(move)` for
 * the host to run the live reorder. New files are staged via the picker button
 * and read from {@link stagedFiles} on submit; deletion is emitted so the host
 * removes the file live.
 */
@Component({
  selector: 'zx-file-selector',
  standalone: true,
  imports: [CommonModule, TranslateModule, SvgIconComponent, ZxButtonComponent, ZxCloseButtonComponent],
  templateUrl: './zx-file-selector.component.html',
  styleUrl: './zx-file-selector.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFileSelectorComponent {
  @Input() files: FileSelectorItem[] = [];
  @Input() accept = '';
  /** Show per-file ◀/▶ controls that emit `(move)`. */
  @Input() reorderable = false;
  /** Emits the id of an existing file the user removed (host deletes it live). */
  @Output() readonly removeExisting = new EventEmitter<number>();
  /** Emits the staged new files whenever the selection changes. */
  @Output() readonly filesChange = new EventEmitter<File[]>();
  /** Emits a reorder request for an existing file (host runs it live). */
  @Output() readonly move = new EventEmitter<FileMove>();

  /** New files staged for upload. */
  stagedFiles: File[] = [];
  removedIds = new Set<number>();

  constructor(private readonly iconReg: SvgIconRegistryService) {
    this.iconReg.loadSvg(`${environment.svgUrl}skip-previous.svg`, 'skip-previous')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}skip-next.svg`, 'skip-next')?.subscribe();
  }

  get visibleFiles(): FileSelectorItem[] {
    return this.files.filter(file => !this.removedIds.has(file.id));
  }

  onFilesSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.stagedFiles = Array.from(input.files ?? []);
    this.filesChange.emit(this.stagedFiles);
  }

  onRemoveExisting(id: number): void {
    this.removedIds.add(id);
    this.removeExisting.emit(id);
  }

  onMove(file: FileSelectorItem, direction: 'left' | 'right'): void {
    this.move.emit({fileId: file.id, direction});
  }

  isFirst(file: FileSelectorItem): boolean {
    return this.visibleFiles[0]?.id === file.id;
  }

  isLast(file: FileSelectorItem): boolean {
    const visible = this.visibleFiles;
    return visible[visible.length - 1]?.id === file.id;
  }

  trackById(_index: number, file: FileSelectorItem): number {
    return file.id;
  }

  trackByName(_index: number, file: File): string {
    return file.name;
  }
}
