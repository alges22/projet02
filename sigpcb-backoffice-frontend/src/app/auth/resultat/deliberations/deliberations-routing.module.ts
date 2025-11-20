import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DeliberationsComponent } from './deliberations.component';
import { ResultDelibAdmisComponent } from './result-delib-admis/result-delib-admis.component';

const routes: Routes = [
  {
    path: '',
    component: DeliberationsComponent,
    children: [
      {
        path: 'admis',
        component: ResultDelibAdmisComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class DeliberationsRoutingModule {}
