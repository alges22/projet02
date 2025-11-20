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
import { AbsenceConduiteComponent } from './demandes/absence-conduite/absence-conduite.component';
import { AbsenceConduiteJustifComponent } from './demandes/absence-conduite-justif/absence-conduite-justif.component';
import { AbsenceCodeJustifComponent } from './demandes/absence-code-justif/absence-code-justif.component';

@NgModule({
  declarations: [
    DashboardComponent,
    DashHomeComponent,
    InscriptionExamenComponent,
    PremierInscriptionComponent,
    InscriptionConduiteComponent,
    AbsenceConduiteComponent,
    AbsenceConduiteJustifComponent,
    AbsenceCodeJustifComponent,
  ],
  imports: [CommonModule, DashboardRoutingModule, FormsModule, CoreModule],
  exports: [InscriptionExamenComponent],
  providers: [DatePipe],
})
export class DashboardModule {}
