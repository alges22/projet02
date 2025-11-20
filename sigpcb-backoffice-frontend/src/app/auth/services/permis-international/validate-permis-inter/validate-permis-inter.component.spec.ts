import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ValidatePermisInterComponent } from './validate-permis-inter.component';

describe('ValidatePermisInterComponent', () => {
  let component: ValidatePermisInterComponent;
  let fixture: ComponentFixture<ValidatePermisInterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ValidatePermisInterComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ValidatePermisInterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
