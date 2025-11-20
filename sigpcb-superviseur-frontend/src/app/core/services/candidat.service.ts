import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class CandidatService {
  constructor(private http: HttpClient) {}

  /**
   * Le filter contient des object de la forme {key:value} où key est un paramètre de filtre
   * Récupération: key= list
   * - init les dossiers initiés
   * - payment les dossiers soumis au paiement
   * - validate les dossiers  validés par CED
   * - rejet les dossiers rejetés
   * - pending les dossiers monitorisés sans paiement
   * Les dossiers seront pour uniquement l'auto-école connecté
   * D'autres clés
   * - categorie_permis_id filtre par catégorie
   * langue_id filtre par langue
   etc
   Pour prendre les candidats uniquement sans les autres champs importants
   on peut utiliser la clé snippet=candidat
   */
  getDossiers(
    filters: Record<string, number | string | null>[] = []
  ): Observable<ServerResponseType> {
    let url = apiUrl('/dossier-sessions');

    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType>(url);
  }
}
