import { TestBed } from '@angular/core/testing';

import { UniteAdminService } from './unite-admin.service';

describe('UniteAdminService', () => {
  let service: UniteAdminService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(UniteAdminService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
