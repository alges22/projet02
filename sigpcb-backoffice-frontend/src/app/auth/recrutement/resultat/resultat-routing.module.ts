import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ResultatComponent } from './resultat.component';
import { ResultatFinalComponent } from './resultat-final/resultat-final.component';

const routes: Routes = [
  {
    path: '',
    component: ResultatComponent,
    children: [
      {
        path: '',
        component: ResultatFinalComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ResultatRoutingModule {}
