import { AuthComponent } from './auth.component';
import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from '../core/guard/auth.guard';

const routes: Routes = [
  {
    path: '',
    component: AuthComponent,
    children: [
      {
        path: 'dashboard',
        canActivateChild: [AuthGuard],
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./dashboard/dashboard.module').then((m) => m.DashboardModule),
      },
      {
        path: 'profiles',
        canActivateChild: [AuthGuard],
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./profile/profile.module').then((m) => m.ProfileModule),
      },
      {
        path: 'gestions',
        loadChildren: () =>
          import('./gestion/gestion.module').then((m) => m.GestionModule),
      },
      {
        path: 'licences',
        canActivateChild: [AuthGuard],
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./licences/licences.module').then((m) => m.LicencesModule),
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AuthRoutingModule {}
