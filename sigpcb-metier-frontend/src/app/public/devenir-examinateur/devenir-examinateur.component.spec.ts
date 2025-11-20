import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DevenirExaminateurComponent } from './devenir-examinateur.component';

describe('DevenirExaminateurComponent', () => {
  let component: DevenirExaminateurComponent;
  let fixture: ComponentFixture<DevenirExaminateurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DevenirExaminateurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DevenirExaminateurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
