import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DemandeLicenceComponent } from './demande-licence.component';
import { NouvelleLicenceComponent } from './nouvelle-licence/nouvelle-licence.component';
import { LicenceRejeteeComponent } from './licence-rejetee/licence-rejetee.component';
import { LicenceValideeComponent } from './licence-validee/licence-validee.component';

const routes: Routes = [
  {
    path: '',
    component: DemandeLicenceComponent,
    children: [
      {
        path: 'nouvelles-licences',
        component: NouvelleLicenceComponent,
      },
      {
        path: 'licences-rejetees',
        component: LicenceRejeteeComponent,
      },
      {
        path: 'licences-validees',
        component: LicenceValideeComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class DemandeLicenceRoutingModule {}
