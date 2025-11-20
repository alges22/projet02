import { TestBed } from '@angular/core/testing';

import { EchangePermisService } from './echange-permis.service';

describe('EchangePermisService', () => {
  let service: EchangePermisService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(EchangePermisService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
