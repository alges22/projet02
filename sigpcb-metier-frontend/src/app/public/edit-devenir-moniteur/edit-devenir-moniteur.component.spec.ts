import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EditDevenirMoniteurComponent } from './edit-devenir-moniteur.component';

describe('EditDevenirMoniteurComponent', () => {
  let component: EditDevenirMoniteurComponent;
  let fixture: ComponentFixture<EditDevenirMoniteurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EditDevenirMoniteurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EditDevenirMoniteurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
