import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AbsenceConduiteComponent } from './absence-conduite.component';

describe('AbsenceConduiteComponent', () => {
  let component: AbsenceConduiteComponent;
  let fixture: ComponentFixture<AbsenceConduiteComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AbsenceConduiteComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AbsenceConduiteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
