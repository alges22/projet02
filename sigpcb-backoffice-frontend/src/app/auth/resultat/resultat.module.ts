import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ResultatRoutingModule } from './resultat-routing.module';
import { ResultatComponent } from './resultat.component';
import { ConduitesComponent } from './conduites/conduites.component';
import { CodesComponent } from './codes/codes.component';
import { CoreModule } from 'src/app/core/core.module';
import { AbsentsComponent } from './absents/absents.component';
import { FormsModule } from '@angular/forms';
import { AdmisDefinitifsComponent } from './admis-definitifs/admis-definitifs.component';
import { NgxPaginationModule } from 'ngx-pagination';

@NgModule({
  declarations: [
    ResultatComponent,
    ConduitesComponent,
    CodesComponent,
    AbsentsComponent,
    AdmisDefinitifsComponent,
  ],
  imports: [
    CommonModule,
    ResultatRoutingModule,
    CoreModule,
    NgxPaginationModule,
    FormsModule,
  ],
})
export class ResultatModule {}
