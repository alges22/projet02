import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GrDemandesComponent } from './gr-demandes.component';

describe('GrDemandesComponent', () => {
  let component: GrDemandesComponent;
  let fixture: ComponentFixture<GrDemandesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ GrDemandesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(GrDemandesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
