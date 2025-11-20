import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EditInscriptionComponent } from './edit-inscription.component';

describe('EditInscriptionComponent', () => {
  let component: EditInscriptionComponent;
  let fixture: ComponentFixture<EditInscriptionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EditInscriptionComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EditInscriptionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
