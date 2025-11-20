import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ReportingComponent } from './reporting.component';
import { PaiementsComponent } from './paiements/paiements.component';
import { PaiementTitresDeviresComponent } from './paiement-titres-devires/paiement-titres-devires.component';

const routes: Routes = [
  {
    path: '',
    component: ReportingComponent,
    children: [
      {
        path: 'paiements',
        component: PaiementsComponent,
      },
      {
        path: 'paiement-titres-derives',
        component: PaiementTitresDeviresComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ReportingRoutingModule {}
