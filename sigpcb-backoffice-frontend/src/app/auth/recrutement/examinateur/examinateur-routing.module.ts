import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ExaminateurComponent } from './examinateur.component';
import { DemandeExaminateurComponent } from './demande-examinateur/demande-examinateur.component';
import { RejetExaminateurComponent } from './rejet-examinateur/rejet-examinateur.component';
import { ValidateExaminateurComponent } from './validate-examinateur/validate-examinateur.component';

const routes: Routes = [
  {
    path: '',
    component: ExaminateurComponent,
    children: [
      {
        path: 'nouvelles',
        component: DemandeExaminateurComponent,
      },
      {
        path: 'rejets',
        component: RejetExaminateurComponent,
      },
      {
        path: 'valides',
        component: ValidateExaminateurComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ExaminateurRoutingModule {}
