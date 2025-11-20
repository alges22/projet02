import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { DashboardRoutingModule } from './dashboard-routing.module';
import { DashboardComponent } from './dashboard.component';
import { NgChartsModule } from 'ng2-charts';
import { CoreModule } from 'src/app/core/core.module';
import { ResultatSessionsComponent } from './resultat-sessions/resultat-sessions.component';
import { FormsModule } from '@angular/forms';

@NgModule({
  declarations: [DashboardComponent, ResultatSessionsComponent],
  imports: [
    CommonModule,
    DashboardRoutingModule,
    NgChartsModule,
    FormsModule,
    CoreModule,
  ],
})
export class DashboardModule {}
