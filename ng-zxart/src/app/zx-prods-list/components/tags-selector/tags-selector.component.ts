import {Component} from '@angular/core';
import {TagsSearchService} from '../../../shared/services/tags-search.service';

@Component({
  selector: 'app-tags-selector',
  templateUrl: './tags-selector.component.html',
  styleUrls: ['./tags-selector.component.scss'],
})
export class TagsSelectorComponent {
  tagText = '';

  constructor(
    private tagsSearch: TagsSearchService,
  ) {
  }

  change(): void {
    if (this.tagText.length > 2){
      this.tagsSearch.search(this.tagText).subscribe();
    }
  }
}
