import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DashboardComponent } from './dashboard.component';
import { DashHomeComponent } from './dash-home/dash-home.component';
import { PremierInscriptionComponent } from './demandes/premier-inscription/premier-inscription.component';
import { InscriptionConduiteComponent } from './demandes/inscription-conduite/inscription-conduite.component';
import { EditInscriptionComponent } from './demandes/edit-inscription/edit-inscription.component';
import { EditDossierComponent } from './demandes/edit-dossier/edit-dossier.component';
import { InscriptionAbsenceComponent } from './demandes/inscription-absence/inscription-absence.component';

const routes: Routes = [
  {
    path: '',
    component: DashboardComponent,
    children: [
      {
        path: '',
        component: DashHomeComponent,
      },
      {
        path: 'inscription-au-permis',
        component: PremierInscriptionComponent,
      },
      {
        path: 'inscription-au-permis/:id',
        component: EditInscriptionComponent,
      },
      {
        path: 'inscription-conduite',
        component: InscriptionConduiteComponent,
      },

      {
        path: 'edit-dossier',
        component: EditDossierComponent,
      },
      {
        path: 'inscription-absence',
        component: InscriptionAbsenceComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class DashboardRoutingModule {}
