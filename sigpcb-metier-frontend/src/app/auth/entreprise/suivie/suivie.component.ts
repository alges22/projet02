import { Component } from '@angular/core';
import { EntrepriseParcours } from 'src/app/core/interfaces/entreprise-parcours';
import { EntrepriseService } from 'src/app/core/services/entreprise.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-suivie',
  templateUrl: './suivie.component.html',
  styleUrls: ['./suivie.component.scss'],
})
export class SuivieComponent {
  cardOpendedIndex = null as null | number;
  parcours: { service: string; liste: EntrepriseParcours[] }[] = [];
  latestParcours: EntrepriseParcours[] = [];
  demandes: any[] = [];
  constructor(
    private entrepriseService: EntrepriseService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.getSuivies();
  }
  openCard(index: number) {
    if (this.cardOpendedIndex === index) {
      this.cardOpendedIndex = null;
    } else {
      this.cardOpendedIndex = index;
    }
  }

  getSuivies() {
    this.errorHandler.startLoader();
    this.entrepriseService
      .getDemandeParcours()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.demandes = response.data;
        this.errorHandler.stopLoader();
      });
  }

  private transform(parcours: any) {
    this.parcours = parcours.map((tableau: EntrepriseParcours[]) => {
      const service = tableau[0].service;

      const latest = tableau[0];

      if (latest) {
        this.createLatestParcours(latest);
      }
      return {
        service: service,
        liste: tableau,
      };
    });
  }

  private createLatestParcours(parcours: EntrepriseParcours) {
    const found = this.latestParcours.find((p) => p.id == parcours.id);

    if (!found) {
      this.latestParcours.push(parcours);
    }
  }

  createRejetUrl(rejetId: number): string {
    if (rejetId) {
      return '';
    } else {
      return '';
    }
  }

  base_url(path: string) {
    return environment.entreprise.base_url + path;
  }
}
