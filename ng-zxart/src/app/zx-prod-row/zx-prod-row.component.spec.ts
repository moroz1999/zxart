import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ZxProdRowComponent } from './zx-prod-row.component';

describe('ZxProdRowComponent', () => {
  let component: ZxProdRowComponent;
  let fixture: ComponentFixture<ZxProdRowComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ZxProdRowComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(ZxProdRowComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
