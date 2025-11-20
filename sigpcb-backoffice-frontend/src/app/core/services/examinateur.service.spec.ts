import { TestBed } from '@angular/core/testing';

import { ExaminateurService } from './examinateur.service';

describe('ExaminateurService', () => {
  let service: ExaminateurService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ExaminateurService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
