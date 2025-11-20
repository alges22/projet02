import { TestBed } from '@angular/core/testing';

import { ArrondissementService } from './arrondissement.service';

describe('ArrondissementService', () => {
  let service: ArrondissementService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ArrondissementService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
