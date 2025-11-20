import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ExamatiqueComponent } from './examatique.component';

describe('ExamatiqueComponent', () => {
  let component: ExamatiqueComponent;
  let fixture: ComponentFixture<ExamatiqueComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ExamatiqueComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ExamatiqueComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
