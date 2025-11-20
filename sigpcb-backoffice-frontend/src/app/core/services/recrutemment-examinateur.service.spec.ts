import { TestBed } from '@angular/core/testing';

import { RecrutemmentExaminateurService } from './recrutemment-examinateur.service';

describe('RecrutemmentExaminateurService', () => {
  let service: RecrutemmentExaminateurService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(RecrutemmentExaminateurService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
