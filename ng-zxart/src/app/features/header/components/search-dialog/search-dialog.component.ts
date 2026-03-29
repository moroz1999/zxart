import {
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ElementRef,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {DialogRef} from '@angular/cdk/dialog';
import {Subject, Subscription} from 'rxjs';
import {debounceTime, distinctUntilChanged, switchMap} from 'rxjs/operators';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {SearchService} from '../../services/search.service';
import {SearchResultGroup} from '../../models/search-result.dto';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {BackendLinksService} from '../../services/backend-links.service';
import {environment} from '../../../../../environments/environment';

const MIN_QUERY_LENGTH = 2;
const ICONS = ['person', 'list', 'videogame-asset', 'image', 'music-note'];

@Component({
  selector: 'zx-search-dialog',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxSkeletonComponent,
    ZxButtonControlsComponent,
  ],
  templateUrl: './search-dialog.component.html',
  styleUrls: ['./search-dialog.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SearchDialogComponent implements OnInit, OnDestroy {
  @ViewChild('input', {static: true}) inputRef!: ElementRef<HTMLInputElement>;

  query = '';
  groups: SearchResultGroup[] = [];
  loading = false;
  searched = false;
  searchUrl: string | null = null;
  focusedIndex = -1;

  private readonly querySubject = new Subject<string>();
  private subscription = new Subscription();

  constructor(
    private dialogRef: DialogRef,
    private searchService: SearchService,
    private iconReg: SvgIconRegistryService,
    private cdr: ChangeDetectorRef,
    private backendLinksService: BackendLinksService,
    private el: ElementRef<HTMLElement>,
  ) {}

  ngOnInit(): void {
    ICONS.forEach(name => this.iconReg.loadSvg(`${environment.svgUrl}${name}.svg`, name)?.subscribe());
    this.inputRef.nativeElement.focus();
    this.subscription.add(
      this.backendLinksService.links$.subscribe(links => {
        this.searchUrl = links.searchUrl;
        this.cdr.markForCheck();
      }),
    );

    this.subscription.add(
      this.querySubject.pipe(
        debounceTime(400),
        distinctUntilChanged(),
        switchMap(q => {
          if (q.length < MIN_QUERY_LENGTH) {
            this.groups = [];
            this.loading = false;
            this.searched = false;
            this.cdr.markForCheck();
            return [];
          }
          this.loading = true;
          this.searched = false;
          this.cdr.markForCheck();
          return this.searchService.search(q);
        }),
      ).subscribe(groups => {
        this.groups = groups;
        this.loading = false;
        this.searched = true;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }

  onQueryChange(value: string): void {
    this.query = value;
    this.focusedIndex = -1;
    this.querySubject.next(value);
  }

  onKeyDown(event: KeyboardEvent): void {
    const total = this.groups.reduce((sum, g) => sum + g.items.length, 0);
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      if (total === 0) return;
      event.preventDefault();
      if (event.key === 'ArrowDown') {
        this.focusedIndex = (this.focusedIndex + 1) % total;
      } else {
        this.focusedIndex = this.focusedIndex <= 0 ? total - 1 : this.focusedIndex - 1;
      }
      setTimeout(() => this.scrollFocusedIntoView());
    } else if (event.key === 'Enter') {
      event.preventDefault();
      if (this.focusedIndex >= 0) {
        const flat = this.groups.flatMap(g => g.items);
        const item = flat[this.focusedIndex];
        if (item?.url) {
          window.location.href = item.url;
        }
      } else {
        const url = this.searchAllUrl();
        if (url) {
          window.location.href = url;
        }
      }
    }
  }

  private scrollFocusedIntoView(): void {
    const focused = this.el.nativeElement.querySelector<HTMLElement>('.sd-result-item--focused');
    focused?.scrollIntoView({block: 'nearest'});
  }

  getItemFlatIndex(groupIdx: number, itemIdx: number): number {
    let offset = 0;
    for (let i = 0; i < groupIdx; i++) {
      offset += this.groups[i].items.length;
    }
    return offset + itemIdx;
  }

  close(): void {
    this.dialogRef.close();
  }

  searchAllUrl(): string | null {
    if (!this.searchUrl || !this.query.trim()) {
      return null;
    }
    return `${this.searchUrl}phrase:${this.query.trim()}/`;
  }
}
