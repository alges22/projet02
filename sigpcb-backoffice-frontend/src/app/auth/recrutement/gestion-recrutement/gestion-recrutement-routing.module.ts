import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { GestionRecrutementComponent } from './gestion-recrutement.component';
import { GrDemandesComponent } from './gr-demandes/gr-demandes.component';
import { GrDemandesRejectedComponent } from './gr-demandes-rejected/gr-demandes-rejected.component';
import { GrDemandesValidateComponent } from './gr-demandes-validate/gr-demandes-validate.component';

const routes: Routes = [
  {
    path: '',
    component: GestionRecrutementComponent,
    children: [
      {
        path: '',
        component: GrDemandesComponent,
      },
      {
        path: 'nouvelles',
        component: GrDemandesComponent,
      },
      {
        path: 'rejets',
        component: GrDemandesRejectedComponent,
      },
      {
        path: 'valides',
        component: GrDemandesValidateComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class GestionRecrutementRoutingModule {}
