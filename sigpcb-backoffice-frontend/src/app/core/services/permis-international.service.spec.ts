import { TestBed } from '@angular/core/testing';

import { PermisInternationalService } from './permis-international.service';

describe('PermisInternationalService', () => {
  let service: PermisInternationalService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(PermisInternationalService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
