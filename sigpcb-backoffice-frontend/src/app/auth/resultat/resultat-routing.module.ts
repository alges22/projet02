import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ResultatComponent } from './resultat.component';
import { CodesComponent } from './codes/codes.component';
import { ConduitesComponent } from './conduites/conduites.component';
import { AbsentsComponent } from './absents/absents.component';
import { AdmisDefinitifsComponent } from './admis-definitifs/admis-definitifs.component';

const routes: Routes = [
  {
    path: '',
    component: ResultatComponent,
    children: [
      {
        path: 'codes',
        component: CodesComponent,
      },
      {
        component: ConduitesComponent,
        path: 'conduites',
      },
      {
        component: AbsentsComponent,
        path: 'absents',
      },

      {
        component: AdmisDefinitifsComponent,
        path: 'admis-definitifs',
      },
    ],
  },
  {
    path: 'deliberations',
    loadChildren: () =>
      import('./deliberations/deliberations.module').then(
        (m) => m.DeliberationsModule
      ),
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ResultatRoutingModule {}
