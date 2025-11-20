import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { DossiersRoutingModule } from './dossiers-routing.module';
import { DossiersComponent } from './dossiers.component';
import { NouvellesDemandesComponent } from './nouvelles-demandes/nouvelles-demandes.component';
import { CoreModule } from 'src/app/core/core.module';
import { CandidatParcoursInfoComponent } from './components/candidat-parcours-info/candidat-parcours-info.component';
import { FormsModule } from '@angular/forms';
import { DossierValidesComponent } from './dossier-valides/dossier-valides.component';
import { DossierRejectedComponent } from './dossier-rejected/dossier-rejected.component';
import { NgxPaginationModule } from 'ngx-pagination';

@NgModule({
  declarations: [
    DossiersComponent,
    NouvellesDemandesComponent,
    CandidatParcoursInfoComponent,
    DossierValidesComponent,
    DossierRejectedComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    NgxPaginationModule,
    CoreModule,
    DossiersRoutingModule,
  ],
})
export class DossiersModule {}
