import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { StatistiquesComponent } from './statistiques.component';
import { RapportComponent } from './rapport/rapport.component';
import { FilterSystemComponent } from './filter-system/filter-system.component';

const routes: Routes = [
  {
    path: '',
    component: StatistiquesComponent,
    children: [
      {
        path: '',
        component: RapportComponent,
      },

      {
        path: 'rapports',
        component: RapportComponent,
      },

      {
        path: 'systemes-de-filtrage',
        component: FilterSystemComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class StatistiquesRoutingModule {}
