import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { EchangePermisRoutingModule } from './echange-permis-routing.module';
import { EchangePermisComponent } from './echange-permis.component';
import { ValidateEchangePermisComponent } from './validate-echange-permis/validate-echange-permis.component';
import { DemandeEchangePermisComponent } from './demande-echange-permis/demande-echange-permis.component';
import { RejetEchangePermisComponent } from './rejet-echange-permis/rejet-echange-permis.component';
import { CoreModule } from 'src/app/core/core.module';
import { NgxPaginationModule } from 'ngx-pagination';
import { EchangePermisFicheComponent } from './components/echange-permis-fiche/echange-permis-fiche.component';
import { FormsModule } from '@angular/forms';

@NgModule({
  declarations: [
    EchangePermisComponent,
    ValidateEchangePermisComponent,
    DemandeEchangePermisComponent,
    RejetEchangePermisComponent,
    EchangePermisFicheComponent,
  ],
  imports: [
    CoreModule,
    CommonModule,
    EchangePermisRoutingModule,
    NgxPaginationModule,
    FormsModule,
  ],
})
export class EchangePermisModule {}
