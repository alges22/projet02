import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { MoniteurComponent } from './moniteur.component';
import { DemandeMoniteurComponent } from './demande-moniteur/demande-moniteur.component';
import { RejetMoniteurComponent } from './rejet-moniteur/rejet-moniteur.component';
import { ValidateMoniteurComponent } from './validate-moniteur/validate-moniteur.component';

const routes: Routes = [
  {
    path: '',
    component: MoniteurComponent,
    children: [
      {
        path: 'nouvelles',
        component: DemandeMoniteurComponent,
      },
      {
        path: 'rejets',
        component: RejetMoniteurComponent,
      },
      {
        path: 'valides',
        component: ValidateMoniteurComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class MoniteurRoutingModule {}
