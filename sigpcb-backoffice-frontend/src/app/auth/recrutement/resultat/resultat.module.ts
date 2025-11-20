import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ResultatRoutingModule } from './resultat-routing.module';
import { ResultatComponent } from './resultat.component';
import { ResultatFinalComponent } from './resultat-final/resultat-final.component';
import { FormsModule } from '@angular/forms';
import { CoreModule } from 'src/app/core/core.module';

@NgModule({
  declarations: [ResultatComponent, ResultatFinalComponent],
  imports: [CommonModule, CoreModule, FormsModule, ResultatRoutingModule],
})
export class ResultatModule {}
