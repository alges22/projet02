import { TestBed } from '@angular/core/testing';

import { ActeSigneService } from './acte-signe.service';

describe('ActeSigneService', () => {
  let service: ActeSigneService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ActeSigneService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
