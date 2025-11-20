import { TestBed } from '@angular/core/testing';

import { AffiliationService } from './affiliation.service';

describe('AffiliationService', () => {
  let service: AffiliationService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AffiliationService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
