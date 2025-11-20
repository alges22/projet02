import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AgreementRejetesComponent } from './agreement-rejetes.component';

describe('AgreementRejetesComponent', () => {
  let component: AgreementRejetesComponent;
  let fixture: ComponentFixture<AgreementRejetesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AgreementRejetesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AgreementRejetesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
