import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { EntrepriseRoutingModule } from './entreprise-routing.module';
import { EntrepriseComponent } from './entreprise.component';
import { DashboardComponent } from './dashboard/dashboard.component';
import { FormsModule } from '@angular/forms';
import { CoreModule } from 'src/app/core/core.module';
import { SessionsComponent } from './sessions/sessions.component';
import { SessionComponent } from './session/session.component';
import { NgxPaginationModule } from 'ngx-pagination';
import { CandidatInfoComponent } from './components/candidat-info/candidat-info.component';
import { SuivieComponent } from './suivie/suivie.component';

@NgModule({
  declarations: [
    EntrepriseComponent,
    DashboardComponent,
    SessionsComponent,
    SessionComponent,
    CandidatInfoComponent,
    SuivieComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    CoreModule,
    EntrepriseRoutingModule,
    NgxPaginationModule,
  ],
})
export class EntrepriseModule {}
