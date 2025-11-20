import { TestBed } from '@angular/core/testing';

import { MonitorGuardGuard } from './monitor-guard.guard';

describe('MonitorGuardGuard', () => {
  let guard: MonitorGuardGuard;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    guard = TestBed.inject(MonitorGuardGuard);
  });

  it('should be created', () => {
    expect(guard).toBeTruthy();
  });
});
