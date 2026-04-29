import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output, ViewChild} from '@angular/core';
import {AsyncPipe, NgForOf, NgIf} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslatePipe} from '@ngx-translate/core';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {TagChipItem} from '../../models/tag-chip-item';
import {TagsSearchService} from '../../services/tags-search.service';
import {Tag} from '../../models/tag';
import {ZxInputComponent} from '../../ui/zx-input/zx-input.component';
import {ZxTagsChipsComponent} from '../../ui/zx-tags-chips/zx-tags-chips.component';
import {BehaviorSubject, combineLatest, Observable, of} from 'rxjs';
import {catchError, debounceTime, distinctUntilChanged, map, shareReplay, startWith, switchMap} from 'rxjs/operators';

interface TagsSelectorVm {
    readonly foundTags: Tag[];
    readonly dropdownOpen: boolean;
}

@Component({
    selector: 'zx-tags-selector',
    templateUrl: './tags-selector.component.html',
    styleUrls: ['./tags-selector.component.scss'],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        NgForOf,
        NgIf,
        AsyncPipe,
        FormsModule,
        TranslatePipe,
        CdkConnectedOverlay,
        CdkOverlayOrigin,
        ZxInputComponent,
        ZxTagsChipsComponent,
    ],
})
export class TagsSelectorComponent {
    tagText = '';
    @Input() tagsSelector: Array<Tag> = [];
    @Output() tagsSelected = new EventEmitter<Array<Tag>>();
    @ViewChild('zxInput') zxInput?: ZxInputComponent;
    private readonly searchQuery$ = new BehaviorSubject<string>('');
    private readonly dropdownDismissed$ = new BehaviorSubject<boolean>(false);

    readonly positions: ConnectedPosition[] = [
        {originX: 'start', originY: 'bottom', overlayX: 'start', overlayY: 'top', offsetY: 4},
    ];

    readonly foundTags$: Observable<Tag[]> = this.searchQuery$.pipe(
        debounceTime(300),
        distinctUntilChanged(),
        switchMap(query => query.length > 2
            ? this.tagsSearch.search(query).pipe(catchError(() => of([] as Tag[])))
            : of([] as Tag[])),
        startWith([] as Tag[]),
        shareReplay({bufferSize: 1, refCount: true}),
    );

    readonly vm$: Observable<TagsSelectorVm> = combineLatest([
        this.searchQuery$,
        this.dropdownDismissed$,
        this.foundTags$,
    ]).pipe(
        map(([query, dropdownDismissed, foundTags]) => ({
            foundTags,
            dropdownOpen: query.length > 2 && foundTags.length > 0 && dropdownDismissed === false,
        })),
    );

    constructor(private tagsSearch: TagsSearchService) {}

    change(value: string): void {
        this.tagText = value;
        this.dropdownDismissed$.next(false);
        this.searchQuery$.next(value);
    }

    remove(tag: TagChipItem): void {
        this.tagsSelector = this.tagsSelector.filter(t => t !== tag);
        this.tagsSelected.emit(this.tagsSelector);
    }

    selectTag(tag: Tag): void {
        this.tagsSelector = [...this.tagsSelector, tag];
        this.tagsSelected.emit(this.tagsSelector);
        this.tagText = '';
        this.dropdownDismissed$.next(false);
        this.searchQuery$.next('');
        this.zxInput?.clear();
    }

    closeDropdown(): void {
        this.dropdownDismissed$.next(true);
    }
}
