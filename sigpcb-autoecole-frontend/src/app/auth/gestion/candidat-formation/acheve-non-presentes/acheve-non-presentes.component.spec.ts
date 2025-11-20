import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AcheveNonPresentesComponent } from './acheve-non-presentes.component';

describe('AcheveNonPresentesComponent', () => {
  let component: AcheveNonPresentesComponent;
  let fixture: ComponentFixture<AcheveNonPresentesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AcheveNonPresentesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AcheveNonPresentesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
