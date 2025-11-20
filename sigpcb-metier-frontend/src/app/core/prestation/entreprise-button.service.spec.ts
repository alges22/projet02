import { TestBed } from '@angular/core/testing';

import { EntrepriseButtonService } from './entreprise-button.service';

describe('EntrepriseButtonService', () => {
  let service: EntrepriseButtonService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(EntrepriseButtonService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
