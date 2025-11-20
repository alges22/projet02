import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ProgrammationRoutingModule } from './programmation-routing.module';
import { ProgrammationComponent } from './programmation.component';
import { CoreModule } from 'src/app/core/core.module';
import { CodesComponent } from './codes/codes.component';
import { ConduitesComponent } from './conduites/conduites.component';
import { FormsModule } from '@angular/forms';

@NgModule({
  declarations: [ProgrammationComponent, CodesComponent, ConduitesComponent],
  imports: [CommonModule, CoreModule, FormsModule, ProgrammationRoutingModule],
})
export class ProgrammationModule {}
