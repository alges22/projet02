import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ExamatiqueComponent } from './examatique.component';
import { QuestionsComponent } from './questions/questions.component';
import { ReponsesComponent } from './reponses/reponses.component';
import { BaremeConduitesComponent } from './bareme-conduites/bareme-conduites.component';
import { ChapitresComponent } from './chapitres/chapitres.component';
import { QuestionListComponent } from './questions/question-list/question-list.component';
import { QuestionFormComponent } from './questions/question-form/question-form.component';

const routes: Routes = [
  {
    path: '',
    component: ExamatiqueComponent,
    children: [
      {
        path: 'questions',
        component: QuestionsComponent,
        children: [
          {
            path: '',
            component: QuestionListComponent,
          },
          {
            path: 'add',
            component: QuestionFormComponent,
          },
          {
            path: 'edit/:id',
            component: QuestionFormComponent,
          },
        ],
      },
      {
        path: 'reponses',
        component: ReponsesComponent,
      },
      {
        path: 'bareme-conduites',
        component: BaremeConduitesComponent,
      },
      {
        path: 'chapitres',
        component: ChapitresComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ExamatiqueRoutingModule {}
