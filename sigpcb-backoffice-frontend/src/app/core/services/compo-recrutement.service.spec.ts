import { TestBed } from '@angular/core/testing';

import { CompoRecrutementService } from './compo-recrutement.service';

describe('CompoRecrutementService', () => {
  let service: CompoRecrutementService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(CompoRecrutementService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
