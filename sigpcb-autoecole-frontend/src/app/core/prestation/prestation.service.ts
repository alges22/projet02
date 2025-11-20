import { Injectable } from '@angular/core';
import { Prestation } from './interface/prestation';

@Injectable({
  providedIn: 'root',
})
export class PrestationService {
  private _services: Prestation[] = [
    {
      title: 'Demande d’agrément',
      slug: '/register',
      image: 'assets/images/services/autorisation-ouverture.png',
      href: '/inscription',
      actionText: 'Faire ma demande',
      users: ['promoteur'],
    },
    {
      title: 'Renouvellement de licence',
      slug: 'licence-exploitation',
      image: 'assets/images/services/licence-exploitation.png',
      href: '/licences/demande',
      actionText: 'Faire ma demande',
      users: ['promoteur'],
    },
    {
      title: 'Suivi des candidats ',
      slug: 'monitoring-candidats',
      image: 'assets/images/services/monitoring-candidats.png',
      href: '/gestions/monitoring',
      actionText: 'Démarrer',
      users: ['moniteur', 'promoteur'],
    },
    /*  {
      title:
        'Mutation d’un établissement d’enseignement de conduite automobile et annexes',
      slug: 'mutation-etablissement',
      image: 'assets/images/services/mutation-etablissement.png',
      href: '#',
      actionText: 'Faire ma demande',
      users: ['promoteur'],
    },

    {
      title:
        'Transfert d’un établissement d’enseignement de conduite automobile et annexes',
      slug: 'transfert-etablissement',
      image: 'assets/images/services/transfert-etablissement.png',
      href: '#',
      actionText: 'Faire ma demande',
      users: ['promoteur'],
    }, */
  ];
  constructor() {}

  getServices() {
    return this._services;
  }

  getService(slug: string) {
    return this._services.find((service) => service.slug === slug);
  }
}
