import { TestBed } from '@angular/core/testing';

import { SalleCompoService } from './salle-compo.service';

describe('SalleCompoService', () => {
  let service: SalleCompoService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(SalleCompoService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
