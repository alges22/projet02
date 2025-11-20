import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AutoEcoleListComponent } from './auto-ecole-list.component';
import { AeactivesComponent } from './aeactives/aeactives.component';
import { AelistsComponent } from './aelists/aelists.component';
import { AeexpiresComponent } from './aeexpires/aeexpires.component';

const routes: Routes = [
  {
    path: '',
    component: AutoEcoleListComponent,
    children: [
      {
        path: 'all',
        component: AelistsComponent,
      },
      {
        path: 'actives',
        component: AeactivesComponent,
      },
      {
        path: 'expires',
        component: AeexpiresComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AutoEcoleListRoutingModule {}
