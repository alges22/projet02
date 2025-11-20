import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AgreementComponent } from './agreement.component';
import { ANouvelleDemandesComponent } from './a-nouvelle-demandes/a-nouvelle-demandes.component';
import { AgreementRejetesComponent } from './agreement-rejetes/agreement-rejetes.component';
import { AgreementValidesComponent } from './agreement-valides/agreement-valides.component';

const routes: Routes = [
  {
    path: '',
    component: AgreementComponent,
    children: [
      {
        path: 'news',
        component: ANouvelleDemandesComponent,
      },
      {
        path: 'rejets',
        component: AgreementRejetesComponent,
      },
      {
        path: 'valides',
        component: AgreementValidesComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AgreementRoutingModule {}
