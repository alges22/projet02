import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { Question } from 'src/app/core/interfaces/question';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LangueService } from 'src/app/core/services/langue.service';
import { QuestionService } from 'src/app/core/services/question.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { apiUrl, is_array } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';
import { RegenereQuestionComposition } from '../question-form/question-form.component';

@Component({
  selector: 'app-question-list',
  templateUrl: './question-list.component.html',
  styleUrls: ['./question-list.component.scss'],
})
export class QuestionListComponent {
  questions: any[] = [];
  langues: any[] = [];
  reponses: any[] = [];
  chapitres: any[] = [];
  question = {} as Question;

  question_answer = {} as any;

  titre_formulaire = 'Ajouter une question';
  modalId = 'add-questions';

  action: 'store' | 'edit' | 'show' | string = 'store';

  searchUrl = apiUrl('/questions');

  assetLink = environment.endpoints.asset;

  onLoading = false;

  onLoadingReponse = false;

  onDeleting = false;

  answers: any[] = [];

  question_answers: any[] = [];

  answer = {} as any;

  audio: any;

  illustration: any;

  time: any;

  pageNumber = 1;

  paginate_data!: any;
  noResults = 'Aucune question';

  constructor(
    private readonly questionService: QuestionService,
    private readonly langueService: LangueService,
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly router: Router
  ) {}

  ngOnInit(): void {
    this.get();
    this.getLangues();
  }

  refresh() {
    this.get();
  }

  add() {
    this.router.navigate(['/parametres/examatique/questions/add']);
  }

  edit(id: any) {
    this.router.navigate(['/parametres/examatique/questions/edit', id]);
  }

  private get() {
    this.errorHandler.startLoader();
    this.questionService
      .get(this.pageNumber)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.paginate_data = response.data;
          if (this.paginate_data.data) this.questions = this.paginate_data.data;
        }
        this.errorHandler.stopLoader();
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

  findQuestionLangueInLangues(question: any): string {
    let languesString = '';
    if (question.audiolangues.length > 0) {
      question.audiolangues.forEach((audiolangue: any) => {
        const matchingLangues = this.langues.filter(
          (item) => item.id === audiolangue.langue_id
        );
        const langueNames = matchingLangues.map((item) => item.name);
        languesString += langueNames.join(', ') + ', ';
      });
      // Suppression du dernier caractère (virgule et espace) de la chaîne
      languesString = languesString.slice(0, -2);
    } else {
      languesString = 'Aucune langue';
    }
    return languesString;
  }

  private setAlert(
    message: string = '',
    type: AlertType = 'warning',
    position: AlertPosition = 'bottom-right',
    fixed?: boolean
  ) {
    this.errorHandler.emitAlert(message, type, position, fixed);
  }

  onSearches(response: ServerResponseType) {
    if (response.status) {
      this.questions = response.data.data ?? response.data;
      //Si la réponse n'est pas bonne on reprend les anciennes données
      if (
        !is_array(this.questions) ||
        (is_array(this.questions) && this.questions.length < 1)
      ) {
        this.get();
      }
    } else {
      this.setAlert(response.message, 'danger', 'middle', true);
      this.get();
    }
  }

  destroy(id: number) {
    this.onDeleting = true;
    this.questionService
      .delete(id)
      .pipe(
        this.errorHandler.handleServerError('questions-form', (response) => {
          this.setAlert(response.message, 'danger', 'middle', true);
          this.onDeleting = false;
        })
      )
      .subscribe((response) => {
        this.onDeleting = false;
        this.get();
        this.setAlert('Réponse supprimée avec succès', 'success');
        // Créez une instance de la classe RegenereQuestionComposition en injectant les dépendances nécessaires (par exemple, questionService et errorHandler)
        const regenereQuestionInstance = new RegenereQuestionComposition(
          this.questionService,
          this.errorHandler
        );

        // Appelez la méthode publique
        regenereQuestionInstance.regenereQuestionComposition();
      });
  }
  paginate(number: number) {
    this.pageNumber = number ?? 1;
    this.get();
  }

  statusChange(event: { id: number; status: boolean }) {
    this.questionService
      .status({
        question_id: event.id,
        action: event.status ? 'active' : 'inactive',
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        this.setAlert(response.message.message, 'success');
      });
  }
}
