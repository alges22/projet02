import { Component, Input } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-back',
  templateUrl: './back.component.html',
  styleUrls: ['./back.component.scss'],
})
export class BackComponent {
  constructor(private router: Router) {}
  @Input() url = '';
  @Input() label = 'Retour';
  @Input() show = true;
  /**
   * Retourne à la précédente
   */
  back(event: Event) {
    event.preventDefault();
    if (this.url.length) {
      this.router.navigate([this.url]);
    } else {
      window.history.back();
    }
  }
}
