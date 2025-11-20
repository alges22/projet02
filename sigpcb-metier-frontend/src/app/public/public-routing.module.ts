import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { GuestGuard } from '../core/guard/guest.guard';
import { LoginComponent } from './login/login.component';
import { LogoutComponent } from './logout/logout.component';
import { DevenirExaminateurComponent } from './devenir-examinateur/devenir-examinateur.component';
import { SuivreDemandeComponent } from './suivre-demande/suivre-demande.component';
import { EditDevenirExaminateurComponent } from './edit-devenir-examinateur/edit-devenir-examinateur.component';
import { EntrepriseLoginComponent } from './entreprise-login/entreprise-login.component';
import { GuestEntrepriseGuard } from '../core/guard/guest-entreprise.guard';
import { EntrepriseLogoutComponent } from './entreprise-logout/entreprise-logout.component';
import { MoniteurLoginComponent } from './moniteur-login/moniteur-login.component';
import { GuestMoniteurGuard } from '../core/guard/guest-moniteur.guard';
import { DevenirMoniteurComponent } from './devenir-moniteur/devenir-moniteur.component';
import { EditDevenirMoniteurComponent } from './edit-devenir-moniteur/edit-devenir-moniteur.component';
import { SuivreMoniteurComponent } from './suivre-moniteur/suivre-moniteur.component';
import { MoniteurLogoutComponent } from './moniteur-logout/moniteur-logout.component';

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
  },
  {
    path: 'devenir-examinateur',
    component: DevenirExaminateurComponent,
  },
  {
    path: 'devenir-examinateur/rejets/:rejetId',
    component: EditDevenirExaminateurComponent,
  },
  {
    path: 'suivre-demande',
    // canActivate: [GuestGuard],
    component: SuivreDemandeComponent,
  },
  {
    path: 'entreprise/connexion',
    canActivate: [GuestEntrepriseGuard],
    component: EntrepriseLoginComponent,
  },
  {
    path: 'entreprise/logout',
    component: EntrepriseLogoutComponent,
  },
  {
    path: 'logout',
    component: LogoutComponent,
  },

  //recrutement moniteur
  {
    path: 'moniteur/connexion',
    canActivate: [GuestMoniteurGuard],
    component: MoniteurLoginComponent,
  },
  {
    path: 'devenir-moniteur',
    component: DevenirMoniteurComponent,
  },
  {
    path: 'devenir-moniteur/rejets/:rejetId',
    component: EditDevenirMoniteurComponent,
  },
  {
    path: 'suivre-moniteur',
    component: SuivreMoniteurComponent,
  },
  {
    path: 'moniteur/logout',
    component: MoniteurLogoutComponent,
  },
  {
    path: 'moniteur',
    loadChildren: () =>
      import('./static-moniteur/static-moniteur.module').then(
        (m) => m.StaticMoniteurModule
      ),
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class PublicRoutingModule {}
