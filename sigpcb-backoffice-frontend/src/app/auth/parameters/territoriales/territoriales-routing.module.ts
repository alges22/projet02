import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AnnexesComponent } from './annexes/annexes.component';
import { ArrondissementsComponent } from './arrondissements/arrondissements.component';
import { CommunesComponent } from './communes/communes.component';
import { DepartementsComponent } from './departements/departements.component';
import { TerritorialesComponent } from './territoriales.component';
import { AnnexeListComponent } from './annexes/annexe-list/annexe-list.component';
import { AnnexeFormComponent } from './annexes/annexe-form/annexe-form.component';

const routes: Routes = [
  {
    path: '',
    component: TerritorialesComponent,
    children: [
      {
        path: '',
        component: AnnexesComponent,
        children: [
          {
            path: '',
            component: AnnexeListComponent,
          },
          {
            path: 'annexes/add',
            component: AnnexeFormComponent,
          },
          {
            path: 'annexes/edit/:id',
            component: AnnexeFormComponent,
          },
        ]
      },
      {
        path: 'departements',
        component: DepartementsComponent,
      },
      {
        path: 'communes',
        component: CommunesComponent,
      },
      {
        path: 'arrondissements',
        component: ArrondissementsComponent,
      },
    ],
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class TerritorialesRoutingModule { }
