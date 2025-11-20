import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ValidateEchangePermisComponent } from './validate-echange-permis.component';

describe('ValidateEchangePermisComponent', () => {
  let component: ValidateEchangePermisComponent;
  let fixture: ComponentFixture<ValidateEchangePermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ValidateEchangePermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ValidateEchangePermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
