import { TestBed } from '@angular/core/testing';

import { AccessAdminGuard } from './access-admin.guard';

describe('AccessAdminGuard', () => {
  let guard: AccessAdminGuard;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    guard = TestBed.inject(AccessAdminGuard);
  });

  it('should be created', () => {
    expect(guard).toBeTruthy();
  });
});
