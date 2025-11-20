import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ANouvelleDemandesComponent } from './a-nouvelle-demandes.component';

describe('ANouvelleDemandesComponent', () => {
  let component: ANouvelleDemandesComponent;
  let fixture: ComponentFixture<ANouvelleDemandesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ANouvelleDemandesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ANouvelleDemandesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
