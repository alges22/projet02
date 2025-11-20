import { CoreModule } from './../core/core.module';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AuthRoutingModule } from './auth-routing.module';
import { AuthNavbarComponent } from './auth-navbar/auth-navbar.component';
import { AuthSidebarComponent } from './auth-sidebar/auth-sidebar.component';
import { AuthComponent } from './auth.component';

@NgModule({
  declarations: [AuthNavbarComponent, AuthSidebarComponent, AuthComponent],
  imports: [CommonModule, CoreModule, AuthRoutingModule],
})
export class AuthModule {}
