import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SuivreEserviceComponent } from './suivre-eservice.component';

describe('SuivreEserviceComponent', () => {
  let component: SuivreEserviceComponent;
  let fixture: ComponentFixture<SuivreEserviceComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SuivreEserviceComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SuivreEserviceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
