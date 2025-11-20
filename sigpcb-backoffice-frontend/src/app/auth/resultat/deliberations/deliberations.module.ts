import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { DeliberationsRoutingModule } from './deliberations-routing.module';
import { DeliberationsComponent } from './deliberations.component';
import { CoreModule } from 'src/app/core/core.module';
import { ResultDelibAdmisComponent } from './result-delib-admis/result-delib-admis.component';
import { FormsModule } from '@angular/forms';

@NgModule({
  declarations: [DeliberationsComponent, ResultDelibAdmisComponent],
  imports: [CommonModule, DeliberationsRoutingModule, FormsModule, CoreModule],
})
export class DeliberationsModule {}
