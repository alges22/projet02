import {
  Component,
  EventEmitter,
  Injectable,
  Input,
  Output,
  SimpleChanges,
} from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { Question } from 'src/app/core/interfaces/question';
import { ChapitreService } from 'src/app/core/services/chapitre.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LangueService } from 'src/app/core/services/langue.service';
import { QuestionService } from 'src/app/core/services/question.service';
import { ReponseService } from 'src/app/core/services/reponse.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { emitAlertEvent } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-question-form',
  templateUrl: './question-form.component.html',
  styleUrls: ['./question-form.component.scss'],
})
export class QuestionFormComponent {
  constructor(
    private chapitreService: ChapitreService,
    private langueService: LangueService,
    private questionService: QuestionService,
    private reponseService: ReponseService,
    private errorHandler: HttpErrorHandlerService,
    private route: ActivatedRoute,
    private regenereQuestion: RegenereQuestionComposition
  ) {}

  questions: any[] = [];

  question = {} as any;

  question_title = '';

  onLoading = false;

  onLoadingAduio = false;

  accordionIndex: number | null = null;
  idLangue: number | null = null;
  imageLink: any;
  illustrationType: any;
  /** Ceci permettra de savoir s'il s'agit d'une édition ou pas */
  editPage = false;
  /**
   * Permetra de savoir si une tranche d'âge a été soumise à la modification
   */
  editTranche = false;

  addNewTrancheAge = true;

  pageIndex: number | null = 0;

  /** Si les formulaires ont subt de modification */
  private basicFormHasChange = false;

  private validiteFormHasChange = false;

  @Output() questionAdded: EventEmitter<Question> =
    new EventEmitter<Question>();

  assetLink = environment.endpoints.asset;
  chapitres: any[] = [];
  languesQuestionForm: any[] = [];
  reponses: any[] = [];
  audios: any[] = [];
  audio: any;
  time: any;
  illustration: any;
  question_answer = {} as any;
  question_answers: any[] = [];
  onDeleting = false;
  idQuestion: number | null = null;
  onLoadingReponse = false;
  role_formulaire: any;
  audio_formulaire = 'Ajouter un audio';
  modalId = 'add-audio';

  ngOnInit(): void {
    this.getChapitres();
    this.getReponses();
    this.getAudios();

    this.route.params.subscribe((params) => {
      const id = params['id'];
      this.role_formulaire = "Ajout d'une question";
      if (id) {
        this.getReponsesByQuestionId(id);
        this.role_formulaire = 'Edition de la question ';
      }
    });
  }

  selectedPage(idpage: number) {
    if (idpage === 0) {
      this.pageIndex = 0;
    } else if (idpage === 1) {
      this.pageIndex = 1;
      this.getLangues();
    } else if (idpage === 2) {
      this.pageIndex = 2;
      this.getReponsesByQuestion();
    }
  }

