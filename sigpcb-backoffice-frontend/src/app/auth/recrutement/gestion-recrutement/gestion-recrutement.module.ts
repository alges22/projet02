import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { GestionRecrutementRoutingModule } from './gestion-recrutement-routing.module';
import { GestionRecrutementComponent } from './gestion-recrutement.component';
import { GrDemandesComponent } from './gr-demandes/gr-demandes.component';
import { GrDemandesRejectedComponent } from './gr-demandes-rejected/gr-demandes-rejected.component';
import { GrDemandesValidateComponent } from './gr-demandes-validate/gr-demandes-validate.component';
import { CoreModule } from 'src/app/core/core.module';
import { FormsModule } from '@angular/forms';
import { NgxPaginationModule } from 'ngx-pagination';

@NgModule({
  declarations: [
    GestionRecrutementComponent,
    GrDemandesComponent,
    GrDemandesRejectedComponent,
    GrDemandesValidateComponent,
  ],
  imports: [
    CommonModule,
    GestionRecrutementRoutingModule,
    CoreModule,
    FormsModule,
    NgxPaginationModule,
  ],
})
export class GestionRecrutementModule {}
