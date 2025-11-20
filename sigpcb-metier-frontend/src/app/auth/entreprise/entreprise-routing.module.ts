import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { EntrepriseComponent } from './entreprise.component';
import { DashboardComponent } from './dashboard/dashboard.component';
import { SessionsComponent } from './sessions/sessions.component';
import { SessionComponent } from './session/session.component';
import { GuestEntrepriseGuard } from 'src/app/core/guard/guest-entreprise.guard';
import { SuivieComponent } from './suivie/suivie.component';

const routes: Routes = [
  {
    path: '',
    // canActivate: [GuestEntrepriseGuard],
    component: EntrepriseComponent,
    children: [
      {
        path: '',
        component: DashboardComponent,
      },
      {
        path: 'dashboard',
        component: DashboardComponent,
      },
      {
        path: 'sessions',
        component: SessionsComponent,
      },
      {
        path: 'session/:sesionId',
        component: SessionComponent,
      },
      {
        path: 'suivre-demande',
        component: SuivieComponent,
      },
      // {
      //   path: 'absence-conduite',
      //   component: InscriptionConduiteComponent,
      // },
      // {
      //   path: 'absence-conduite-justification',
      //   component: AbsenceConduiteJustifComponent,
      // },
      // {
      //   path: 'absence-code-justification',
      //   component: AbsenceCodeJustifComponent,
      // },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class EntrepriseRoutingModule {}
