import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { RecrutementParcours } from 'src/app/core/interfaces/recrutement';
import { AuthService } from 'src/app/core/services/auth.service';
import { EserviceParcoursService } from 'src/app/core/services/eservice-parcours.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutementExaminateurService } from 'src/app/core/services/recrutement-examinateur.service';

@Component({
  selector: 'app-suivre-demande',
  templateUrl: './suivre-demande.component.html',
  styleUrls: ['./suivre-demande.component.scss'],
})
export class SuivreDemandeComponent {
  cardOpendedIndex = null as null | number;
  parcours: { service: string; liste: RecrutementParcours[] }[] = [];
  latestParcours: RecrutementParcours[] = [];
  constructor(
    private recrutementParcoursService: RecrutementExaminateurService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.errorHandler.startLoader();
    this.recrutementParcoursService
      .getDemandeParcours()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.transform(response.data);
        console.log(this.parcours);
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
    this.parcours = parcours.map((tableau: RecrutementParcours[]) => {
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

  private createLatestParcours(parcours: RecrutementParcours) {
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
