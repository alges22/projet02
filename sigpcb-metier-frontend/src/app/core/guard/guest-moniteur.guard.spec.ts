import { TestBed } from '@angular/core/testing';

import { GuestMoniteurGuard } from './guest-moniteur.guard';

describe('GuestMoniteurGuard', () => {
  let guard: GuestMoniteurGuard;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    guard = TestBed.inject(GuestMoniteurGuard);
  });

  it('should be created', () => {
    expect(guard).toBeTruthy();
  });
});
