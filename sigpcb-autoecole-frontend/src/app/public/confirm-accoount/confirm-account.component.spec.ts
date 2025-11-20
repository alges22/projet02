import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ConfirmAccoountComponent } from './confirm-account.component';

describe('ConfirmAccoountComponent', () => {
  let component: ConfirmAccoountComponent;
  let fixture: ComponentFixture<ConfirmAccoountComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ConfirmAccoountComponent],
    }).compileComponents();

    fixture = TestBed.createComponent(ConfirmAccoountComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
