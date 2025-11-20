import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GrDemandesRejectedComponent } from './gr-demandes-rejected.component';

describe('GrDemandesRejectedComponent', () => {
  let component: GrDemandesRejectedComponent;
  let fixture: ComponentFixture<GrDemandesRejectedComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ GrDemandesRejectedComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(GrDemandesRejectedComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
