import { HomeComponent } from './home/home.component';
import { CoreModule } from './../core/core.module';
import { FormsModule } from '@angular/forms';
import { LoginComponent } from './login/login.component';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PublicRoutingModule } from './public-routing.module';
import { LogoutComponent } from './logout/logout.component';
import { PublicComponent } from './public.component';
import { ForgotPasswordComponent } from './forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import { RecaptchaV3Module } from 'ng-recaptcha';

@NgModule({
  declarations: [
    LogoutComponent,
    LoginComponent,
    PublicComponent,
    HomeComponent,
    ForgotPasswordComponent,
    ResetPasswordComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    PublicRoutingModule,
    RecaptchaV3Module,
    CoreModule,
  ],
})
export class PublicModule {}
