import { TestBed } from '@angular/core/testing';

import { CodeInspectionService } from './code-inspection.service';

describe('CodeInspectionService', () => {
  let service: CodeInspectionService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(CodeInspectionService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
