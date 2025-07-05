import {ComponentFixture, TestBed} from '@angular/core/testing';

import {CategoriesTreeSelectorComponent} from './categories-tree-selector.component';

describe('CategoriesTreeSelectorComponent', () => {
    let component: CategoriesTreeSelectorComponent;
    let fixture: ComponentFixture<CategoriesTreeSelectorComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            declarations: [CategoriesTreeSelectorComponent],
        })
            .compileComponents();
    });

    beforeEach(() => {
        fixture = TestBed.createComponent(CategoriesTreeSelectorComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });
});
