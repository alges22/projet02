import { TestBed } from '@angular/core/testing';

import { PreventRefreshingGuard } from './prevent-refreshing.guard';

describe('PreventRefreshingGuard', () => {
  let guard: PreventRefreshingGuard;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    guard = TestBed.inject(PreventRefreshingGuard);
  });

  it('should be created', () => {
    expect(guard).toBeTruthy();
  });
});
