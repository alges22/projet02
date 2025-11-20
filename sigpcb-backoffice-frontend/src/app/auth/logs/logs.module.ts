import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { LogsRoutingModule } from './logs-routing.module';
import { LogsComponent } from './logs.component';
import { DeveloperModeComponent } from './developer-mode/developer-mode.component';
import { CoreModule } from 'src/app/core/core.module';
import { NgxPaginationModule } from 'ngx-pagination';

@NgModule({
  declarations: [LogsComponent, DeveloperModeComponent],
  imports: [CommonModule, LogsRoutingModule, NgxPaginationModule, CoreModule],
})
export class LogsModule {}
