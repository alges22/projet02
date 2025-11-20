import { Injectable } from '@angular/core';
import { EntrepriseButton } from './interface/entreprise-button';

@Injectable({
  providedIn: 'root',
})
export class EntrepriseButtonService {
  private _buttons: EntrepriseButton[] = [
    {
      title: 'Gestion des recrutements',
      slug: 'sessions',
    },
    // {
    //   title: 'Digital driving license application',
    //   slug: 'demande-permis-numerique',
    // },
    // {
    //   title: "Renewal of driver's license",
    //   slug: 'renouvellement-permis',
    // },
  ];
  constructor() {}

  getServices() {
    return this._buttons;
  }

  getService(slug: string) {
    return this._buttons.find((button) => button.slug === slug);
  }
}
