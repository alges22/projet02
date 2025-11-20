import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AgendaComponent } from './agenda.component';
import { SessionExamenComponent } from './session-examen/session-examen.component';
import { EditerCalendrierComponent } from './editer-calendrier/editer-calendrier.component';

const routes: Routes = [
  {
    path: '',
    component: AgendaComponent,
    children: [
      {
        path: 'session-examens',
        component: SessionExamenComponent,
      },
      {
        path: 'editer-calendrier',
        component: EditerCalendrierComponent,
      },
      {
        path: 'editer-calendrier/:id',
        component: EditerCalendrierComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AgendaRoutingModule {}
