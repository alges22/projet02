import { TestBed } from '@angular/core/testing';

import { AutoecoleService } from './autoecole.service';

describe('AutoecoleService', () => {
  let service: AutoecoleService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AutoecoleService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
