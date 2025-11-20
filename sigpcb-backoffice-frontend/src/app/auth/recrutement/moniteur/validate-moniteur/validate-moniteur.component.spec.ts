import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ValidateMoniteurComponent } from './validate-moniteur.component';

describe('ValidateMoniteurComponent', () => {
  let component: ValidateMoniteurComponent;
  let fixture: ComponentFixture<ValidateMoniteurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ValidateMoniteurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ValidateMoniteurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
