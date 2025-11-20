import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { LicencesRoutingModule } from './licences-routing.module';
import { LicencesComponent } from './licences.component';
import { DemandeLicenceComponent } from './demande-licence/demande-licence.component';
import { CoreModule } from 'src/app/core/core.module';
import { FormsModule } from '@angular/forms';

@NgModule({
  declarations: [LicencesComponent, DemandeLicenceComponent],
  imports: [CommonModule, LicencesRoutingModule, FormsModule, CoreModule],
})
export class LicencesModule {}
