import { Component, Input } from '@angular/core';
import { Suivi } from '../../interfaces/monitoring';
import { Candidat } from '../../interfaces/dossier-candidat';

@Component({
  selector: 'suivi-details',
  templateUrl: './suivi-details.component.html',
  styleUrls: ['./suivi-details.component.scss'],
})
export class SuiviDetailsComponent {
  openFiche = false;
  openDetails = false;
  chapites: any[] = [];
  @Input('candidat') data: Suivi | null = null;
  candidat: Candidat | null = null;
  dossier: any | null = null; // doit provenir d'un component parent
  @Input('download-fiche') fiche = false;
  onLoadDossier = false;
  parcours_state = 'En cours' as 'En cours' | 'échec' | 'Réussi';
  constructor() {}
  ngOnInit(): void {
    if (this.data) {
      this.candidat = this.data.candidat;
      this.dossier = this.data.dossier;
      this.chapites = this.data.chapitres;
    }
  }
  toggleFiche() {
    this.openFiche = !this.openFiche;
    this.openDetails = false;
  }

  toggleDetails() {
    this.openDetails = !this.openDetails;
    this.openFiche = false;
  }
}
