import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ExamatiqueRoutingModule } from './examatique-routing.module';
import { ExamatiqueComponent } from './examatique.component';
import { QuestionsComponent } from './questions/questions.component';
import { ReponsesComponent } from './reponses/reponses.component';
import { BaremeConduitesComponent } from './bareme-conduites/bareme-conduites.component';
import { CoreModule } from '../../../core/core.module';
import { ExamatiqueTopbarComponent } from './components/examatique-topbar/examatique-topbar.component';
import { FormsModule } from '@angular/forms';
import { ChapitresComponent } from './chapitres/chapitres.component';
import { NgMultiSelectDropDownModule } from 'ng-multiselect-dropdown';
import { NgxPaginationModule } from 'ngx-pagination';
import { AddQuestionComponent } from './components/add-question/add-question.component';
import { NgWindowModule } from 'src/app/ng-window/ng-window.module';
import { QuestionListComponent } from './questions/question-list/question-list.component';
import {
  QuestionFormComponent,
  RegenereQuestionComposition,
} from './questions/question-form/question-form.component';
import { SubbaremeComponent } from './components/subbareme/subbareme.component';

@NgModule({
  declarations: [
    ExamatiqueComponent,
    QuestionsComponent,
    ReponsesComponent,
    BaremeConduitesComponent,
    ExamatiqueTopbarComponent,
    ChapitresComponent,
    AddQuestionComponent,
    QuestionListComponent,
    QuestionFormComponent,
    SubbaremeComponent,
  ],
  imports: [
    CommonModule,
    ExamatiqueRoutingModule,
    CoreModule,
    FormsModule,
    NgxPaginationModule,
    NgMultiSelectDropDownModule.forRoot(),
    NgWindowModule,
  ],
  providers: [RegenereQuestionComposition], // Enregistrez la classe en tant que fournisseur
})
export class ExamatiqueModule {}
