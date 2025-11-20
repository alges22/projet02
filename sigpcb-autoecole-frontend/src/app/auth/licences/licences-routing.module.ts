import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { LicencesComponent } from './licences.component';
import { DemandeLicenceComponent } from './demande-licence/demande-licence.component';

const routes: Routes = [
  {
    path: '',
    component: LicencesComponent,
    children: [
      {
        path: 'demande',
        component: DemandeLicenceComponent,
        title: 'Demande de licence',
      },

      {
        path: 'demande/:id',
        component: DemandeLicenceComponent,
        title: 'Modifier  une demande de licence',
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class LicencesRoutingModule {}
