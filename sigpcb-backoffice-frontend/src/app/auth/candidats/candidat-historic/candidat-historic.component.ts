import { Component } from '@angular/core';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-candidat-historic',
  templateUrl: './candidat-historic.component.html',
  styleUrls: ['./candidat-historic.component.scss'],
})
export class CandidatHistoricComponent {
  permisHistories: any[] = [];
  info: any | null = null;
  constructor(
    private candidatService: CandidatService,
    private route: ActivatedRoute
  ) {}

  ngOnInit() {
    const npi = this.route.snapshot.paramMap.get('npi');
    if (npi) {
      this.get(npi);
    }
  }
  private get(npi: string) {
    this.candidatService.history(npi).subscribe((response) => {
      const data = response.data.historiques;
      this.info = response.data.candidat; // data est un tableau d'objets
      const results: any[] = [];

      // Utilisation d'un Map pour regrouper les éléments par dossier_candidat_id
      const grouped = new Map();

      for (const dossierSession of data) {
        const dossierId = dossierSession.dossier_candidat_id;

        // Si le dossier_candidat_id existe déjà dans le Map, on ajoute l'objet au tableau
        if (grouped.has(dossierId)) {
          grouped.get(dossierId).push(dossierSession);
        } else {
          grouped.set(dossierId, [dossierSession]);
        }
      }

      // Convertir le Map en tableau d'objets avec la structure souhaitée
      grouped.forEach((dossierSessions, dossier_candidat_id) => {
        const first = dossierSessions[0];
        results.push({
          dossier_candidat_id,
          dossierSessions,
          categoriePermis: first ? first.categorie_permis : null,
          dossier: first ? first.dossier : null,
        });
      });
      this.permisHistories = results;
    });
  }
}
