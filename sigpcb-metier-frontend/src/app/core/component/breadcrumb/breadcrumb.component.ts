import { Component, Input } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-breadcrumb',
  templateUrl: './breadcrumb.component.html',
  styleUrls: ['./breadcrumb.component.scss'],
})
export class BreadcrumbComponent {
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
