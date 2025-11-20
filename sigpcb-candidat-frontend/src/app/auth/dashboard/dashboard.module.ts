import { NgModule } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';

import { DashboardRoutingModule } from './dashboard-routing.module';
import { DashboardComponent } from './dashboard.component';
import { CoreModule } from 'src/app/core/core.module';
import { DashHomeComponent } from './dash-home/dash-home.component';
import { InscriptionExamenComponent } from './demandes/inscription-examen/inscription-examen.component';
import { PremierInscriptionComponent } from './demandes/premier-inscription/premier-inscription.component';
import { FormsModule } from '@angular/forms';
import { InscriptionConduiteComponent } from './demandes/inscription-conduite/inscription-conduite.component';

import { PdfViewerModule } from 'ng2-pdf-viewer';
import { EditInscriptionComponent } from './demandes/edit-inscription/edit-inscription.component';
import { EditDossierComponent } from './demandes/edit-dossier/edit-dossier.component';
import { InscriptionAbsenceComponent } from './demandes/inscription-absence/inscription-absence.component';

@NgModule({
  declarations: [
    DashboardComponent,
    DashHomeComponent,
    InscriptionExamenComponent,
    PremierInscriptionComponent,
    InscriptionConduiteComponent,
    EditInscriptionComponent,
    EditDossierComponent,
    InscriptionAbsenceComponent,
  ],
  imports: [
    CommonModule,
    DashboardRoutingModule,
    FormsModule,
    CoreModule,
    PdfViewerModule,
  ],
  exports: [InscriptionExamenComponent],
  providers: [DatePipe],
})
export class DashboardModule {}
