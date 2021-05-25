import {NestedTreeControl} from '@angular/cdk/tree';
import {Component, EventEmitter, Input, OnChanges, OnInit, Output, SimpleChanges} from '@angular/core';
import {MatTreeNestedDataSource} from '@angular/material/tree';
import {CategoriesSelectorDto, CategorySelectorDto} from '../../../categories-selector-dto';

@Component({
  selector: 'app-categories-tree-selector',
  templateUrl: './categories-tree-selector.component.html',
  styleUrls: ['./categories-tree-selector.component.scss'],
})
export class CategoriesTreeSelectorComponent implements OnInit, OnChanges {

  @Input() selectorData!: CategoriesSelectorDto;
  treeControl = new NestedTreeControl<CategorySelectorDto>(node => node.children);
  dataSource = new MatTreeNestedDataSource<CategorySelectorDto>();
  @Output() categoryChanged = new EventEmitter<number>();

  ngOnInit(): void {
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes.selectorData) {
      this.dataSource.data = this.selectorData;

      const expander = (node: CategorySelectorDto) => {
        node.selected ? this.treeControl.expand(node) : this.treeControl.collapse(node);
        if (node.children) {
          for (const child of node.children) {
            expander(child);
          }
        }
      };
      for (const selector of this.selectorData) {
        expander(selector);
      }
    }
  }

  // isExpandable = (node: CategorySelectorDto) => node.selected;
  hasChild = (_: number, node: CategorySelectorDto) => !!node.children && node.children.length > 0;

  nodeClicked(event: Event, nodeId: number): void {
    event.preventDefault();
    this.categoryChanged.emit(nodeId);
  }
}
