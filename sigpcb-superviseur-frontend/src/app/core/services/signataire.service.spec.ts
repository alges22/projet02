import { TestBed } from '@angular/core/testing';

import { SignataireService } from './signataire.service';

describe('SignataireService', () => {
  let service: SignataireService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(SignataireService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
