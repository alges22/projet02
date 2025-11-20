import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ValidateProrogationComponent } from './validate-prorogation.component';

describe('ValidateProrogationComponent', () => {
  let component: ValidateProrogationComponent;
  let fixture: ComponentFixture<ValidateProrogationComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ValidateProrogationComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ValidateProrogationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
