import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthenticitePermisComponent } from './authenticite-permis.component';
import { DemandeAuthPermisComponent } from './demande-auth-permis/demande-auth-permis.component';
import { RejetAuthPermisComponent } from './rejet-auth-permis/rejet-auth-permis.component';
import { ValidateAuthPermisComponent } from './validate-auth-permis/validate-auth-permis.component';

const routes: Routes = [
  {
    path: '',
    component: AuthenticitePermisComponent,
    children: [
      {
        path: '',
        component: DemandeAuthPermisComponent,
      },
      {
        path: 'nouvelles',
        component: DemandeAuthPermisComponent,
      },
      {
        path: 'rejets',
        component: RejetAuthPermisComponent,
      },

      {
        path: 'valides',
        component: ValidateAuthPermisComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AuthenticitePermisRoutingModule {}
