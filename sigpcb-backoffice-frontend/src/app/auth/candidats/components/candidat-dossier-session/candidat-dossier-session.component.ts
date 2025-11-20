import { Component, Input } from '@angular/core';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-candidat-dossier-session',
  templateUrl: './candidat-dossier-session.component.html',
  styleUrls: ['./candidat-dossier-session.component.scss'],
})
export class CandidatDossierSessionComponent {
  @Input() data: any = null;

  ngOnInit() {}
  get created_at() {
    return this.data.created_at;
  }
  get abandon() {
    if (this.data.abandoned) {
      return 'Oui';
    }
    return 'Non';
  }

  get status() {
    if (this.data.state === 'pending') {
      return 'Monitoring';
    }
    if (this.data.state === 'payment') {
      return 'En attente de validation';
    }

    if (this.data.state === 'validate') {
      return 'Validé';
    }

    return 'Pré-inscription';
  }

  get closed() {
    return this.data.closed ? 'Fermé' : 'Ouvert';
  }

  showFicheMedical() {
    $('#fiche' + this.data.id).modal('show');
  }

  asset(path?: string) {
    return environment.candidat.asset + path;
  }

  get restrictions() {
    return this.data.restrictionss ?? [];
  }
}
