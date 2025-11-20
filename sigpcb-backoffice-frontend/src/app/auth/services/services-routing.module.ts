import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

const routes: Routes = [
  {
    path: 'authenticite-permis',
    loadChildren: () =>
      import('./authenticite-permis/authenticite-permis.module').then(
        (m) => m.AuthenticitePermisModule
      ),
  },
  {
    path: 'duplicata-remplacement-permis',
    loadChildren: () =>
      import('./remplacement-permis/remplacement-permis.module').then(
        (m) => m.RemplacementPermisModule
      ),
  },
  {
    path: 'permis-international',
    loadChildren: () =>
      import('./permis-international/permis-international.module').then(
        (m) => m.PermisInternationalModule
      ),
  },
  {
    path: 'echange-permis',
    loadChildren: () =>
      import('./echange-permis/echange-permis.module').then(
        (m) => m.EchangePermisModule
      ),
  },
  {
    path: 'prorogation-permis',
    loadChildren: () =>
      import('./prorogation-permis/prorogation-permis.module').then(
        (m) => m.ProrogationPermisModule
      ),
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ServicesRoutingModule {}
