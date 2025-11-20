import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { LaisserPasserComponent } from './laisser-passer.component';
import { LaisserPasserHomeComponent } from './laisser-passer-home/laisser-passer-home.component';

const routes: Routes = [
  {
    path: '',
    component: LaisserPasserComponent,
    children: [
      {
        path: 'home',
        component: LaisserPasserHomeComponent,
      },
      {
        path: '',
        component: LaisserPasserHomeComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class LaisserPasserRoutingModule {}
