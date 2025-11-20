import { Component } from '@angular/core';
import { AuthMoniteurService } from 'src/app/core/services/auth-moniteur.service';

@Component({
  template: `<div
    class="d-flex justify-content-center align-items-center vh-100 text-center"
  >
    Déconnexion ...
  </div>`,
})
export class MoniteurLogoutComponent {
  constructor(private authMoniteurService: AuthMoniteurService) {}
  ngOnInit(): void {
    this.authMoniteurService.logout();
    window.location.href = '/moniteur/connexion';
  }
}
