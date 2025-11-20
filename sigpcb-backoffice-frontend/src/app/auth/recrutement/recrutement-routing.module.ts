import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { RecrutementComponent } from './recrutement.component';

const routes: Routes = [
  {
    path: 'examinateurs',
    loadChildren: () =>
      import('./examinateur/examinateur.module').then(
        (m) => m.ExaminateurModule
      ),
  },
  {
    path: 'conducteurs',
    loadChildren: () =>
      import('./conducteur/conducteur.module').then((m) => m.ConducteurModule),
  },
  {
    path: 'gestion-recrutement',
    loadChildren: () =>
      import('./gestion-recrutement/gestion-recrutement.module').then(
        (m) => m.GestionRecrutementModule
      ),
  },
  {
    path: 'programmation',
    loadChildren: () =>
      import('./programmation/programmation.module').then(
        (m) => m.ProgrammationModule
      ),
  },
  {
    path: 'resultat',
    loadChildren: () =>
      import('./resultat/resultat.module').then((m) => m.ResultatModule),
  },
  {
    path: 'moniteurs',
    loadChildren: () =>
      import('./moniteur/moniteur.module').then((m) => m.MoniteurModule),
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class RecrutementRoutingModule {}
