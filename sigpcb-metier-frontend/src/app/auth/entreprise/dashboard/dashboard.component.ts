import { Component } from '@angular/core';
import { EntrepriseButtonService } from 'src/app/core/prestation/entreprise-button.service';
import { EntrepriseButton } from 'src/app/core/prestation/interface/entreprise-button';
import { AuthService } from 'src/app/core/services/auth.service';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss'],
})
export class DashboardComponent {
  buttons: EntrepriseButton[] = [];
  user: any;
  constructor(
    private entrepriseButtonService: EntrepriseButtonService,
    private authService: AuthService // private translate: TranslateService, // private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.user = this.authService.storageService().get('auth-entreprise');
    // Obtenir les prestationsTemp depuis le service entrepriseButtonService
    this.buttons = this.entrepriseButtonService.getServices();
  }
}
