import { Component, OnInit } from '@angular/core';
import { StorageService } from '../../services/storage.service';

@Component({
  selector: 'app-consent-cookie',
  templateUrl: './consent-cookie.component.html',
  styleUrls: ['./consent-cookie.component.scss'],
})
export class ConsentCookieComponent implements OnInit {
  consentGiven: boolean = true;
  delay: number = 2000; // Délai en millisecondes (par défaut 2000ms)

  constructor(private storage: StorageService) {}

  ngOnInit() {
    // Vérifiez si le consentement n'a pas encore été donné après le délai spécifié
    if (!this.consentGiven) {
      setTimeout(() => {
        // Afficher la boîte de consentement après le délai
        this.showConsent();
      }, this.delay);
    }
  }

  showConsent() {
    $('#cookie-consent').addClass('cookie-consent-visible');
  }

  giveConsent() {
    // Mettez à jour la propriété de consentement
    this.consentGiven = true;
    // Enregistrez le consentement dans le navigateur
    this.storage.store('consent.cookie', 'true');
  }
}
