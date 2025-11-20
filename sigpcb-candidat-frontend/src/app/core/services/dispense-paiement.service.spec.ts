import { TestBed } from '@angular/core/testing';

import { DispensePaiementService } from './dispense-paiement.service';

describe('DispensePaiementService', () => {
  let service: DispensePaiementService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(DispensePaiementService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
