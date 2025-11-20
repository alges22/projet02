import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { RemplacementPermisComponent } from './remplacement-permis.component';
import { InitRemplacementPermisComponent } from './init-remplacement-permis/init-remplacement-permis.component';
import { RejetRemplacementPermisComponent } from './rejet-remplacement-permis/rejet-remplacement-permis.component';
import { ValidateRemplacementPermisComponent } from './validate-remplacement-permis/validate-remplacement-permis.component';

const routes: Routes = [
  {
    path: '',
    component: RemplacementPermisComponent,
    children: [
      {
        path: '',
        component: InitRemplacementPermisComponent,
      },
      {
        path: 'nouvelles',
        component: InitRemplacementPermisComponent,
      },
      {
        path: 'rejetes',
        component: RejetRemplacementPermisComponent,
      },

      {
        path: 'valides',
        component: ValidateRemplacementPermisComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class RemplacementPermisRoutingModule {}
