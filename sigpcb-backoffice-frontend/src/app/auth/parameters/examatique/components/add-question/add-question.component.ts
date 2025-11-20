import {
  Component,
  EventEmitter,
  Input,
  Output,
  SimpleChanges,
} from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Question } from 'src/app/core/interfaces/question';
import { TrancheAge } from 'src/app/core/interfaces/tranche-age';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { ChapitreService } from 'src/app/core/services/chapitre.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LangueService } from 'src/app/core/services/langue.service';
import { QuestionService } from 'src/app/core/services/question.service';
import { ReponseService } from 'src/app/core/services/reponse.service';
import { TrancheAgeService } from 'src/app/core/services/tranche-age.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { emitAlertEvent } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-add-question',
  templateUrl: './add-question.component.html',
  styleUrls: ['./add-question.component.scss'],
})
export class AddQuestionComponent {
  constructor(
    private chapitreService: ChapitreService,
    private langueService: LangueService,
    private questionService: QuestionService,
    private reponseService: ReponseService,
    private categoryPermisService: CategoryPermisService,
    private trancheAgeService: TrancheAgeService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  // Ce décorateur permettra que la catégorie peut être récupérée de la liste
  @Input('question') question!: Question;

  // Ce décorateur permettra que la catégorie peut être récupérée de la liste
  @Input('category') category!: CategoryPermis;

  onLoading = false;
  extensions: string[] = [];
  lesExtensions: CategoryPermis[] = [];
  categories: CategoryPermis[] = [];
  tranche: TrancheAge = { age_min: null, age_max: null };
  tranches: TrancheAge[] = [];

  is_extension = false;
  has_tranche = false;

  accordionIndex: number | null = null;

  inputExtension = '';

  tranchesSupprimes: number[] = [];

  newTranches: TrancheAge[] = [];
  /** Ceci permettra de savoir s'il s'agit d'une édition ou pas */
  editPage = false;
  /**
   * Permetra de savoir si une tranche d'âge a été soumise à la modification
   */
  editTranche = false;

  addNewTrancheAge = true;

  /** Si les formulaires ont subt de modification */
  private basicFormHasChange = false;

  private validiteFormHasChange = false;

  @Output() permisSaved: EventEmitter<CategoryPermis> =
    new EventEmitter<CategoryPermis>();

  @Output() questionAdded: EventEmitter<Question> =
    new EventEmitter<Question>();

  assetLink = environment.endpoints.asset;
  chapitres: any[] = [];
  langues: any[] = [];
  reponses: any[] = [];
  audio: any;
  time: any;
  illustration: any;
  question_answer = {} as any;
  question_answers: any[] = [];
  onDeleting = false;
  idQuestion: number | null = null;
  onLoadingReponse = false;

  ngOnInit(): void {
    this.getChapitres();
    this.getLangues();
    this.getReponses();
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
        this.lesExtensions = this.categories.filter((cat) => {
          return !cat.is_extension;
        });
      });
  }

  getChapitres() {
    this.chapitreService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.chapitres = response.data;
        }
      });
  }

  getLangues() {
    this.langueService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.langues = response.data;
        }
      });
  }

  getReponses() {
    this.reponseService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.reponses = response.data;
        }
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
    // this.get();
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

          if (response.data.reponses) {
            response.data.reponses.map((reponse: any) => {
              var answer = {
                id: reponse.id,
                reponse_id: reponse.reponse_id,
                name: this.reponses.find(
                  (item) => item.id === reponse.reponse_id
                ).name,
                is_correct: reponse.is_correct,
              };
              this.question_answers.push(answer);
              this.question_answers.sort((a, b) =>
                a.name > b.name ? 1 : b.name > a.name ? -1 : 0
              );
            });
          }
        }
      });
  }

  save(event: Event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('name', this.question.name);
    formData.append('langue_id', this.question.langue_id as any);
    formData.append('chapitre_id', this.question.chapitre_id as any);
    if (this.question.text) {
      formData.append('text', this.question.text);
    }
    if (this.audio) {
      formData.append('audio', this.audio);
      formData.append('time', this.time);
    }

    if (this.illustration) {
      formData.append('illustration', this.illustration);
    }
    formData.append('question_reponses_groupes', JSON.stringify([]));
    this.onLoading = true;
    if (this.question.id) {
      // this.update(formData);
    } else {
      this.post(formData);
    }
  }

  // private update(data: any) {
  //   this.questionService
  //     .update(data, this.question.id ?? 0)
  //     .pipe(
  //       this.errorHandler.handleServerError(
  //         'questions-form',
  //         (response: ServerResponseType) => {
  //           this.onLoading = false;
  //           this.setAlert(response.message, 'danger', 'middle');
  //         }
  //       )
  //     )
  //     .subscribe((response) => {
  //       this.onLoading = false;
  //       this.setAlert(response.message, 'success');
  //       this.hideModal();
  //       this.audio = '';
  //       this.illustration = '';
  //       $('#reset').click();
  //       this.get();
  //     });
  // }

  private post(data: any) {
    this.questionService
      .post(data)
      .pipe(
        this.errorHandler.handleServerError('questions-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        console.log(response);
        emitAlertEvent('Question ajoutée avec succès!', 'success');
        this.onLoading = false;
        this.question = response.data;
        // this.setAlert(response.message, 'success');
        // this.audio = '';
        // this.illustration = '';
        // this.hideModal();
        $('#reset').click();
        this.getReponsesByQuestionId(this.question.id);
        this.questionAdded.emit();
      });
  }
  // save(event: Event) {
  //   event.preventDefault();
  //   this.onLoading = true;
  //   if (this.category.id) {
  //     this.update();
  //   } else {
  //     this.post();
  //   }
  // }

  // post() {
  //   this.errorHandler.clearServerErrorsMessages('category-permis-basic');
  //   const categoryData = this.prepareData();
  //   categoryData.tranche_age_groupe = this.tranches;

  //   this.categoryPermisService
  //     .post(categoryData)
  //     .pipe(
  //       this.errorHandler.handleServerError(
  //         'category-permis-basic',
  //         (response) => {
  //           this.onLoading = false;
  //         }
  //       )
  //     )
  //     .subscribe((response) => {
  //       this.emitPermisSaved();
  //       emitAlertEvent('Catégorie de permis ajoutée avec succès!', 'success');
  //       this.onLoading = false;
  //     });
  // }
  private update() {
    this.category.status = Boolean(this.category.status);
    this.updateCategory();

    this.updateTranche();

    this.deleteTrancheAge();
  }

  private emitPermisSaved() {
    this.permisSaved.emit(this.category);
  }

  selectAccordion(index: number, event: Event) {
    event.preventDefault();
    //Toggle l'accordion
    if (this.accordionIndex === index) {
      if (this.accordionIndex == null) {
        this.accordionIndex = index;
      } else {
        this.accordionIndex = null;
      }
    } else {
      this.accordionIndex = index;
    }
  }

  isExtensible(target: any) {
    if (target) {
      this.category.is_extension = target.value == '1';
    }
  }

  appendExtension() {}
  isExtension(event: any) {
    this.is_extension = event.target.value == '1';
    this.basicFormHasChange = true;
    this.category.is_extension = true;
  }

  hasTranche(target: any) {
    if (target) {
      this.has_tranche = target.value === '1';
    }
  }

  addTranche(event: any) {
    if (!this.tranches.includes(this.tranche)) {
      if (!this.tranche.age_min || !this.tranche.age_max) {
        emitAlertEvent(
          "L'âge minimal et maximal sont requis",
          'warning',
          'middle',
          true
        );
        return;
      }
      if (this.tranche.age_min >= this.tranche.age_max) {
        emitAlertEvent(
          "L'âge minimal doît être strictement inférieur à l'âge maximal",
          'warning',
          'middle',
          true
        );
        return;
      }
      //Si la tranche d'âge existe déjà
      if (
        this.tranches.some(
          (tr) =>
            tr.age_max == this.tranche.age_max &&
            tr.age_min == this.tranche.age_min
        )
      ) {
        emitAlertEvent(
          "Cette tranche d'âge existe pour cette catégorie déjà",
          'warning',
          'middle',
          true
        );
        return;
      }
      this.tranches.push(this.tranche);
    }
  }

  appendNewTranche() {
    this.addNewTrancheAge = true;
  }
  cancelAppendNewTranche() {
    this.addNewTrancheAge = false;
  }
  removeTranche(index: number) {
    const tranche = this.tranches[index];
    if (tranche) {
      if (tranche.id) {
        this.tranchesSupprimes.push(tranche.id);
      }
    }
    this.tranches = this.tranches.filter((tr) => tr !== this.tranches[index]);
  }

  private prepareData() {
    const categoryData = {
      ...this.category,
      tranche_age_groupe: [] as TrancheAge[],
    };
    if (!this.tranches.length) {
      this.tranches.push({
        validite: this.tranche.validite,
        age_max: null,
        age_min: null,
      });
    } else {
    }
    return categoryData;
  }
  /**
   *
   * @param changes
   */
  ngOnChanges(changes: SimpleChanges): void {
    console.log(this.question_answer, this.question_answers);
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
    //Netoie les mesages d'erreur
    [
      'category-permis-basic',
      'category-permis-validite',
      'category-permis-extension',
    ].forEach((formId) => {
      this.errorHandler.clearServerErrorsMessages(formId);
    });
    //Met ajout la catégorie
    this.setTrancheageFromServer();
  }
  ngAfterViewInit(): void {
    $('#category-permis-basic').on('change', (e) => {
      this.basicFormHasChange = true;
    });

    $('#category-permis-validite').on('change', (e) => {
      this.basicFormHasChange = true;
    });
  }
  private setTrancheageFromServer() {
    // this.tranche = {} as TrancheAge;
    // if (this.category.id) {
    //   this.editPage = true;
    //   //Le button d'ajout de tranche d'âge sera éteint par défaut
    //   this.addNewTrancheAge = false;
    //   this.is_extension = Boolean(this.category.is_extension);
    //   const trancheages = this.category.trancheage as any;
    //   // Si le tableau dépasse 2 élément d'office ça dispose de tranche age
    //   this.has_tranche = trancheages.length > 1;
    //   this.syncTrancheAgeFromServerData();
    // }
  }

  private updateCategory() {
    if (this.basicFormHasChange) {
      this.categoryPermisService
        .update(this.category, this.category.id ?? 0)
        .pipe(
          this.errorHandler.handleServerError(
            'category-permis-basic',
            (response) => {
              this.onLoading = false;
            }
          )
        )
        .subscribe((response) => {
          this.emitPermisSaved();
          this.onLoading = false;
          emitAlertEvent(response.message, 'success');
        });
    } else {
      emitAlertEvent('Aucune donnée à modifier');
    }
  }

  /**
   * Si la tranche est modifié
   */
  private updateTranche() {
    if (this.tranche.id && this.validiteFormHasChange) {
      this.trancheAgeService
        .update(this.tranche, this.tranche.id)
        .pipe(this.errorHandler.handleServerError('category-permis-validite'))
        .subscribe((response) => {
          emitAlertEvent('Modification effectuée avec succès!', 'success');
        });
    }
  }

  /**
   *  Modifie une tranche d'âge
   * @param index
   */
  editTrancheAge(index: number) {
    this.tranche = this.tranches[index] ?? this.tranche;
    if (this.tranche) {
      this.editTranche = true;
    }
  }

  private deleteTrancheAge() {
    if (this.tranchesSupprimes.length) {
      //Ceci ne devrait pas causer un souci de performance, il n'y aura pas assez de tranche d'âge à supprimer
      for (const id of this.tranchesSupprimes) {
        this.trancheAgeService
          .delete(id)
          .pipe(this.errorHandler.handleServerErrors())
          .subscribe((response) => {
            emitAlertEvent(response.message, 'success');
          });
      }
    }
  }

  private syncTrancheAgeFromServerData() {
    const trancheages = this.category.trancheage as any;
    if (this.has_tranche) {
      this.tranches = trancheages;
      const lastIndex = this.tranches.length - 1;
      //On prend la dernière
      this.tranche = this.tranches[lastIndex >= 0 ? lastIndex : 0];
    } else {
      const tranche = trancheages[0];

      if (tranche) {
        //Ceci conservera le tableau même si c'est un seul élément dans le tableau
        this.tranches = trancheages;
        //Si age min et max existent
        this.has_tranche = tranche.age_min !== null && tranche.age_max !== null;
        if (!this.has_tranche) {
          this.tranche = tranche;
          this.tranches = [];
        }
      }
    }
    console.log(this.category);
  }
}
