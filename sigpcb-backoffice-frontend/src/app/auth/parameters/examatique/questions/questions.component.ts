import { Component, ElementRef, SimpleChanges } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Question } from 'src/app/core/interfaces/question';
import { BrowserEventServiceService } from 'src/app/core/services/browser-event-service.service';
import { ChapitreService } from 'src/app/core/services/chapitre.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LangueService } from 'src/app/core/services/langue.service';
import { QuestionService } from 'src/app/core/services/question.service';
import { ReponseService } from 'src/app/core/services/reponse.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { apiUrl, is_array } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-questions',
  templateUrl: './questions.component.html',
  styleUrls: ['./questions.component.scss'],
})
export class QuestionsComponent {
  category = {} as CategoryPermis;
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
    private questionService: QuestionService,
    private langueService: LangueService,
    private reponseService: ReponseService,
    private chapitreService: ChapitreService,
    private errorHandler: HttpErrorHandlerService,
    private refElement: ElementRef<HTMLElement>,
    private browerEventService: BrowserEventServiceService
  ) {}

  ngOnInit(): void {}
}
