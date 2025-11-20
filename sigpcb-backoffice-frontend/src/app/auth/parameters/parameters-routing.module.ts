import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ParametersComponent } from './parameters.component';
import { ParametersHomeComponent } from './parameters-home/parameters-home.component';

const routes: Routes = [
  {
    path: '',
    component: ParametersComponent,
    children: [
      {
        path: '',
        component: ParametersHomeComponent,
      },
      {
        path: 'gestions',
        // canActivate: [AccessGuard],
        loadChildren: () =>
          import('./administrateurs/administrateurs.module').then(
            (m) => m.AdministrateursModule
          ),
      },
      {
        path: 'territoriales',
        loadChildren: () =>
          import('./territoriales/territoriales.module').then(
            (m) => m.TerritorialesModule
          ),
      },
      {
        path: 'base',
        loadChildren: () =>
          import('./param-base/param-base.module').then(
            (m) => m.ParamBaseModule
          ),
      },
      {
        path: 'territoriales',
        loadChildren: () =>
          import('./territoriales/territoriales.module').then(
            (m) => m.TerritorialesModule
          ),
      },
      {
        path: 'signatures',
        loadChildren: () =>
          import('./signatures/signatures.module').then(
            (m) => m.SignaturesModule
          ),
      },
      {
        path: 'examatique',
        loadChildren: () =>
          import('./examatique/examatique.module').then(
            (m) => m.ExamatiqueModule
          ),
      },

      {
        path: 'inspections',
        loadChildren: () =>
          import('./inspections/inspections.module').then(
            (m) => m.InspectionsModule
          ),
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ParametersRoutingModule {}
