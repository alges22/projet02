import { Component, OnInit } from '@angular/core';
import { EserviceParcours } from 'src/app/core/interfaces/services';
import { EserviceParcoursService } from 'src/app/core/services/eservice-parcours.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service copy';

@Component({
  selector: 'app-suivre-eservice',
  templateUrl: './suivre-eservice.component.html',
  styleUrls: ['./suivre-eservice.component.scss'],
})
export class SuivreEserviceComponent implements OnInit {
  cardOpendedIndex = null as null | number;
  parcours: { service: string; liste: EserviceParcours[] }[] = [];
  latestParcours: EserviceParcours[] = [];
  constructor(
    private eserviceParcoursService: EserviceParcoursService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.errorHandler.startLoader();
    this.eserviceParcoursService
      .get()
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
    this.parcours = parcours.map((tableau: EserviceParcours[]) => {
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

  private createLatestParcours(parcours: EserviceParcours) {
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
