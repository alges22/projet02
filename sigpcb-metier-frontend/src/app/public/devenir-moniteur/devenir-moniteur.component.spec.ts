import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DevenirMoniteurComponent } from './devenir-moniteur.component';

describe('DevenirMoniteurComponent', () => {
  let component: DevenirMoniteurComponent;
  let fixture: ComponentFixture<DevenirMoniteurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DevenirMoniteurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DevenirMoniteurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
