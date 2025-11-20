import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AttestationSuccessComponent } from './attestation-success.component';

describe('AttestationSuccessComponent', () => {
  let component: AttestationSuccessComponent;
  let fixture: ComponentFixture<AttestationSuccessComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AttestationSuccessComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AttestationSuccessComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
