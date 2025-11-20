import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AuthenticitePermisRoutingModule } from './authenticite-permis-routing.module';
import { AuthenticitePermisComponent } from './authenticite-permis.component';
import { DemandeAuthPermisComponent } from './demande-auth-permis/demande-auth-permis.component';
import { CoreModule } from 'src/app/core/core.module';
import { FormsModule } from '@angular/forms';
import { AuthPermisFicheComponent } from './components/auth-permis-fiche/auth-permis-fiche.component';
import { NgxPaginationModule } from 'ngx-pagination';
import { RejetAuthPermisComponent } from './rejet-auth-permis/rejet-auth-permis.component';
import { ValidateAuthPermisComponent } from './validate-auth-permis/validate-auth-permis.component';

@NgModule({
  declarations: [
    AuthenticitePermisComponent,
    DemandeAuthPermisComponent,
    AuthPermisFicheComponent,
    RejetAuthPermisComponent,
    ValidateAuthPermisComponent,
  ],
  imports: [
    CommonModule,
    AuthenticitePermisRoutingModule,
    CoreModule,
    FormsModule,
    NgxPaginationModule,
  ],
})
export class AuthenticitePermisModule {}
