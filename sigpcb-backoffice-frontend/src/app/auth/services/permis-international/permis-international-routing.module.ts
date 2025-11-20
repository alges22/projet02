import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { PermisInternationalComponent } from './permis-international.component';
import { DemandePermisInterComponent } from './demande-permis-inter/demande-permis-inter.component';
import { RejetPermisInterComponent } from './rejet-permis-inter/rejet-permis-inter.component';
import { ValidatePermisInterComponent } from './validate-permis-inter/validate-permis-inter.component';

const routes: Routes = [
  {
    path: '',
    component: PermisInternationalComponent,
    children: [
      {
        path: 'nouvelles',
        component: DemandePermisInterComponent,
      },
      {
        path: 'rejets',
        component: RejetPermisInterComponent,
      },

      {
        path: 'valides',
        component: ValidatePermisInterComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class PermisInternationalRoutingModule {}
