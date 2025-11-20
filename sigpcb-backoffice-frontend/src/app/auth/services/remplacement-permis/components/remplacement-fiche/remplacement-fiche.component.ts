import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import {
  AuthenticitePermis,
  DuplicataRemplacement,
} from 'src/app/core/interfaces/services';
import { environment } from 'src/environments/environment';
type State = 'validate' | 'rejected' | 'failed';
@Component({
  selector: 'app-remplacement-fiche',
  templateUrl: './remplacement-fiche.component.html',
  styleUrls: ['./remplacement-fiche.component.scss'],
})
export class RemplacementFicheComponent {
  candidat: any;
  @Output('validate') validateEvent = new EventEmitter<{
    data: DuplicataRemplacement;
    state: State;
  }>();
  @Input('data') data: DuplicataRemplacement | null = null;
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
