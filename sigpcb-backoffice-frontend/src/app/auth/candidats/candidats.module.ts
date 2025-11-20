import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { CandidatsRoutingModule } from './candidats-routing.module';
import { CandidatsComponent } from './candidats.component';
import { CoreModule } from 'src/app/core/core.module';
import { FormsModule } from '@angular/forms';
import { CandidatHomeComponent } from './candidat-home/candidat-home.component';
import { NgxPaginationModule } from 'ngx-pagination';
import { CandidatHistoricComponent } from './candidat-historic/candidat-historic.component';
import { CandidatDossierComponent } from './components/candidat-dossier/candidat-dossier.component';
import { CandidatDossierSessionComponent } from './components/candidat-dossier-session/candidat-dossier-session.component';
import { CandidatEditComponent } from './candidat-edit/candidat-edit.component';

@NgModule({
  declarations: [CandidatsComponent, CandidatHomeComponent, CandidatHistoricComponent, CandidatDossierComponent, CandidatDossierSessionComponent, CandidatEditComponent],
  imports: [
    CommonModule,
    CandidatsRoutingModule,
    NgxPaginationModule,
    CoreModule,
    FormsModule,
  ],
})
export class CandidatsModule {}
