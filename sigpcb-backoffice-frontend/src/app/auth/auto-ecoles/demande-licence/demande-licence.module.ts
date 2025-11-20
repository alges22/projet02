import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { DemandeLicenceRoutingModule } from './demande-licence-routing.module';
import { DemandeLicenceComponent } from './demande-licence.component';
import { NouvelleLicenceComponent } from './nouvelle-licence/nouvelle-licence.component';
import { FormsModule } from '@angular/forms';
import { NgxPaginationModule } from 'ngx-pagination';
import { CoreModule } from 'src/app/core/core.module';
import { LicenceFicheComponent } from './components/licence-fiche/licence-fiche.component';
import { LicenceRejeteeComponent } from './licence-rejetee/licence-rejetee.component';
import { LicenceValideeComponent } from './licence-validee/licence-validee.component';

@NgModule({
  declarations: [DemandeLicenceComponent, NouvelleLicenceComponent, LicenceFicheComponent, LicenceRejeteeComponent, LicenceValideeComponent],
  imports: [
    CommonModule,
    DemandeLicenceRoutingModule,
    NgxPaginationModule,
    CoreModule,
    FormsModule,
  ],
})
export class DemandeLicenceModule {}
