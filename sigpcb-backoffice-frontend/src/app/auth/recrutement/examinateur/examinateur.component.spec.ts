import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ExaminateurComponent } from './examinateur.component';

describe('ExaminateurComponent', () => {
  let component: ExaminateurComponent;
  let fixture: ComponentFixture<ExaminateurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ExaminateurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ExaminateurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
