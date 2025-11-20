import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { StaticComponent } from './static.component';
import { WelcomeComponent } from './welcome/welcome.component';

const routes: Routes = [
  {
    path: '',
    component: StaticComponent,
    children: [
      {
        path: '',
        component: WelcomeComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class StaticRoutingModule {}
