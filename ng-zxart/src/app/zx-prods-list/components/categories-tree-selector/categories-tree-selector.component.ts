import {NestedTreeControl} from '@angular/cdk/tree';
import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {MatTreeNestedDataSource} from '@angular/material/tree';
import {CategoriesSelectorDto, CategorySelectorDto} from '../../../categories-selector-dto';

@Component({
  selector: 'app-categories-tree-selector',
  templateUrl: './categories-tree-selector.component.html',
  styleUrls: ['./categories-tree-selector.component.scss'],
})
export class CategoriesTreeSelectorComponent implements OnInit {

  @Input() selectorData!: CategoriesSelectorDto;
  treeControl = new NestedTreeControl<CategorySelectorDto>(node => node.children);
  dataSource = new MatTreeNestedDataSource<CategorySelectorDto>();
  @Output() categoryChanged = new EventEmitter<number>();

  ngOnInit(): void {
    this.dataSource.data = this.selectorData;
  }

  hasChild = (_: number, node: CategorySelectorDto) => !!node.children && node.children.length > 0;

  nodeClicked(event: Event, nodeId: number): void {
    event.preventDefault();
    this.categoryChanged.emit(nodeId);
  }
}
