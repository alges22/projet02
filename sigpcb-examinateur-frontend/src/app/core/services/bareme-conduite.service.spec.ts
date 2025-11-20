import { TestBed } from '@angular/core/testing';

import { BaremeConduiteService } from './bareme-conduite.service';

describe('BaremeConduiteService', () => {
  let service: BaremeConduiteService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(BaremeConduiteService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
