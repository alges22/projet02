import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BlueAsideEntrepriseComponent } from './blue-aside-entreprise.component';

describe('BlueAsideEntrepriseComponent', () => {
  let component: BlueAsideEntrepriseComponent;
  let fixture: ComponentFixture<BlueAsideEntrepriseComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ BlueAsideEntrepriseComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BlueAsideEntrepriseComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
