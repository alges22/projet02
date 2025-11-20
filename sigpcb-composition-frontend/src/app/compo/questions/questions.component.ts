import {
  AfterViewInit,
  Component,
  ElementRef,
  Input,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import { ProfileData } from 'src/app/core/interfaces/profiles';
import { AudioService } from 'src/app/core/services/audio.service';
import { QuestionService } from 'src/app/core/services/question.service';
import { StateService } from 'src/app/core/services/state.service';
import { CurrentQuestion } from 'src/app/core/types/server-response.type';

@Component({
  selector: 'app-questions',
  templateUrl: './questions.component.html',
  styleUrls: ['./questions.component.scss'],
})
export class QuestionsComponent implements OnInit, AfterViewInit, OnDestroy {
  @Input() profileData!: ProfileData;

  @ViewChild('chrono') chronoElement: ElementRef | null = null;
  answers: number[] = [];
  @Input('question-index') questionIndex: number | null = 0;

  private repeatCount = 1;

  /**
   * Le temps restant
   * timeDecrement = 0;
   */

  timer: any;

  @ViewChild('videoPlayer', { static: false })
  videoElement: ElementRef<HTMLVideoElement> | null = null;
  video: HTMLVideoElement | null = null;
  showVideo = false;
  start_chrono = false;
  chronoCount = 5;

  posting = false;
  data: CurrentQuestion | null = null;
  constructor(
    private questionService: QuestionService,
    private audio: AudioService,
    private stateService: StateService
  ) {}

  ngOnInit(): void {
    this.onNetwork();
    this.boot();
  }

  private boot() {
    this.chronoCount = 5;
    // Stop the timer for the current question
    this.stopTimer();

    // Mets à jour la
    this.answers = [];
    this._getQuestion();
  }

  /**
   * Le chrono est initalisié une fois la page est prête
   */
  ngAfterViewInit(): void {
    this.prepareNext();
  }

  private setCurrentData(data: CurrentQuestion) {
    this.data = data;
  }
  stopTimer() {
    clearInterval(this.timer);
  }

  /**
   * Lorsque le component n'est plus rendu il faut nettoyer la mémoire
   */
  ngOnDestroy(): void {
    this.stopTimer();
    this.timer = null;
  }
  /**
   * Mets à jours la réponse sur serveur
   */
  private sendQuestionToServer() {
    if (this.currentQuestion) {
      this.posting = true;
      const questionAnswer = this.questionService.findOne(
        this.currentQuestion?.id
      );

      if (questionAnswer) {
        //Envoie les réponses au serveur
        this.questionService
          .syncToServer(questionAnswer.questionId, questionAnswer.responses)
          .subscribe((response) => {
            this.data = response.data.currentQuestion;

            this.posting = false;
            if (this.data) {
              if (this.data.completed) {
                if (this.audio) {
                  this.audio.stop();
                }
                this.stateService.changePage('thanks');
                return;
              }

              if (this.data.question) {
                this.boot();
              }
            }
          });
      }
    }
  }

  /**
   * Démarre la question
   * @param fromView
   */
  start(fromView = false) {
    if (fromView) {
      this.stateService.enterFullScreen();
      this.stateService.setupUnloadWarning();
    }
    this.profileData.lostConnection = false;
    if (this.data) {
      if (this.data.question) {
        this.start_chrono = false;
        this.playAudio(this.data.question.audio);
      }
    }
  }

  private playAudio(src: string) {
    const play = () => {
      this.audio.play(src, (err) => {
        this.profileData.lostConnection = true;
      });
      this.audio.ended(() => {
        if (this.repeatCount > 0) {
          play();
          this.repeatCount--;
        } else {
          this.repeatCount = 1;
          this.startChrono(); // Démarre le chrono après deux lectures
        }
      });
    };

    //Lancer l'audio après vidéo au cas ou l'illustration est une vidéo
    if (this.data) {
      if (this.data.question?.type_illustration == 'video') {
        this.showVideo = true;
        this.startVideo(play);
      } else {
        play();
      }
    }
  }
  private startChrono() {
    this.start_chrono = true;
  }

  /**
   * Lorsque le chrono est terminé
   * On enregistre les données avant de passer au suivant
   */
  chronoEnded() {
    this.showVideo = false;
    if (!this.posting) {
      this.sendQuestionToServer();
    }
  }
  private startVideo(callable: CallableFunction) {
    if (!this.video) {
      this.prepareNext();
    }

    // Accéder à l'élément vidéo et le lire
    if (this.video && this.data?.question) {
      this.video.src = this.data.question.illustration;
      this.video.load();
      this.video?.play();

      //Quand la vidéo est terminée
      this.video?.addEventListener('ended', () => {
        callable();
      });
    }
  }

  prepareNext() {
    if (this.videoElement) {
      this.video = this.videoElement.nativeElement;
      if (this.video) {
        this.video.preload = 'auto';
        this.video.volume = 0;
      }
    }
  }

  /**
   * Récupère la question actuelle à composer
   * Si la question est présente
   */
  private _getQuestion() {
    this.questionService.getCurrent().subscribe((response) => {
      this.setCurrentData(response.data);
      if (this.data?.completed) {
        if (this.audio) {
          this.audio.stop();
        }
        this.stateService.changePage('thanks');
        return;
      }
      this.start();
    });
  }

  private onNetwork() {
    this.stateService.onNetwork(
      (err) => {
        this.stopTimer();
        this.audio.pause();
        this.start_chrono = false;
        this.stateService.$alert.alert('Vous avez perdu la connexion');
      },
      () => {
        this.profileData.lostConnection = true;
        this.stateService.$alert.close();
      }
    );
  }
  get currentIndex() {
    return this.data?.index ?? 0;
  }

  get currentQuestion() {
    return this.data?.question ?? null;
  }
}
