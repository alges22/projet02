import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ValidateExaminateurComponent } from './validate-examinateur.component';

describe('ValidateExaminateurComponent', () => {
  let component: ValidateExaminateurComponent;
  let fixture: ComponentFixture<ValidateExaminateurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ValidateExaminateurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ValidateExaminateurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
