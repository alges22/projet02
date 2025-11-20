import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { LogsComponent } from './logs.component';
import { DeveloperModeComponent } from './developer-mode/developer-mode.component';

const routes: Routes = [
  {
    path: '',
    component: LogsComponent,
    children: [
      {
        path: '',
        component: DeveloperModeComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class LogsRoutingModule {}
