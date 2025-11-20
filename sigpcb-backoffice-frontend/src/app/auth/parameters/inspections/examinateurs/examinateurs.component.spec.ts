import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ExaminateursComponent } from './examinateurs.component';

describe('ExaminateursComponent', () => {
  let component: ExaminateursComponent;
  let fixture: ComponentFixture<ExaminateursComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ExaminateursComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ExaminateursComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
