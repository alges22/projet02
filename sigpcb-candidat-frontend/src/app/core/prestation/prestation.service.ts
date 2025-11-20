import { Injectable } from '@angular/core';
import { Prestation } from './interface/prestation';
import { Subject } from 'rxjs';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root',
})
export class PrestationService {
  private _services: Prestation[] = [
    {
      title: "Registration for the Driver's License Exam",
      slug: 'inscription-examen',
      image: 'assets/images/services/permis-conduite.png',
      active: true,
    },
    {
      title: 'Attestation de succès au permis de conduire',
      slug: 'attestation-de-succes',
      image: 'assets/images/services/attestation-success.png',
      active: true,
    },
    {
      title: "Renewal of driver's license",
      slug: 'renouvellement-permis',
      image: 'assets/images/services/renouvellement-permis.png',
    },
    {
      title: 'Digital driving license application',
      slug: 'demande-permis-numerique',
      image: 'assets/images/services/demande-permis-numerique.png',
    },

    {
      title: "Duplicate & Replacement of Driver's License",
      slug: 'duplicata-remplacement',
      image: 'assets/images/services/duplicata-remplacement.png',
    },
    {
      title: "Exchange my driver's license",
      slug: 'echange-permis',
      image: 'assets/images/services/echanger-mon-permis.png',
    },

    {
      title: "Authenticity of the driver's license",
      slug: 'authenticite-du-permis',
      image: 'assets/images/services/authenticite-du-permis.png',
    },

    {
      title: 'International driving license',
      slug: 'permis-international',
      image: 'assets/images/services/permis-internationnal.png',
    },
  ];
  constructor() {}

  getServices() {
    return this._services;
  }

  getService(slug: string) {
    return this._services.find((service) => service.slug === slug);
  }

  private modalOpenedSubject = new Subject<void>();
  modalOpened$ = this.modalOpenedSubject.asObservable();

  emitModalOpenedEvent() {
    this.modalOpenedSubject.next();
  }
}
