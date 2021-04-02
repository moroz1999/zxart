import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TagsSelectorComponent } from './tags-selector.component';

describe('TagsSelectorComponent', () => {
  let component: TagsSelectorComponent;
  let fixture: ComponentFixture<TagsSelectorComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ TagsSelectorComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(TagsSelectorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
