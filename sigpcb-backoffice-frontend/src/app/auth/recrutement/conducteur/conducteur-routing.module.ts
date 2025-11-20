import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ConducteurComponent } from './conducteur.component';
import { EntrepriseConducteurComponent } from './entreprise-conducteur/entreprise-conducteur.component';

const routes: Routes = [
  {
    path: '',
    component: ConducteurComponent,
    children: [
      {
        path: '',
        component: EntrepriseConducteurComponent,
      },
      {
        path: 'entreprises',
        component: EntrepriseConducteurComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ConducteurRoutingModule {}
