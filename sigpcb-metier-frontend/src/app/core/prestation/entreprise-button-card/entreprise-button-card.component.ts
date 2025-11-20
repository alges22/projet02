import { Component, Input } from '@angular/core';
import { EntrepriseButtonService } from '../entreprise-button.service';
import { EntrepriseButton } from '../interface/entreprise-button';
import { Router } from '@angular/router';

@Component({
  selector: 'app-entreprise-button-card',
  templateUrl: './entreprise-button-card.component.html',
  styleUrls: ['./entreprise-button-card.component.scss'],
})
export class EntrepriseButtonCardComponent {
  @Input() entrepriseButton: any;
  prestationTemp: any;
  constructor(
    private entrepriseButtonService: EntrepriseButtonService,
    private router: Router
  ) {}

  // userConnected() {
  //   if (this.authService.checked()) {
  //     return true;
  //   }
  //   return false;
  // }

  demande(slug: string): void {
    this.entrepriseButton = this.entrepriseButtonService.getService(slug);
    // if (this.prestationTemp) {
    //   this.translate
    //     .get(this.prestationTemp.title)
    //     .subscribe((translation: string) => {
    //       this.entrepriseButton = { ...this.prestationTemp, title: translation };
    //       $('#prestation-demande').modal('show');
    //     });
    // }
  }
}
