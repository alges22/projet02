import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';
import { StatCode, StatConduite } from '../interfaces/resultats';

@Injectable({
  providedIn: 'root',
})
export class ResultatService {
  constructor(private http: HttpClient) {}

  statCode(
    filters: Record<string, number | string | null>[] = []
  ): Observable<ServerResponseType<StatCode>> {
    let url = apiUrl('/resultats/statistic-code');
    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType<StatCode>>(url);
  }

  statConduite(
    filters: Record<string, number | string | null>[] = []
  ): Observable<ServerResponseType<StatConduite>> {
    let url = apiUrl('/resultats/statistic-conduite');
    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType<StatConduite>>(url);
  }

  conduites(filters: Record<string, number | string | null>[] = []) {
    let url = apiUrl('/resultats/conduites');
    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType<StatConduite>>(url);
  }

  codes(filters: Record<string, number | string | null>[] = []) {
    let url = apiUrl('/resultats');
    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType<StatConduite>>(url);
  }

  deliberations(filters: Record<string, number | string | null> = {}) {
    let url = apiUrl('/resultats');
    url = urlencode(url, [filters]);
    return this.http.get<ServerResponseType<StatConduite>>(url);
  }
  listEmargement(filters: Record<string, number | string | null> = {}) {
    let url = apiUrl('/resultats/list-emargement');
    url = urlencode(url, [filters]);
    return this.http.get<ServerResponseType<StatConduite>>(url);
  }

  getAdmisDefinitifs(filters: Record<string, any> = {}) {
    let url = apiUrl('/resultats/admis-permis');
    return this.http.get<ServerResponseType<any>>(url, {
      params: filters,
    });
  }

  createPermis(data: any) {
    let url = apiUrl('/permis');
    return this.http.post<ServerResponseType<StatConduite>>(url, data);
  }
}
