import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AgreementValidesComponent } from './agreement-valides.component';

describe('AgreementValidesComponent', () => {
  let component: AgreementValidesComponent;
  let fixture: ComponentFixture<AgreementValidesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AgreementValidesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AgreementValidesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
