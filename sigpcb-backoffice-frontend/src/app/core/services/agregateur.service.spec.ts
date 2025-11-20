import { TestBed } from '@angular/core/testing';

import { AgregateurService } from './agregateur.service';

describe('AgregateurService', () => {
  let service: AgregateurService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AgregateurService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
