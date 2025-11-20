import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { EchangePermisComponent } from './echange-permis.component';
import { DemandeEchangePermisComponent } from './demande-echange-permis/demande-echange-permis.component';
import { RejetEchangePermisComponent } from './rejet-echange-permis/rejet-echange-permis.component';
import { ValidateEchangePermisComponent } from './validate-echange-permis/validate-echange-permis.component';

const routes: Routes = [
  {
    path: '',
    component: EchangePermisComponent,
    children: [
      {
        path: 'nouvelles',
        component: DemandeEchangePermisComponent,
      },
      {
        path: 'rejets',
        component: RejetEchangePermisComponent,
      },

      {
        path: 'valides',
        component: ValidateEchangePermisComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class EchangePermisRoutingModule {}
