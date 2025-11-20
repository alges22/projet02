import { CandidatHomeComponent } from './candidat-home/candidat-home.component';
import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { CandidatsComponent } from './candidats.component';
import { CandidatHistoricComponent } from './candidat-historic/candidat-historic.component';
import { CandidatEditComponent } from './candidat-edit/candidat-edit.component';

const routes: Routes = [
  {
    path: '',
    component: CandidatsComponent,
    children: [
      {
        path: '',
        component: CandidatHomeComponent,
      },
      {
        path: 'histories/:npi',
        component: CandidatHistoricComponent,
      },
      {
        path: 'editions',
        component: CandidatEditComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class CandidatsRoutingModule {}
