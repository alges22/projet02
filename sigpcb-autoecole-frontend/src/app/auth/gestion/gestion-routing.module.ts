import { AgendaComponent } from './agenda/agenda.component';
import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { GestionComponent } from './gestion.component';
import { GestDashboardComponent } from './gest-dashboard/gest-dashboard.component';
import { MonitoringComponent } from './monitoring/monitoring.component';
import { CandidatFormationComponent } from './candidat-formation/candidat-formation.component';
import { FaqComponent } from './faq/faq.component';
import { ServiceClientComponent } from './service-client/service-client.component';
import { StatutValidationComponent } from './statut-validation/statut-validation.component';
import { CandidatsComponent } from './candidats/candidats.component';
import { AbsencesComponent } from './absences/absences.component';
import { MonitorGuardGuard } from 'src/app/core/guard/monitor-guard.guard';
import { AuthGuard } from 'src/app/core/guard/auth.guard';

const routes: Routes = [
  {
    path: '',
    component: GestionComponent,
    children: [
      {
        path: 'home',
        canActivate: [AuthGuard],
        component: GestDashboardComponent,
      },
      {
        path: 'monitoring',

        canActivate: [MonitorGuardGuard],
        component: MonitoringComponent,
      },
      {
        path: 'statut-candidat-formation',
        canActivate: [AuthGuard],
        component: CandidatFormationComponent,
      },
      {
        path: 'statut-validations',
        canActivate: [AuthGuard],
        component: StatutValidationComponent,
      },
      {
        path: 'candidats',
        canActivate: [AuthGuard],
        component: CandidatsComponent,
      },
      {
        path: 'absences',
        canActivate: [AuthGuard],
        component: AbsencesComponent,
      },
      {
        path: 'service-client',
        canActivate: [AuthGuard],
        component: ServiceClientComponent,
      },
      {
        path: 'agenda',
        canActivate: [AuthGuard],
        component: AgendaComponent,
      },
      {
        path: 'faqs',
        canActivate: [AuthGuard],
        component: FaqComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class GestionRoutingModule {}
