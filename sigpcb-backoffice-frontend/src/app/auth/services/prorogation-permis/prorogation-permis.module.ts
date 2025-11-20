import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ProrogationPermisRoutingModule } from './prorogation-permis-routing.module';
import { ProrogationPermisComponent } from './prorogation-permis.component';
import { ProrogationFicheComponent } from './components/prorogation-fiche/prorogation-fiche.component';
import { ValidateProrogationComponent } from './validate-prorogation/validate-prorogation.component';
import { DemandeProrogationComponent } from './demande-prorogation/demande-prorogation.component';
import { RejetProrogationComponent } from './rejet-prorogation/rejet-prorogation.component';
import { FormsModule } from '@angular/forms';
import { NgxPaginationModule } from 'ngx-pagination';
import { CoreModule } from 'src/app/core/core.module';

@NgModule({
  declarations: [
    ProrogationPermisComponent,
    ProrogationFicheComponent,
    ValidateProrogationComponent,
    DemandeProrogationComponent,
    RejetProrogationComponent,
  ],
  imports: [
    CoreModule,
    CommonModule,
    ProrogationPermisRoutingModule,
    NgxPaginationModule,
    FormsModule,
  ],
})
export class ProrogationPermisModule {}
