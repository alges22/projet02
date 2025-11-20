import { TestBed } from '@angular/core/testing';

import { AecoleService } from './aecole.service';

describe('AecoleService', () => {
  let service: AecoleService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AecoleService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
