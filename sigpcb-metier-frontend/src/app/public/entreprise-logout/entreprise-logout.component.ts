import { Component } from '@angular/core';
import { AuthService } from 'src/app/core/services/auth.service';

@Component({
  selector: 'app-entreprise-logout',
  templateUrl: './entreprise-logout.component.html',
  styleUrls: ['./entreprise-logout.component.scss'],
})
export class EntrepriseLogoutComponent {
  constructor(private authService: AuthService) {}
  ngOnInit(): void {
    this.authService.logout();
    window.location.href = '/entreprise/';
  }
}
