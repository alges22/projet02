import { TestBed } from '@angular/core/testing';

import { DuplicataRemplacementService } from './duplicata-remplacement.service';

describe('DuplicataRemplacementService', () => {
  let service: DuplicataRemplacementService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(DuplicataRemplacementService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
