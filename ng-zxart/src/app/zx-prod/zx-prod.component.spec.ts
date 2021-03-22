import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ZxProdComponent } from './zx-prod.component';

describe('ZxProdComponent', () => {
  let component: ZxProdComponent;
  let fixture: ComponentFixture<ZxProdComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ZxProdComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(ZxProdComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
