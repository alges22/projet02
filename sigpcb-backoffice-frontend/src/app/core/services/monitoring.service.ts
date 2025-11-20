import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';
import { Monitoring } from '../interfaces/candidat';

@Injectable({
  providedIn: 'root',
})
export class MonitoringService {
  constructor(private http: HttpClient) {}

  all(
    filters: Record<string, number | string | null>[] = []
  ): Observable<ServerResponseType> {
    let url = apiUrl('/suivi-candidats');

    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType>(url);
  }

  getMonitoringById(id: number): Observable<ServerResponseType> {
    const url = apiUrl(`/suivi-candidats/${id}`);
    return this.http.get<ServerResponseType>(url);
  }
  validate(suivi_id: number) {
    const url = apiUrl('/suivi-candidats/validate');
    return this.http.post<ServerResponseType>(url, { suivi_id });
  }

  reject(suivi_id: number, data: { motif: string; consignes: string }) {
    const url = apiUrl('/suivi-candidats/reject');
    const d = {
      motif: data.motif,
      consigne: data.consignes,
      suivi_id: suivi_id,
    };

    return this.http.post<ServerResponseType>(url, d);
  }
}
