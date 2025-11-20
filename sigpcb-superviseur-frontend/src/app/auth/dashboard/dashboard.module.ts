import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AngularSignaturePadModule } from '@almothafar/angular-signature-pad';
import { DashboardRoutingModule } from './dashboard-routing.module';
import { DashboardComponent } from './dashboard.component';
import { CoreModule } from 'src/app/core/core.module';
import { DashHomeComponent } from './dash-home/dash-home.component';
import { FormsModule } from '@angular/forms';
import { CandidatInfoComponent } from './components/candidat-info/candidat-info.component';
import { PresenceComponent } from './components/presence/presence.component';
import { CandidatComponent } from './components/candidat/candidat.component';

@NgModule({
  declarations: [DashboardComponent, DashHomeComponent, CandidatInfoComponent, PresenceComponent, CandidatComponent],
  imports: [
    CommonModule,
    DashboardRoutingModule,
    FormsModule,
    AngularSignaturePadModule,
    CoreModule,
  ],
})
export class DashboardModule {}
