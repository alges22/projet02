import { TestBed } from '@angular/core/testing';

import { EserviceParcoursService } from './eservice-parcours.service';

describe('EserviceParcoursService', () => {
  let service: EserviceParcoursService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(EserviceParcoursService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
