import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DashboardComponent } from './dashboard.component';
import { DashHomeComponent } from './dash-home/dash-home.component';
import { PremierInscriptionComponent } from './demandes/premier-inscription/premier-inscription.component';
import { InscriptionConduiteComponent } from './demandes/inscription-conduite/inscription-conduite.component';
import { AbsenceConduiteJustifComponent } from './demandes/absence-conduite-justif/absence-conduite-justif.component';
import { AbsenceCodeJustifComponent } from './demandes/absence-code-justif/absence-code-justif.component';

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
        path: 'inscription-conduite',
        component: InscriptionConduiteComponent,
      },
      {
        path: 'absence-conduite',
        component: InscriptionConduiteComponent,
      },
      {
        path: 'absence-conduite-justification',
        component: AbsenceConduiteJustifComponent,
      },
      {
        path: 'absence-code-justification',
        component: AbsenceCodeJustifComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class DashboardRoutingModule {}
