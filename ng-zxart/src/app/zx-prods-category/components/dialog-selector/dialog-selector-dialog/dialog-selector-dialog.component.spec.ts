import {ComponentFixture, TestBed} from '@angular/core/testing';

import {DialogSelectorDialogComponent} from './dialog-selector-dialog.component';

describe('YearSelectorDialogComponent', () => {
    let component: DialogSelectorDialogComponent;
    let fixture: ComponentFixture<DialogSelectorDialogComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            declarations: [DialogSelectorDialogComponent],
        })
            .compileComponents();
    });

    beforeEach(() => {
        fixture = TestBed.createComponent(DialogSelectorDialogComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });
});
