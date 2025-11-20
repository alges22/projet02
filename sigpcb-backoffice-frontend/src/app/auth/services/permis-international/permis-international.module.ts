import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { PermisInternationalRoutingModule } from './permis-international-routing.module';
import { PermisInternationalComponent } from './permis-international.component';
import { DemandePermisInterComponent } from './demande-permis-inter/demande-permis-inter.component';
import { RejetPermisInterComponent } from './rejet-permis-inter/rejet-permis-inter.component';
import { ValidatePermisInterComponent } from './validate-permis-inter/validate-permis-inter.component';
import { CoreModule } from 'src/app/core/core.module';
import { PermisInterFicheComponent } from './components/permis-inter-fiche/permis-inter-fiche.component';
import { FormsModule } from '@angular/forms';
import { NgxPaginationModule } from 'ngx-pagination';

@NgModule({
  declarations: [
    PermisInternationalComponent,
    DemandePermisInterComponent,
    RejetPermisInterComponent,
    ValidatePermisInterComponent,
    PermisInterFicheComponent,
  ],
  imports: [
    CoreModule,
    CommonModule,
    PermisInternationalRoutingModule,
    FormsModule,
    NgxPaginationModule,
  ],
})
export class PermisInternationalModule {}
