import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { StaticMoniteurComponent } from './static-moniteur.component';
import { WelcomeComponent } from './welcome/welcome.component';

const routes: Routes = [
  {
    path: '',
    component: StaticMoniteurComponent,
    children: [
      {
        path: '',
        component: WelcomeComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class StaticMoniteurRoutingModule {}
