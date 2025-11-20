import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AgendaRoutingModule } from './agenda-routing.module';
import { AgendaComponent } from './agenda.component';
import { SessionExamenComponent } from './session-examen/session-examen.component';
import { CoreModule } from 'src/app/core/core.module';
import { EditerCalendrierComponent } from './editer-calendrier/editer-calendrier.component';
import { FormsModule } from '@angular/forms';

@NgModule({
  declarations: [
    AgendaComponent,
    SessionExamenComponent,
    EditerCalendrierComponent,
  ],
  imports: [CommonModule, CoreModule, FormsModule, AgendaRoutingModule],
})
export class AgendaModule {}
