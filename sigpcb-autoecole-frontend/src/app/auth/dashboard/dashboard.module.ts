import { NgxPaginationModule } from 'ngx-pagination';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { DashboardRoutingModule } from './dashboard-routing.module';
import { DashboardComponent } from './dashboard.component';
import { CoreModule } from 'src/app/core/core.module';
import { DashHomeComponent } from './dash-home/dash-home.component';
import { FormsModule } from '@angular/forms';
import { HistoriquesComponent } from './historiques/historiques.component';
import { AgrementHistoriqComponent } from './historiques/agrement-historiq/agrement-historiq.component';
import { LicenceHistoriqComponent } from './historiques/licence-historiq/licence-historiq.component';
import { MyInfosComponent } from './my-infos/my-infos.component';
import { InformationHistoriqComponent } from './historiques/information-historiq/information-historiq.component';
import { AffiliationComponent } from './affiliation/affiliation.component';

@NgModule({
  declarations: [
    DashboardComponent,
    DashHomeComponent,
    HistoriquesComponent,
    AgrementHistoriqComponent,
    LicenceHistoriqComponent,
    MyInfosComponent,
    InformationHistoriqComponent,
    AffiliationComponent,
  ],
  imports: [
    CommonModule,
    DashboardRoutingModule,
    FormsModule,
    CoreModule,
    NgxPaginationModule,
  ],
})
export class DashboardModule {}
