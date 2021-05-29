import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DialogSelectorComponent } from './dialog-selector.component';

describe('YearSelectorComponent', () => {
  let component: DialogSelectorComponent;
  let fixture: ComponentFixture<DialogSelectorComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DialogSelectorComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(DialogSelectorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