  getChapitres() {
    this.errorHandler.startLoader();
    this.chapitreService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.chapitres = response.data;
        }
        this.errorHandler.stopLoader();
      });
  }

  getLangues() {
    this.languesQuestionForm = [];
    this.errorHandler.startLoader();
    this.langueService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          console.log(this.audios);
          response.data.forEach((langue: any) => {
            const correspondingAudio = this.audios.find((audio: any) => {
              return (
                this.question.id == audio.question_id &&
                langue.id == audio.langue_id
              );
            });
            if (correspondingAudio) {
              langue.audio = correspondingAudio.audio;
              langue.idAudio = correspondingAudio.id;
            }
            this.languesQuestionForm.push(langue);
          });
        }
        this.errorHandler.stopLoader();
      });
  }

  getReponses() {
    this.errorHandler.startLoader();
    this.reponseService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.reponses = response.data;
        }
        this.errorHandler.stopLoader();
      });
  }

  getAudios() {
    this.errorHandler.startLoader();
    this.questionService
      .getAudio()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.audios = response.data;
        }
        this.errorHandler.stopLoader();
      });
  }

  onFileSelected(event: any, field: string) {
    if (event.target.files && event.target.files.length) {
      const file = event.target.files[0];
      if (field === 'audio') {
        this.audio = file;
      } else if (field === 'illustration') {
        this.illustration = file;
      }
    }
  }

  onAudioSelected(event: any) {
    const file = event.target.files[0];
    this.audio = file;
    const audio = new Audio();
    audio.addEventListener('loadedmetadata', () => {
      //conversion en minutes
      this.time = audio.duration * 60;
    });
    audio.src = URL.createObjectURL(file);
    audio.load();
  }

  // on check pour l'ajout de reponse pour une question enregistré auparavent
  onSelectQuestionReponse(question_answer: any) {
    question_answer.is_correct = !question_answer.is_correct;
  }

  showReponse(data: any) {
    this.question_answer = data;
    $('#reponse_id').focus();
  }

  onReponse() {
    this.question_answer.is_correct = false;
  }

  saveReponse(event: Event) {
    event.preventDefault();
    if (!this.question_answer.is_correct)
      this.question_answer.is_correct = false;
    var data = {
      question_id: this.question.id,
      reponse_id: this.question_answer.reponse_id,
      is_correct: this.question_answer.is_correct,
    };
    this.onLoadingReponse = true;
    if (this.question_answer.id) {
      this.updateReponse(data);
    } else {
      this.postReponse(data);
    }
  }

  // pour enregistrer une reponse d'une question
  private postReponse(data: any) {
    this.questionService
      .postReponseByQuestion(data)
      .pipe(
        this.errorHandler.handleServerError(
          'question-reponses-form',
          (response: ServerResponseType) => {
            this.onLoadingReponse = false;
          }
        )
      )
      .subscribe((response) => {
        this.onLoadingReponse = false;
        emitAlertEvent(response.message, 'success');
        this.question_answer = {};
        this.getReponsesByQuestionId(this.question.id);
        this.questionAdded.emit();
      });
  }

  private updateReponse(data: any) {
    this.questionService
      .updateReponseByQuestion(data, this.question_answer.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'question-reponses-form',
          (response: ServerResponseType) => {
            this.onLoading = false;
          }
        )
      )
      .subscribe((response) => {
        this.onLoadingReponse = false;
        emitAlertEvent(response.message, 'success');
        this.question_answer = {};
        this.getReponsesByQuestionId(this.question.id);
        this.questionAdded.emit();
      });
  }

  deleteQuestionReponse(id: number) {
    this.onDeleting = true;
    this.questionService
      .deleteReponseByQuestion(id)
      .pipe(
        this.errorHandler.handleServerError(
          'question-reponses-form',
          (response) => {
            this.onLoading = false;
          }
        )
      )
      .subscribe((response) => {
        this.onDeleting = false;
        this.question_answer = {};
        this.getReponsesByQuestionId(this.question.id);
        emitAlertEvent(response.message, 'success');
        this.questionAdded.emit();
      });
  }

  private getReponsesByQuestionId(id: any) {
    this.errorHandler.startLoader();
    this.question_answers = [];
    this.question_answer.is_correct = false;
    this.questionService
      .findById(id)
      .pipe(
        this.errorHandler.handleServerError('questions-form', (response) => {
          // this.setAlert(response.message, 'danger', 'middle');
        })
      )
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.question = response.data;
          this.question_title = response.data.name;
          if (response.data.reponses) {
            response.data.reponses.map((reponse: any) => {
              var answer = {
                id: reponse.id,
                reponse_id: reponse.reponse_id,
                name: this.reponses.find(
                  (item) => item.id === reponse.reponse_id
                )?.name,
                is_correct: reponse.is_correct,
              };
              this.question_answers.push(answer);
              this.question_answers.sort((a, b) =>
                a.name > b.name ? 1 : b.name > a.name ? -1 : 0
              );
            });
          }
        }
        this.errorHandler.stopLoader();
      });
  }

  getReponsesByQuestion() {
    this.errorHandler.startLoader();
    this.question_answer = {};
    this.question_answers = [];
    if (this.question.id) {
      if (this.question.reponses) {
        this.question_answers = [];
        this.question.reponses.map((reponse: any) => {
          var answer = {
            id: reponse.id,
            reponse_id: reponse.reponse_id,
            name: this.reponses.find((item) => item.id === reponse.reponse_id)
              .name,
            is_correct: reponse.is_correct,
          };
          this.question_answers.push(answer);
          this.question_answers.sort((a, b) =>
            a.name > b.name ? 1 : b.name > a.name ? -1 : 0
          );
        });
      }
    }
    this.errorHandler.stopLoader();
  }

  save(event: Event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('name', this.question.name);
    formData.append('chapitre_id', this.question.chapitre_id as any);
    if (this.question.texte) {
      formData.append('texte', this.question.texte);
    }
    if (this.illustration) {
      formData.append('illustration', this.illustration);
      if (this.question.code_illustration) {
        formData.append('code_illustration', this.question.code_illustration);
      }
    }
    this.onLoading = true;
    if (this.question.id) {
      this.update(formData);
    } else {
      this.post(formData);
    }
  }

  addAudio(id: any, action: any) {
    console.log(this.question);
    $('#reset').click();
    this.idLangue = id;
    this.audio = '';
    this.time = '';
    this.openModal('store');
  }

  saveAudio(event: Event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('question_id', this.question.id as any);
    formData.append('langue_id', this.idLangue as any);
    if (this.audio) {
      formData.append('audio', this.audio);
      formData.append('time', this.time);
    } else {
      this.setAlert('Veuillez sélectionner un audio!!!', 'danger', 'middle');
      return;
    }

    this.onLoadingAduio = true;
    this.postAudio(formData);
  }

  private setAlert(
    message: string = '',
    type: AlertType = 'warning',
    position: AlertPosition = 'bottom-right',
    fixed?: boolean
  ) {
    this.errorHandler.emitAlert(message, type, position, fixed);
  }

  private postAudio(data: any) {
    this.questionService
      .postAudio(data)
      .pipe(
        this.errorHandler.handleServerError('questions-form', (response) => {
          this.onLoadingAduio = false;
        })
      )
      .subscribe((response) => {
        emitAlertEvent('Audio ajouté avec succès!', 'success');
        this.onLoadingAduio = false;
        $('#resetAudio').click();
        $(`#${this.modalId}`).modal('hide');
        this.errorHandler.startLoader();
        this.getAudiosPromise().then(() => {
          this.languesQuestionForm = [];
          setTimeout(() => {
            this.getLangues();
            this.errorHandler.stopLoader();
          }, 2000);
        });
      });
  }

  getAudiosPromise() {
    return new Promise<void>((resolve) => {
      this.getAudios();
      resolve();
    });
  }

  deleteAudio(id: number) {
    this.onDeleting = true;
    this.questionService
      .deleteAudio(id)
      .pipe(
        this.errorHandler.handleServerError('audio-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.onDeleting = false;
        this.errorHandler.startLoader();
        this.getAudiosPromise().then(() => {
          this.languesQuestionForm = [];
          setTimeout(() => {
            this.getLangues();
            this.errorHandler.stopLoader();
          }, 2000);
        });
      });
  }

  private post(data: any) {
    this.questionService
      .post(data)
      .pipe(
        this.errorHandler.handleServerError('questions-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        emitAlertEvent('Question ajoutée avec succès!', 'success');
        this.onLoading = false;
        this.question = response.data;
        $('#reset').click();
        this.getReponsesByQuestionId(this.question.id);
        this.regenereQuestion.regenereQuestionComposition();
      });
  }

  // private regenereQuestionComposition() {
  //   this.questionService
  //     .regenere()
  //     .pipe(this.errorHandler.handleServerErrors())
  //     .subscribe((response) => {});
  // }

  private update(data: any) {
    this.questionService
      .update(data, this.question.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'questions-form',
          (response: ServerResponseType) => {
            this.onLoading = false;
            this.setAlert(response.message, 'danger', 'middle');
          }
        )
      )
      .subscribe((response) => {
        emitAlertEvent('Question modifiée avec succès!', 'success');
        this.onLoading = false;
        this.question = response.data;
        $('#reset').click();
        this.getReponsesByQuestionId(this.question.id);
        this.regenereQuestion.regenereQuestionComposition();
      });
  }

  openModal(action: 'store' | 'edit' | 'show', object?: any) {
    this.audio_formulaire = 'Ajouter un audio';
    $(`#${this.modalId}`).modal('show');
  }

  openImageModal(link: any, type: any) {
    this.illustrationType = type;
    this.imageLink = this.assetLink + '/' + link;
    $(`#imageModal`).modal('show');
  }
}
@Injectable()
export class RegenereQuestionComposition {
  constructor(
    private questionService: QuestionService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  public regenereQuestionComposition() {
    this.questionService
      .regenere()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {});
  }
}
