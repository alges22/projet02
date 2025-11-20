import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DashboardComponent } from './dashboard.component';
import { DashHomeComponent } from './dash-home/dash-home.component';
import { HistoriquesComponent } from './historiques/historiques.component';
import { MyInfosComponent } from './my-infos/my-infos.component';
import { AffiliationComponent } from './affiliation/affiliation.component';

const routes: Routes = [
  {
    path: '',
    component: DashboardComponent,
    children: [
      {
        path: '',
        component: DashHomeComponent,
        title: 'Tableau de board',
      },
      {
        path: 'historiques',
        component: HistoriquesComponent,
        title: 'Les notifications',
      },
      {
        path: 'my-informations',
        component: MyInfosComponent,
        title: 'Mes informations',
      },
      {
        path: 'my-informations/:rejetId',
        component: MyInfosComponent,
        title: 'Mes informations',
      },
      {
        path: 'affiliations',
        component: AffiliationComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class DashboardRoutingModule {}
