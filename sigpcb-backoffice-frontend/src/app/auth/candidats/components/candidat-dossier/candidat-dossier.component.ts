import { Component, Input } from '@angular/core';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-candidat-dossier',
  templateUrl: './candidat-dossier.component.html',
  styleUrls: ['./candidat-dossier.component.scss'],
})
export class CandidatDossierComponent {
  @Input() dossier: any = null;

  get createdAt() {
    return this.dossier.created_at;
  }

  get status() {
    const state = this.dossier.state;

    if (state === 'success') {
      return {
        class: 'success',
        text: 'Validé',
      };
    }

    if (state === 'pending') {
      return {
        class: 'warning',
        text: 'En cours',
      };
    }

    if (state === 'failed') {
      return {
        class: 'danger',
        text: 'Echec',
      };
    }

    return {
      class: 'dark',
      text: 'Fermé',
    };
  }

  asset(path?: string) {
    return environment.candidat.asset + path;
  }

  showGroupSanguin() {
    $('#groupe-sanguin' + this.dossier.id).modal('show');
  }
}
