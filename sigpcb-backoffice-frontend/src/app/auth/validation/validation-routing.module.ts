import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ValidationComponent } from './validation.component';
import { JustifRecusComponent } from './justifs/justif-recus/justif-recus.component';
import { JustifRejetesComponent } from './justifs/justif-rejetes/justif-rejetes.component';
import { JustifValidesComponent } from './justifs/justif-valides/justif-valides.component';

const routes: Routes = [
  {
    path: '',
    component: ValidationComponent,
    children: [
      {
        path: '',
        component: JustifRecusComponent,
      },
      {
        path: 'justifs-recus',
        component: JustifRecusComponent,
      },
      {
        path: 'justifs-rejetes',
        component: JustifRejetesComponent,
      },
      {
        path: 'justifs-valides',
        component: JustifValidesComponent,
      },
    ],
  },

  {
    path: 'dossiers',
    loadChildren: () =>
      import('./dossiers/dossiers.module').then((m) => m.DossiersModule),
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ValidationRoutingModule {}
