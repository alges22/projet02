import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ActeSignesComponent } from './acte-signes/acte-signes.component';
import { SignatairesComponent } from './signataires/signataires.component';
import { SignaturesComponent } from './signatures.component';

const routes: Routes = [
  {
    path: '',
    component: SignaturesComponent,
    children: [
      {
        path: 'signataires',
        component: SignatairesComponent,
      },
      {
        path: 'acte-signes',
        component: ActeSignesComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class SignaturesRoutingModule {}
