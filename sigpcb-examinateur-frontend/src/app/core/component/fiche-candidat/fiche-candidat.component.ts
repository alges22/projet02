import { Component, Input, OnInit } from '@angular/core';
import { DossierSession } from '../../interfaces/dossier-candidat';
import { Suivi } from '../../interfaces/monitoring';

@Component({
  selector: 'app-fiche-candidat',
  templateUrl: './fiche-candidat.component.html',
  styleUrls: ['./fiche-candidat.component.scss'],
})
export class FicheCandidatComponent implements OnInit {
  openFiche = false;
  openDetails = false;
  chapites: any[] = [];
  @Input('candidat') data: DossierSession | Suivi | null = null;
  candidat: any | null = null; // doit provenir d'un component parent
  @Input('download-fiche') fiche = false;
  parcours_state = 'En cours' as 'En cours' | 'échec' | 'Réussi';
  restriction: {
    id: number;
    name: string;
  } | null = null;
  page = 'dossier' as 'monitoring' | 'dossier';
  ngOnInit(): void {
    if (this.data) {
      this.candidat = this.data.candidat;
    }
  }
  toggleFiche() {
    this.openFiche = !this.openFiche;
    this.openDetails = false;
    if (this.openFiche) {
      let data: Suivi = this.data as Suivi;
      this.chapites = data.chapitres;
    }
  }

  toggleDetails() {
    this.openDetails = !this.openDetails;
    this.openFiche = false;
    if (this.openDetails) {
      if (this.data) {
        switch (this.data.state) {
          case 'success':
            this.parcours_state = 'Réussi';
            break;
          case 'failed':
            this.parcours_state = 'échec';
            break;
          default:
            this.parcours_state = 'En cours';
            break;
        }
      }
    }
  }
}
