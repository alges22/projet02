import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { RemplacementPermisRoutingModule } from './remplacement-permis-routing.module';
import { RemplacementPermisComponent } from './remplacement-permis.component';
import { InitRemplacementPermisComponent } from './init-remplacement-permis/init-remplacement-permis.component';
import { CoreModule } from 'src/app/core/core.module';
import { FormsModule } from '@angular/forms';
import { RejetRemplacementPermisComponent } from './rejet-remplacement-permis/rejet-remplacement-permis.component';
import { ValidateRemplacementPermisComponent } from './validate-remplacement-permis/validate-remplacement-permis.component';
import { RemplacementFicheComponent } from './components/remplacement-fiche/remplacement-fiche.component';
import { NgxPaginationModule } from 'ngx-pagination';

@NgModule({
  declarations: [
    RemplacementPermisComponent,
    InitRemplacementPermisComponent,
    RejetRemplacementPermisComponent,
    ValidateRemplacementPermisComponent,
    RemplacementFicheComponent,
  ],
  imports: [
    CommonModule,
    RemplacementPermisRoutingModule,
    CoreModule,
    FormsModule,
    NgxPaginationModule,
  ],
})
export class RemplacementPermisModule {}
