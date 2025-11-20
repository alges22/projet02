import { TestBed } from '@angular/core/testing';

import { AuthEntrepriseGuard } from './auth-entreprise.guard';

describe('AuthEntrepriseGuard', () => {
  let guard: AuthEntrepriseGuard;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    guard = TestBed.inject(AuthEntrepriseGuard);
  });

  it('should be created', () => {
    expect(guard).toBeTruthy();
  });
});
