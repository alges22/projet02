import { TestBed } from '@angular/core/testing';

import { RecrutementMoniteurService } from './recrutement-moniteur.service';

describe('RecrutementMoniteurService', () => {
  let service: RecrutementMoniteurService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(RecrutementMoniteurService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
