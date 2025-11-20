import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ProrogationPermisComponent } from './prorogation-permis.component';
import { DemandeProrogationComponent } from './demande-prorogation/demande-prorogation.component';
import { RejetProrogationComponent } from './rejet-prorogation/rejet-prorogation.component';
import { ValidateProrogationComponent } from './validate-prorogation/validate-prorogation.component';

const routes: Routes = [
  {
    path: '',
    component: ProrogationPermisComponent,
    children: [
      {
        path: 'nouvelles',
        component: DemandeProrogationComponent,
      },
      {
        path: 'rejets',
        component: RejetProrogationComponent,
      },

      {
        path: 'valides',
        component: ValidateProrogationComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ProrogationPermisRoutingModule {}
