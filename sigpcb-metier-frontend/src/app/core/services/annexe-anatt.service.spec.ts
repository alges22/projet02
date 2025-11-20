import { TestBed } from '@angular/core/testing';

import { AnnexeAnattService } from './annexe-anatt.service';

describe('AnnexeAnattService', () => {
  let service: AnnexeAnattService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AnnexeAnattService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
