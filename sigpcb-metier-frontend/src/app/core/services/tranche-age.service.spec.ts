import { TestBed } from '@angular/core/testing';

import { TrancheAgeService } from './tranche-age.service';

describe('TrancheAgeService', () => {
  let service: TrancheAgeService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(TrancheAgeService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
