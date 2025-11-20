import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ProgrammationComponent } from './programmation.component';
import { ConduitesComponent } from './conduites/conduites.component';
import { CodesComponent } from './codes/codes.component';

const routes: Routes = [
  {
    path: '',
    component: ProgrammationComponent,

    children: [
      {
        path: 'codes',
        component: CodesComponent,
      },
      {
        component: ConduitesComponent,
        path: 'conduites',
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ProgrammationRoutingModule {}
