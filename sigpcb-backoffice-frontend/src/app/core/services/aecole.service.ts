import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class AecoleService {
  constructor(private http: HttpClient) {}

  // get(state?: any): Observable<ServerResponseType> {
  //   const url = apiUrl('/auto-ecoles', 'admin');
  //   const params = { state: state };
  //   return this.http.get<ServerResponseType>(url, { params });
  // }

  get(
    filters: Record<string, number | string | null>[] = []
  ): Observable<ServerResponseType> {
    let url = apiUrl('/auto-ecoles', 'admin');

    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType>(url);
  }

  status(data: any) {
    const url = apiUrl('/auto-ecoles/status', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  // getLicence(status?: any): Observable<ServerResponseType> {
  //   const url = apiUrl('/licences', 'admin');
  //   const params = { status: status };
  //   return this.http.get<ServerResponseType>(url, { params });
  // }

  getLicence(
    filters: Record<string, number | string | null>[] = []
  ): Observable<ServerResponseType> {
    let url = apiUrl('/licences', 'admin');

    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType>(url);
  }

  // getAgrement(states?: any): Observable<ServerResponseType> {
  //   let url = apiUrl('/demande-agrement', 'admin');
  //   url = `${url}?state=${states.join(',')}`;
  //   return this.http.get<ServerResponseType>(url);
  // }

  // Fonction getAgrement mise à jour avec les paramètres page et search
  getAgrement(
    states: string[],
    page?: number | null,
    search?: string | null | number
  ): Observable<ServerResponseType> {
    // Construire l'URL avec les états fournis
    let url = apiUrl('/demande-agrement', 'admin');
    url += `?state=${states.join(',')}`;

    // Ajouter le paramètre page à l'URL s'il est fourni et n'est pas null
    if (page !== undefined && page !== null) {
      url += `&page=${page}`;
    }

    // Ajouter le paramètre search à l'URL s'il est fourni et n'est pas null
    if (search !== undefined && search !== null) {
      url += `&search=${search}`;
    }

    // Effectuer la requête HTTP GET
    return this.http.get<ServerResponseType>(url);
  }

  // getAgrement(
  //   filters: Record<string, number | string | null>[] = []
  // ): Observable<ServerResponseType> {
  //   let url = apiUrl('/demande-agrement', 'admin');

  //   if (filters.length) {
  //     url = urlencode(url, filters);
  //   }
  //   return this.http.get<ServerResponseType>(url);
  // }

  validate(agrement: any) {
    const url = apiUrl('/demande-agrement/validate');
    return this.http.post<ServerResponseType>(url, agrement);
  }

  reject(d_agrement_id: number, data: { motif: string; consignes: string }) {
    const url = apiUrl('/demande-agrement/rejet');
    const d = {
      motif: data.motif,
      consigne: data.consignes,
      d_agrement_id: d_agrement_id,
    };
    return this.http.post<ServerResponseType>(url, d);
  }

  // getNouvelleLicence(states?: any): Observable<ServerResponseType> {
  //   let url = apiUrl('/demande-licences', 'admin');
  //   url = `${url}?state=${states.join(',')}`;
  //   return this.http.get<ServerResponseType>(url);
  // }

  // getNouvelleLicence(
  //   filters: Record<string, number | string | null>[] = []
  // ): Observable<ServerResponseType> {
  //   let url = apiUrl('/demande-licences', 'admin');

  //   if (filters.length) {
  //     url = urlencode(url, filters);
  //   }
  //   return this.http.get<ServerResponseType>(url);
  // }

  getNouvelleLicence(
    states: string[],
    page?: number | null,
    search?: string | null | number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/demande-licences', 'admin');
    url += `?state=${states.join(',')}`;
    if (page !== undefined && page !== null) {
      url += `&page=${page}`;
    }
    if (search !== undefined && search !== null) {
      url += `&search=${search}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  validateNouvelleLicence(nouvellelicence: any) {
    const url = apiUrl('/demande-licences/validate');
    return this.http.post<ServerResponseType>(url, nouvellelicence);
  }

  rejectNouvelleLicence(
    d_licence_id: number,
    data: { motif: string; consigne: string }
  ) {
    const url = apiUrl('/demande-licences/rejet');
    const d = {
      motif: data.motif,
      consigne: data.consigne,
      d_licence_id: d_licence_id,
    };
    return this.http.post<ServerResponseType>(url, d);
  }

  postImportFile(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/importation-auto-ecoles', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  postAutoEcole(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/auto-ecoles/create', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  updateAutoEcole(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/auto-ecoles/update/' + data.id, 'admin');
    return this.http.put<ServerResponseType>(url, data);
  }

  private updateAutoEcole$ = new BehaviorSubject<void>(undefined);

  get updateAutoEcoleObservable() {
    return this.updateAutoEcole$.asObservable();
  }

  updateAutoEcoleList() {
    this.updateAutoEcole$.next(undefined);
  }

  updateMoniteur(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/auto-ecoles/update-motineur/' + data.id, 'admin');
    return this.http.put<ServerResponseType>(url, data);
  }

  updatePromoteur(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/auto-ecoles/update-promoteur/' + data.id, 'admin');
    return this.http.put<ServerResponseType>(url, data);
  }

  updateVehicule(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/auto-ecoles/update-vehicule/' + data.id, 'admin');
    return this.http.put<ServerResponseType>(url, data);
  }
}
