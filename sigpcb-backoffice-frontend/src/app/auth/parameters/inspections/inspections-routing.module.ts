import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { InspectionsComponent } from './inspections.component';
import { InspecteursComponent } from './inspecteurs/inspecteurs.component';
import { ExaminateursComponent } from './examinateurs/examinateurs.component';

const routes: Routes = [
  {
    path: '',
    component: InspectionsComponent,
    children: [
      {
        path: 'inspecteurs',
        component: InspecteursComponent,
      },
      {
        path: 'examinateurs',
        component: ExaminateursComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class InspectionsRoutingModule {}
