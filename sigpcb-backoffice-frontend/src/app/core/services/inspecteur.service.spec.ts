import { TestBed } from '@angular/core/testing';

import { InspecteurService } from './inspecteur.service';

describe('InspecteurService', () => {
  let service: InspecteurService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(InspecteurService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
