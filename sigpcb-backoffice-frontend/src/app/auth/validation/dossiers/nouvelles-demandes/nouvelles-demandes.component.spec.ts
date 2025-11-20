import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NouvellesDemandesComponent } from './nouvelles-demandes.component';

describe('NouvellesDemandesComponent', () => {
  let component: NouvellesDemandesComponent;
  let fixture: ComponentFixture<NouvellesDemandesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ NouvellesDemandesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(NouvellesDemandesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
