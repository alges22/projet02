import { TestBed } from '@angular/core/testing';

import { DispensePaymentService } from './dispense-payment.service';

describe('DispensePaymentService', () => {
  let service: DispensePaymentService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(DispensePaymentService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
