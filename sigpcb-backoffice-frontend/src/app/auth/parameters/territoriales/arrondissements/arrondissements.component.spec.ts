import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ArrondissementsComponent } from './arrondissements.component';

describe('ArrondissementsComponent', () => {
  let component: ArrondissementsComponent;
  let fixture: ComponentFixture<ArrondissementsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ArrondissementsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ArrondissementsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
