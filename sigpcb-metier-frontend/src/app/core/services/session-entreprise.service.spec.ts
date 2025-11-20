import { TestBed } from '@angular/core/testing';

import { SessionEntrepriseService } from './session-entreprise.service';

describe('SessionEntrepriseService', () => {
  let service: SessionEntrepriseService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(SessionEntrepriseService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
