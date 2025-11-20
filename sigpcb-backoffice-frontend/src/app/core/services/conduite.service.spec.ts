import { TestBed } from '@angular/core/testing';

import { ConduiteService } from './conduite.service';

describe('ConduiteService', () => {
  let service: ConduiteService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ConduiteService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
