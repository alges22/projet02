import { TestBed } from '@angular/core/testing';

import { AeService } from './ae.service';

describe('AeService', () => {
  let service: AeService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AeService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
