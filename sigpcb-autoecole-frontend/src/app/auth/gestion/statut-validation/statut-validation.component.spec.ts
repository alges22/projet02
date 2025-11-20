import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StatutValidationComponent } from './statut-validation.component';

describe('StatutValidationComponent', () => {
  let component: StatutValidationComponent;
  let fixture: ComponentFixture<StatutValidationComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ StatutValidationComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StatutValidationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
