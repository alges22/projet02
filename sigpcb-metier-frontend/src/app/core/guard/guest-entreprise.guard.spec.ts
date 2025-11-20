import { TestBed } from '@angular/core/testing';

import { GuestEntrepriseGuard } from './guest-entreprise.guard';

describe('GuestEntrepriseGuard', () => {
  let guard: GuestEntrepriseGuard;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    guard = TestBed.inject(GuestEntrepriseGuard);
  });

  it('should be created', () => {
    expect(guard).toBeTruthy();
  });
});
