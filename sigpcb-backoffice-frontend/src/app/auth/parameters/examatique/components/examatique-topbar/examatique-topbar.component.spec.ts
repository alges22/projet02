import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ExamatiqueTopbarComponent } from './examatique-topbar.component';

describe('ExamatiqueTopbarComponent', () => {
  let component: ExamatiqueTopbarComponent;
  let fixture: ComponentFixture<ExamatiqueTopbarComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ExamatiqueTopbarComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ExamatiqueTopbarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
