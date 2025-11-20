import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SignatureTopbarComponent } from './signature-topbar.component';

describe('SignatureTopbarComponent', () => {
  let component: SignatureTopbarComponent;
  let fixture: ComponentFixture<SignatureTopbarComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SignatureTopbarComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SignatureTopbarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
