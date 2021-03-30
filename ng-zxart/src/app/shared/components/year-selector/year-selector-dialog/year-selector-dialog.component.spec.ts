import { ComponentFixture, TestBed } from '@angular/core/testing';

import { YearSelectorDialogComponent } from './year-selector-dialog.component';

describe('YearSelectorDialogComponent', () => {
  let component: YearSelectorDialogComponent;
  let fixture: ComponentFixture<YearSelectorDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ YearSelectorDialogComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(YearSelectorDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
