import { TestBed } from '@angular/core/testing';

import { RecrutementExaminateurService } from './recrutement-examinateur.service';

describe('RecrutementExaminateurService', () => {
  let service: RecrutementExaminateurService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(RecrutementExaminateurService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
