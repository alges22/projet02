import { Component, Input } from '@angular/core';
import { Prestation } from '../interface/prestation';
import { AuthService } from '../../services/auth.service';
import { TranslateService } from '@ngx-translate/core';
import { PrestationService } from '../prestation.service';
import { Router } from '@angular/router';
type Page = 'user-type' | 'todo-action' | 'prepare-my-self';

@Component({
  selector: 'app-service-card',
  templateUrl: './service-card.component.html',
  styleUrls: ['./service-card.component.scss'],
})
export class ServiceCardComponent {
  @Input() prestation: Prestation | null = null;
  prestationTemp: any;
  page: Page = 'user-type';
  constructor(
    private authService: AuthService,
    private prestationService: PrestationService,
    private translate: TranslateService,
    private router: Router
  ) {}

  userConnected() {
    if (this.authService.checked()) {
      return true;
    }
    return false;
  }

  demande(slug: string): void {
    if (this.authService.checked()) {
      this.prestationTemp = this.prestationService.getService(slug);
      if (this.prestationTemp) {
        this.translate
          .get(this.prestationTemp.title)
          .subscribe((translation: string) => {
            this.prestation = { ...this.prestationTemp, title: translation };
            this.page = 'user-type';
            $('#prestation-demande').modal('show');
            this.prestationService.emitModalOpenedEvent();
          });
      }
    } else {
      this.router.navigate(['/dashboard/']);
    }
  }
}
