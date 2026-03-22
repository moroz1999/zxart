import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnInit, Output, ViewChild} from '@angular/core';
import {NgForOf, NgIf} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslatePipe} from '@ngx-translate/core';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {TagsSearchService} from '../../services/tags-search.service';
import {Tag} from '../../models/tag';
import {ZxInputComponent} from '../../ui/zx-input/zx-input.component';
import {environment} from '../../../../environments/environment';

@Component({
    selector: 'zx-tags-selector',
    templateUrl: './tags-selector.component.html',
    styleUrls: ['./tags-selector.component.scss'],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        NgForOf,
        NgIf,
        FormsModule,
        TranslatePipe,
        CdkConnectedOverlay,
        CdkOverlayOrigin,
        SvgIconComponent,
        ZxInputComponent,
    ],
})
export class TagsSelectorComponent implements OnInit {
    tagText = '';
    private timeout: number = 0;
    @Input() tagsSelector: Array<Tag> = [];
    foundTags: Tag[] = [];
    dropdownOpen = false;
    @Output() tagsSelected = new EventEmitter<Array<Tag>>();
    @ViewChild('zxInput') zxInput?: ZxInputComponent;

    readonly positions: ConnectedPosition[] = [
        {originX: 'start', originY: 'bottom', overlayX: 'start', overlayY: 'top', offsetY: 4},
    ];

    constructor(
        private tagsSearch: TagsSearchService,
        private iconReg: SvgIconRegistryService,
    ) {}

    ngOnInit(): void {
        this.iconReg.loadSvg(`${environment.svgUrl}cancel.svg`, 'cancel')?.subscribe();
    }

    change(): void {
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
        if (this.tagText.length > 2) {
            this.timeout = setTimeout(() => this.tagsSearch.search(this.tagText).subscribe(
                (tags: Array<Tag>) => {
                    this.foundTags = tags;
                    this.dropdownOpen = tags.length > 0;
                },
            ), 300);
        } else {
            this.foundTags = [];
            this.dropdownOpen = false;
        }
    }

    remove(tag: Tag): void {
        this.tagsSelector = this.tagsSelector.filter(t => t !== tag);
        this.tagsSelected.emit(this.tagsSelector);
    }

    selectTag(tag: Tag): void {
        this.tagsSelector = [...this.tagsSelector, tag];
        this.tagsSelected.emit(this.tagsSelector);
        this.tagText = '';
        this.dropdownOpen = false;
        this.foundTags = [];
        this.zxInput?.clear();
    }

    closeDropdown(): void {
        this.dropdownOpen = false;
    }
}
