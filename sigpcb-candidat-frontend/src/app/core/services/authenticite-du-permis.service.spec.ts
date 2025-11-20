import { TestBed } from '@angular/core/testing';

import { AuthenticiteDuPermisService } from './authenticite-du-permis.service';

describe('AuthenticiteDuPermisService', () => {
  let service: AuthenticiteDuPermisService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AuthenticiteDuPermisService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
