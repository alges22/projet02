import { AfterViewInit, Component, Input } from '@angular/core';
import { Router } from '@angular/router';
import { ProfileData } from 'src/app/core/interfaces/profiles';
import { QuestionService } from 'src/app/core/services/question.service';
import { StateService } from 'src/app/core/services/state.service';

@Component({
  selector: 'app-start-compo',
  templateUrl: './start-compo.component.html',
  styleUrls: ['./start-compo.component.scss'],
})
export class StartCompoComponent {
  time = 10;
  otime = 10;
  timer: any;
  questionMessage = null as string | null;
  onChecking = false;
  needToPlay = false;
  startCompo = false;
  @Input() profileData!: ProfileData;

  data: {
    toCompose: number;
    time: number;
    ready: boolean;
  } = {
    toCompose: 0,
    time: 0,
    ready: false,
  };
  constructor(
    private questionService: QuestionService,
    private stateService: StateService
  ) {}

  ngOnInit(): void {
    this.stateService.onNetwork(
      () => {
        this.stopTimer();
        this.stateService.$alert.alert('Vous avez perdu le réseau', 'danger');
      },
      () => {
        this.start(true);
      }
    );
  }

  stopTimer() {
    clearInterval(this.timer);
    this.timer = null;
  }

  private startChrono() {
    this.startCompo = true;
  }

  start(fromView = false) {
    if (fromView) {
      this.stateService.enterFullScreen();
    }
    this.needToPlay = false;

    this.timer = setInterval(() => {
      this.fetch();

      // Vérifiez si toCompose a une valeur et arrêtez le timer
      if (this.data.ready) {
        this.stopTimer();
        return;
      }
    }, 5000);
  }

  goto() {
    this.stateService.changePage('questions');
  }

  onChronoErrors() {
    this.needToPlay = true;
  }
  ngOnDestroy() {
    this.stopTimer();
  }
  convertSeconds() {
    let seconds = 25 * 60;
    if (this.data) {
      seconds = this.data.time > 0 ? this.data.time : this.data.toCompose * 60;
      seconds += this.data.toCompose * 30;
    }
    return Math.floor((seconds % 3600) / 60);
  }

  private fetch() {
    if (this.data.ready) {
      return;
    }
    this.questionService.startCompo().subscribe((response) => {
      this.data = response.data;
      if (this.data.ready) {
        this.startChrono();
      }
    });
  }
}
