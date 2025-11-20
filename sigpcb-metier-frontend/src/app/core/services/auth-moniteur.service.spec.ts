import { TestBed } from '@angular/core/testing';

import { AuthMoniteurService } from './auth-moniteur.service';

describe('AuthMoniteurService', () => {
  let service: AuthMoniteurService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AuthMoniteurService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
