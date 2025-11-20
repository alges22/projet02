import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { DashboardRoutingModule } from './dashboard-routing.module';
import { DashboardComponent } from './dashboard.component';
import { CoreModule } from 'src/app/core/core.module';
import { DashHomeComponent } from './dash-home/dash-home.component';
import { FormsModule } from '@angular/forms';
import { AnnotationComponent } from './components/annotation/annotation.component';
import { AngularSignaturePadModule } from '@almothafar/angular-signature-pad';
import { NoteComponent } from './dash-home/note/note.component';
import { PresenceComponent } from './components/presence/presence.component';
import { ComponentsComponent } from './components/components.component';

@NgModule({
  declarations: [
    DashboardComponent,
    DashHomeComponent,
    AnnotationComponent,
    NoteComponent,
    PresenceComponent,
    ComponentsComponent,
  ],
  imports: [
    CommonModule,
    DashboardRoutingModule,
    FormsModule,
    CoreModule,
    AngularSignaturePadModule,
  ],
  exports: [NoteComponent],
})
export class DashboardModule {}
