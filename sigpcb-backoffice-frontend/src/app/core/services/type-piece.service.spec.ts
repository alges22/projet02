import { TestBed } from '@angular/core/testing';

import { TypePieceService } from './type-piece.service';

describe('TypePieceService', () => {
  let service: TypePieceService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(TypePieceService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
