import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ConsentCookieComponent } from './consent-cookie.component';

describe('ConsentCookieComponent', () => {
  let component: ConsentCookieComponent;
  let fixture: ComponentFixture<ConsentCookieComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ConsentCookieComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ConsentCookieComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
