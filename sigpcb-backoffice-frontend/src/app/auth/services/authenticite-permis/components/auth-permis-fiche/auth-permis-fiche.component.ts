import { Component, EventEmitter, Input, Output } from '@angular/core';
import { AuthenticitePermis } from 'src/app/core/interfaces/services';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { environment } from 'src/environments/environment';
type State = 'validate' | 'rejected' | 'failed';
@Component({
  selector: 'app-auth-permis-fiche',
  templateUrl: './auth-permis-fiche.component.html',
  styleUrls: ['./auth-permis-fiche.component.scss'],
})
export class AuthPermisFicheComponent {
  candidat: any;
  @Output('validate') validateEvent = new EventEmitter<{
    data: AuthenticitePermis;
    state: State;
  }>();
  @Input('data') data: AuthenticitePermis | null = null;
  previewUrl = '';
  categorie_permis: CategoryPermis | null = null;
  selectedRestrictionIds: number[] = [];
  constructor() {}

  @Input() page = 'pending' as 'validate' | 'rejected' | 'pending';

  onValidate() {
    if (this.data) {
      this.validateEvent.emit({
        data: this.data,
        state: 'validate',
      });
    }
  }

  onRejected() {
    if (this.data) {
      this.validateEvent.emit({
        data: this.data,
        state: 'rejected',
      });
    }
  }
  ngOnInit(): void {
    this.candidat = this.data?.demandeur_info;
  }

  openSuiviModal(url?: string) {
    if (url) {
      this.previewUrl = url;
    }
    $(`#suivi-${this.data?.id}`).modal('show');
  }

  assets(path?: string) {
    return environment.candidat.asset + path;
  }
}
