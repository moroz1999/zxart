import {Component, EventEmitter, Input, OnInit, Output, ViewChild} from '@angular/core';
import {TagsSearchService} from '../../services/tags-search.service';
import {Tag} from '../../models/tag';
import {Subject} from 'rxjs';
import {MatAutocomplete, MatAutocompleteSelectedEvent, MatOption,} from '@angular/material/autocomplete';
import {MatChip, MatChipSet} from '@angular/material/chips';
import {MatIcon} from '@angular/material/icon';
import {AsyncPipe, NgForOf} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslatePipe} from '@ngx-translate/core';
import {ZxInputComponent} from '../../ui/zx-input/zx-input.component';

@Component({
    selector: 'app-tags-selector',
    templateUrl: './tags-selector.component.html',
    styleUrls: ['./tags-selector.component.scss'],
    standalone: true,
    imports: [
        MatAutocomplete,
        MatOption,
        MatChip,
        MatChipSet,
        MatIcon,
        NgForOf,
        FormsModule,
        AsyncPipe,
        TranslatePipe,
        ZxInputComponent,
    ],
})
export class TagsSelectorComponent implements OnInit {
    tagText = '';
    timeout: number = 0;
    @Input() tagsSelector: Array<Tag> = [];
    foundTags = new Subject<Tag[]>();
    @Output() tagsSelected = new EventEmitter<Array<Tag>>();
    @ViewChild('zxInput') zxInput?: ZxInputComponent;

    constructor(
        private tagsSearch: TagsSearchService,
    ) {
    }

    change(): void {
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
        if (this.tagText.length > 2) {
            this.timeout = setTimeout(() => this.tagsSearch.search(this.tagText).subscribe(
                (tags: Array<Tag>) => this.foundTags.next(tags),
            ), 300);
        }
    }

    remove(tag: Tag) {
        this.tagsSelector.splice(this.tagsSelector.indexOf(tag), 1);
        this.tagsSelected.emit(this.tagsSelector);
    }

    selected(event: MatAutocompleteSelectedEvent) {
        this.tagsSelector.push(event.option.value);
        this.tagsSelected.emit(this.tagsSelector);
        this.tagText = '';
        this.zxInput?.clear();
    }

    ngOnInit() {
    }
}
