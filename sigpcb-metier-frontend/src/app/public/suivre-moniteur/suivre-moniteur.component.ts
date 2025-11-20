import { Component } from '@angular/core';
import { RecrutementMoniteurParcours } from 'src/app/core/interfaces/recrutement-moniteur';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutementMoniteurService } from 'src/app/core/services/recrutement-moniteur.service';

@Component({
  selector: 'app-suivre-moniteur',
  templateUrl: './suivre-moniteur.component.html',
  styleUrls: ['./suivre-moniteur.component.scss'],
})
export class SuivreMoniteurComponent {
  cardOpendedIndex = null as null | number;
  parcours: { service: string; liste: RecrutementMoniteurParcours[] }[] = [];
  latestParcours: RecrutementMoniteurParcours[] = [];
  constructor(
    private recrutementMoniteurService: RecrutementMoniteurService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.errorHandler.startLoader();
    this.recrutementMoniteurService
      .getDemandeParcours()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.transform(response.data);
        this.errorHandler.stopLoader();
      });
  }
  openCard(index: number) {
    if (this.cardOpendedIndex === index) {
      this.cardOpendedIndex = null;
    } else {
      this.cardOpendedIndex = index;
    }
  }

  private transform(parcours: any) {
    this.parcours = parcours.map((tableau: RecrutementMoniteurParcours[]) => {
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

  private createLatestParcours(parcours: RecrutementMoniteurParcours) {
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
}
