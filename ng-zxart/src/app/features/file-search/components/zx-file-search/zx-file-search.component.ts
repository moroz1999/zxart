import {ChangeDetectionStrategy, Component, OnDestroy, OnInit, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {of, Subject, Subscription} from 'rxjs';
import {debounceTime, distinctUntilChanged, switchMap, tap} from 'rxjs/operators';
import {FileSearchService} from '../../services/file-search.service';
import {FileSearchResult} from '../../models/file-search-result';
import {isSpaUrl} from '../../../../shared/utils/spa-url';
const MIN_QUERY_LENGTH = 2;

/**
 * Searches the file registry by file name or md5 and links each match to the
 * containing entity (prod/release).
 */
@Component({
  selector: 'zx-file-search',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, TranslateModule],
  templateUrl: './zx-file-search.component.html',
  styleUrls: ['./zx-file-search.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFileSearchComponent implements OnInit, OnDestroy {
  query = '';
  loading = signal(false);
  searched = signal(false);
  results = signal<FileSearchResult[]>([]);

  private readonly querySubject = new Subject<string>();
  private subscription?: Subscription;

  constructor(private readonly fileSearchService: FileSearchService) {}

  ngOnInit(): void {
    this.subscription = this.querySubject.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      tap(query => this.loading.set(query.trim().length >= MIN_QUERY_LENGTH)),
      switchMap(query => query.trim().length < MIN_QUERY_LENGTH
        ? of([] as FileSearchResult[])
        : this.fileSearchService.search(query.trim())),
    ).subscribe(items => {
      this.results.set(items);
      this.loading.set(false);
      this.searched.set(this.query.trim().length >= MIN_QUERY_LENGTH);
    });
  }

  ngOnDestroy(): void {
    this.subscription?.unsubscribe();
  }

  onInput(value: string): void {
    this.query = value;
    this.querySubject.next(value);
  }

  isInternal(url: string): boolean {
    return isSpaUrl(url);
  }
}
