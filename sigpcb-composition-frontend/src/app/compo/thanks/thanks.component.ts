import { AfterViewInit, Component, Input, OnInit } from '@angular/core';

import { ProfileData } from 'src/app/core/interfaces/profiles';
import { QuestionService } from 'src/app/core/services/question.service';
import { StateService } from 'src/app/core/services/state.service';

@Component({
  selector: 'app-thanks',
  templateUrl: './thanks.component.html',
  styleUrls: ['./thanks.component.scss'],
})
export class ThanksComponent implements OnInit, AfterViewInit {
  resultaTimer: any;
  timeout = 30000;
  constructor(
    private stateService: StateService,
    private questionService: QuestionService
  ) {}
  success = false;
  loaded = false;
  @Input() profileData!: ProfileData;
  data: {
    correct_count: number;
    success: boolean;
    count: number;
  } | null = null;
  ngOnInit(): void {
    this.getResults();
  }

  ngAfterViewInit(): void {
    this.stateService.removeUnloadWarning();
  }
  onPress() {
    this.stateService.changePage('results');
  }

  private getResults() {
    if (this.data) {
      return;
    }
    this.resultaTimer = setInterval(() => {
      if (!this.data) {
        this.questionService.thanks().subscribe((response) => {
          const result = response.data;
          if (typeof result != 'string') {
            this.data = result;
            clearInterval(this.resultaTimer);
            this._startCountdown();
          } else {
            this.data = null;
            this._getQuestions();
          }
        });
      } else {
        this._startCountdown();
      }
    }, 5000);
  }

  ngOnDestroy(): void {
    clearInterval(this.resultaTimer);
  }

  private _startCountdown() {
    setTimeout(() => {
      this.onPress();
    }, this.timeout);
  }

  private _getQuestions() {
    this.questionService.get().subscribe((response) => {
      const questions = response.data as any;
      if (questions) {
        const responses = questions.responses;
        const toCompose = questions.toCompose;

        if (Array.isArray(responses)) {
          if (responses.length != toCompose) {
            clearInterval(this.resultaTimer);
            this.stateService.changePage('questions');
          }
          [];
        }
      }
    });
  }
}
