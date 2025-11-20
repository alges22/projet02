import {
  Component,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import { Question } from '../../interfaces/question';
import { Reponse } from '../../interfaces/reponse';
import { QuestionService } from '../../services/question.service';

@Component({
  selector: 'app-question-check',
  templateUrl: './question-check.component.html',
  styleUrls: ['./question-check.component.scss'],
})
export class QuestionCheckComponent implements OnInit, OnChanges {
  @Input('question') question: Question | null = null;
  isMultiple = false;
  @Input() answers: number[] = [];
  responses: Reponse[] = [];

  constructor(private questionService: QuestionService) {}
  ngOnInit(): void {
    if (this.question) {
      this.responses = this.question.responses;
      this.isMultiple = this.question.isMultiple;
      this.synchronize();
    }
  }
  select(id: number) {
    if (!this.isMultiple) {
      // Si c'était déjà dedans on le retire
      if (this.answers.includes(id)) {
        this.answers = [];
      } else {
        this.answers = [];
        this.answers.push(id);
      }
    } else {
      if (!this.answers.includes(id)) {
        this.answers.push(id);
      } else {
        this.answers = this.answers.filter((a) => a !== id);
      }
    }

    this.synchronize();
  }

  responseChecked(id: number) {
    return this.answers.includes(id);
  }
  /**
   */
  synchronize() {
    if (this.question) {
      //Enregistre les informations automatiquement
      this.questionService.setAnswers(this.question.id, this.answers);
    }
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['question']) {
      const newQuestion: Question | null = changes['question'].currentValue;

      if (newQuestion) {
        this.question = newQuestion;
        this.responses = newQuestion.responses;
        this.isMultiple = newQuestion.isMultiple;
        this.synchronize();
      }
    }
  }
}
