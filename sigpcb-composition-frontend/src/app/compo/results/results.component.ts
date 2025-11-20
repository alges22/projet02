import { Component, Input } from '@angular/core';
import { Router } from '@angular/router';
import { catchError } from 'rxjs';
import { ProfileData } from 'src/app/core/interfaces/profiles';
import { QuestionResult } from 'src/app/core/interfaces/question';
import { AlertService } from 'src/app/core/services/alert.service';
import { QuestionService } from 'src/app/core/services/question.service';
import { StateService } from 'src/app/core/services/state.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';

@Component({
  selector: 'app-results',
  templateUrl: './results.component.html',
  styleUrls: ['./results.component.scss'],
})
export class ResultsComponent {
  private timer: any;
  time = 60;
  @Input() profileData!: ProfileData;
  constructor(
    private router: Router,
    private questionService: QuestionService,
    private alertService: AlertService,
    private stateService: StateService
  ) {}

  result: QuestionResult | null | 'no-result' = null;
  questions: any[] = [];
  ngOnInit(): void {
    this._getQuestions(() => {
      this._results(() => {
        this.startTimer();
        this.answersMaps();
      });
    });
  }
  startTimer() {
    if (this.result && this.result !== 'no-result') {
      this.timer = setInterval(() => {
        this.time--;
        if (this.time <= 0) {
          this.logout();
          this.stopTimer();
        }
      }, 1000);
    }
  }

  stopTimer() {
    clearInterval(this.timer);
  }
  ngOnDestroy(): void {
    this.stopTimer();
  }

  ngAfterViewInit(): void {
    this.stateService.removeUnloadWarning();
  }

  isChecked(responseId: number, answers: number[]): boolean {
    return answers.includes(responseId);
  }

  isCorrect(qId: number) {
    if (this.result && this.result != 'no-result') {
      const qcm = this.result.answers.find((ans) => {
        return ans.questionId === qId;
      });

      if (qcm) {
        return qcm.is_correct;
      } else {
        return false;
      }
    }
    return false;
  }

  getAnswers(qId: number) {
    if (this.result && this.result !== 'no-result') {
      const qcm = this.result.answers.find((ans) => {
        return ans.questionId === qId;
      });

      if (qcm) {
        return qcm.answers;
      } else {
        return [];
      }
    }
    return [];
  }

  private answersMaps() {
    if (this.result == 'no-result') {
      return;
    }
    const answers = this.result?.answers as any[];
    this.questions = this.questions.map((qs: any) => {
      const answer = answers.find((ns) => ns.questionId === qs.id);
      const correctes = answer.correctes.map((id: any) => {
        return qs.responses.find((resp: any) => resp.id === id);
      });
      qs.correctes = correctes;
      return qs;
    }) as any;
  }

  private _results(call: CallableFunction) {
    //Lorsque le résultat n'était pas encore chargé on le charge
    if (!this.result) {
      this.questionService
        .questionAnswers()
        .pipe(
          catchError((e: any) => {
            const error = e.error as ServerResponseType;
            let message = error.message || 'Un problème inatttendu est survenu';
            this.alertService.alert(message, 'danger');
            return [];
          })
        )
        .subscribe((response) => {
          this.result = response.data;
          if (typeof this.result === 'string') {
            this.router.navigate(['/thanks']);
            return;
          }
          call();
        });
    }
  }

  private _getQuestions(call: CallableFunction) {
    if (!this.questions.length) {
      this.questionService
        .get()
        .pipe(
          catchError((e: any) => {
            const error = e.error as ServerResponseType;
            let message = error.message || 'Un problème inatttendu est survenu';
            this.alertService.alert(message, 'danger');
            return [];
          })
        )
        .subscribe((response) => {
          this.questions = response.data.questions;
          call();
        });
    }
  }

  logout() {
    localStorage.clear();
    window.location.href = '/';
  }
}
