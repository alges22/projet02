import { TestBed } from '@angular/core/testing';

import { CategoryPermisService } from './category-permis.service';

describe('CategoryPermisService', () => {
  let service: CategoryPermisService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(CategoryPermisService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
