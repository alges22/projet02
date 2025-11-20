import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { GuestGuard } from '../core/guard/guest.guard';
import { LoginComponent } from './login/login.component';
import { ForgotPasswordComponent } from './forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import { LogoutComponent } from './logout/logout.component';
import { ServiceDetailsComponent } from './service-details/service-details.component';
import { RegisterComponent } from './register/register.component';
import { VueBuilderComponent } from './vue-builder/vue-builder.component';
import { ConfirmAccountComponent } from './confirm-accoount/confirm-account.component';
import { MoniteurLoginComponent } from './moniteur-login/moniteur-login.component';

const routes: Routes = [
  {
    path: '',
    loadChildren: () =>
      import('./static/static.module').then((m) => m.StaticModuleModule),
  },
  {
    path: 'connexion',
    canActivate: [GuestGuard],
    component: LoginComponent,
    title: 'Connexion',
  },
  {
    path: 'connexion/monitoring',
    canActivate: [GuestGuard],
    component: MoniteurLoginComponent,
    title: 'Connexion des moniteurs',
  },
  {
    path: 'inscription',
    component: RegisterComponent,
    title: "Demande d'agrément",
  },

  {
    path: 'demande-agrement',
    component: RegisterComponent,
    title: "Demande d'agrément",
  },
  {
    path: 'demande-agrement/:id',
    component: RegisterComponent,
    title: "Modification d'agrément",
  },

  {
    path: 'logout',
    component: LogoutComponent,
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class PublicRoutingModule {}
