import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AgreementRoutingModule } from './agreement-routing.module';
import { AgreementComponent } from './agreement.component';
import { NgxPaginationModule } from 'ngx-pagination';
import { CoreModule } from 'src/app/core/core.module';
import { ANouvelleDemandesComponent } from './a-nouvelle-demandes/a-nouvelle-demandes.component';
import { FormsModule } from '@angular/forms';
import { AgreementRejetesComponent } from './agreement-rejetes/agreement-rejetes.component';
import { AgreementValidesComponent } from './agreement-valides/agreement-valides.component';
import { AgreementFicheComponent } from './components/agreement-fiche/agreement-fiche.component';

@NgModule({
  declarations: [
    AgreementComponent,
    ANouvelleDemandesComponent,
    AgreementFicheComponent,
    AgreementRejetesComponent,
    AgreementValidesComponent,
  ],
  imports: [
    CommonModule,
    AgreementRoutingModule,
    NgxPaginationModule,
    CoreModule,
    FormsModule,
  ],
})
export class AgreementModule {}
