import { TestBed } from '@angular/core/testing';

import { BipService } from './bip.service';

describe('BipService', () => {
  let service: BipService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(BipService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
