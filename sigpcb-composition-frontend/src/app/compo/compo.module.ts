import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { CompoRoutingModule } from './compo-routing.module';
import { ResultsComponent } from './results/results.component';
import { InformationsComponent } from './informations/informations.component';
import { StartCompoComponent } from './start-compo/start-compo.component';
import { QuestionsComponent } from './questions/questions.component';
import { ThanksComponent } from './thanks/thanks.component';
import { CompoComponent } from './compo.component';
import { CoreModule } from '../core/core.module';

@NgModule({
  declarations: [
    ResultsComponent,
    InformationsComponent,
    StartCompoComponent,
    QuestionsComponent,
    ThanksComponent,
    CompoComponent,
  ],
  imports: [CommonModule, CoreModule, CompoRoutingModule],
})
export class CompoModule {}
