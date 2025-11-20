import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminUniteComponent } from './admin-unite.component';

const routes: Routes = [{ path: '', component: AdminUniteComponent }];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminUniteRoutingModule { }
