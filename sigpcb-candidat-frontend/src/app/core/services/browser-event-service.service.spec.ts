import { TestBed } from '@angular/core/testing';

import { BrowserEventServiceService } from './browser-event-service.service';

describe('BrowserEventServiceService', () => {
  let service: BrowserEventServiceService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(BrowserEventServiceService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
