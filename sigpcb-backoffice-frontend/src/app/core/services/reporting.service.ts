import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class ReportingService {
  constructor(private http: HttpClient) {}

  all(
    filters: Record<string, number | string | null>[] = []
  ): Observable<ServerResponseType> {
    let url = apiUrl('/reporting');

    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType>(url);
  }

  titresDerives(
    filters: Record<string, number | string | null>[] = []
  ): Observable<ServerResponseType> {
    let url = apiUrl('/reporting/eservices');

    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType>(url);
  }

  getReportingById(id: number): Observable<ServerResponseType> {
    const url = apiUrl(`/reporting/${id}`);
    return this.http.get<ServerResponseType>(url);
  }
  validate(suivi_id: number) {
    const url = apiUrl('/reporting/validate');
    return this.http.post<ServerResponseType>(url, { suivi_id });
  }

  reject(suivi_id: number, data: { motif: string; consignes: string }) {
    const url = apiUrl('/reporting/reject');
    const d = {
      motif: data.motif,
      consigne: data.consignes,
      suivi_id: suivi_id,
    };

    return this.http.post<ServerResponseType>(url, d);
  }
}
