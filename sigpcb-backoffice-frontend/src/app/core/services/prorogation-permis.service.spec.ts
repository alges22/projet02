import { TestBed } from '@angular/core/testing';

import { ProrogationPermisService } from './prorogation-permis.service';

describe('ProrogationPermisService', () => {
  let service: ProrogationPermisService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ProrogationPermisService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
