import { TestBed } from '@angular/core/testing';

import { AgrementService } from './agrement.service';

describe('AgrementService', () => {
  let service: AgrementService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AgrementService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
