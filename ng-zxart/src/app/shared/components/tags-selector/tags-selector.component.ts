import {Component, ContentChild, EventEmitter, Input, OnInit, Output, ViewChild} from '@angular/core';
import {TagsSearchService} from '../../services/tags-search.service';
import {Tag} from '../../models/tag';
import {Subject} from 'rxjs';
import {MatAutocompleteSelectedEvent} from '@angular/material/autocomplete';

@Component({
  selector: 'app-tags-selector',
  templateUrl: './tags-selector.component.html',
  styleUrls: ['./tags-selector.component.scss'],
})
export class TagsSelectorComponent implements OnInit {
  tagText = '';
  timeout: number = 0;
  @Input() tagsSelector: Array<Tag> = [];
  foundTags = new Subject<Tag[]>();
  @Output() tagsSelected = new EventEmitter<Array<Tag>>();

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
  }

  ngOnInit() {
  }
}
