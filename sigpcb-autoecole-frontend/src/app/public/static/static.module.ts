import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { StaticRoutingModule } from './static-routing.module';
import { StaticComponent } from './static.component';
import { WelcomeComponent } from './welcome/welcome.component';
import { CoreModule } from 'src/app/core/core.module';
import { FormsModule } from '@angular/forms';

@NgModule({
  declarations: [StaticComponent, WelcomeComponent],
  imports: [CommonModule, StaticRoutingModule, CoreModule, FormsModule],
})
export class StaticModuleModule {}
