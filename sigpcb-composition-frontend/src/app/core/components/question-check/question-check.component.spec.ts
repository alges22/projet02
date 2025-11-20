import { ComponentFixture, TestBed } from '@angular/core/testing';

import { QuestionCheckComponent } from './question-check.component';

describe('QuestionCheckComponent', () => {
  let component: QuestionCheckComponent;
  let fixture: ComponentFixture<QuestionCheckComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ QuestionCheckComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(QuestionCheckComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
