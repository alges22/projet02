import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { StaticMoniteurRoutingModule } from './static-moniteur-routing.module';
import { StaticMoniteurComponent } from './static-moniteur.component';
import { FormsModule } from '@angular/forms';
import { CoreModule } from 'src/app/core/core.module';
import { WelcomeComponent } from './welcome/welcome.component';

@NgModule({
  declarations: [StaticMoniteurComponent, WelcomeComponent],
  imports: [CommonModule, StaticMoniteurRoutingModule, CoreModule, FormsModule],
})
export class StaticMoniteurModule {}
