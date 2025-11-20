import { AuthComponent } from './auth.component';
import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

const routes: Routes = [
  {
    path: '',
    component: AuthComponent,
    children: [
      {
        path: 'dashboard',
        loadChildren: () =>
          import('./dashboard/dashboard.module').then((m) => m.DashboardModule),
      },
      {
        path: 'parametres',
        loadChildren: () =>
          import('./parameters/parameters.module').then(
            (m) => m.ParametersModule
          ),
      },
      {
        path: 'profiles',
        loadChildren: () =>
          import('./profile/profile.module').then((m) => m.ProfileModule),
      },

      {
        path: 'validations',
        loadChildren: () =>
          import('./validation/validation.module').then(
            (m) => m.ValidationModule
          ),
      },
      {
        path: 'programmations',
        loadChildren: () =>
          import('./programmation/programmation.module').then(
            (m) => m.ProgrammationModule
          ),
      },

      {
        path: 'resultats',
        loadChildren: () =>
          import('./resultat/resultat.module').then((m) => m.ResultatModule),
      },

      {
        path: 'agendas',
        loadChildren: () =>
          import('./agenda/agenda.module').then((m) => m.AgendaModule),
      },
      {
        path: 'reporting',
        loadChildren: () =>
          import('./reporting/reporting.module').then((m) => m.ReportingModule),
      },
      {
        path: 'auto-ecoles',
        loadChildren: () =>
          import('./auto-ecoles/auto-ecoles.module').then(
            (m) => m.AutoEcolesModule
          ),
      },
      {
        path: 'services',
        loadChildren: () =>
          import('./services/services.module').then((m) => m.ServicesModule),
      },
      {
        path: 'statistiques',
        loadChildren: () =>
          import('./statistiques/statistiques.module').then(
            (m) => m.StatistiquesModule
          ),
      },
      {
        path: 'recrutements',
        loadChildren: () =>
          import('./recrutement/recrutement.module').then(
            (m) => m.RecrutementModule
          ),
      },
      {
        path: 'faqs',
        loadChildren: () => import('./faq/faq.module').then((m) => m.FaqModule),
      },
      {
        path: 'candidats',
        loadChildren: () =>
          import('./candidats/candidats.module').then((m) => m.CandidatsModule),
      },

      {
        path: 'logs',
        loadChildren: () =>
          import('./logs/logs.module').then((m) => m.LogsModule),
      },
      {
        path: 'dispenses-payments',
        loadChildren: () =>
          import('./laisser-passer/laisser-passer.module').then(
            (m) => m.LaisserPasserModule
          ),
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AuthRoutingModule {}
