import { DossierValidesComponent } from './dossier-valides/dossier-valides.component';
import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DossiersComponent } from './dossiers.component';
import { NouvellesDemandesComponent } from './nouvelles-demandes/nouvelles-demandes.component';
import { DossierRejectedComponent } from './dossier-rejected/dossier-rejected.component';

const routes: Routes = [
  {
    path: '',
    component: DossiersComponent,
    children: [
      {
        path: '',
        component: NouvellesDemandesComponent,
      },
      {
        path: 'nouvelles-demandes',
        component: NouvellesDemandesComponent,
      },
      {
        path: 'rejetes',
        component: DossierRejectedComponent,
      },
      {
        path: 'valides',
        component: DossierValidesComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class DossiersRoutingModule {}
