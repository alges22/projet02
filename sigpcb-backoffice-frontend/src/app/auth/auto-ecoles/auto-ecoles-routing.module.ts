import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AutoEcolesComponent } from './auto-ecoles.component';

const routes: Routes = [
  {
    path: 'agreements',
    loadChildren: () =>
      import('./agreement/agreement.module').then((m) => m.AgreementModule),
  },
  {
    path: 'listes',
    loadChildren: () =>
      import('./auto-ecole-list/auto-ecole-list.module').then(
        (m) => m.AutoEcoleListModule
      ),
  },
  {
    path: 'demande-licence',
    loadChildren: () =>
      import('./demande-licence/demande-licence.module').then(
        (m) => m.DemandeLicenceModule
      ),
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AutoEcolesRoutingModule {}
