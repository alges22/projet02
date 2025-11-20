import { TestBed } from '@angular/core/testing';

import { DemandeAgrementService } from './demande-agrement.service';

describe('DemandeAgrementService', () => {
  let service: DemandeAgrementService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(DemandeAgrementService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
