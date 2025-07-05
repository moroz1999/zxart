import {ComponentFixture, TestBed} from '@angular/core/testing';

import {ZxProdsCategoryComponent} from './zx-prods-category.component';

describe('ZxProdsListComponent', () => {
    let component: ZxProdsCategoryComponent;
    let fixture: ComponentFixture<ZxProdsCategoryComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            declarations: [ZxProdsCategoryComponent],
        })
            .compileComponents();
    });

    beforeEach(() => {
        fixture = TestBed.createComponent(ZxProdsCategoryComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });
});
