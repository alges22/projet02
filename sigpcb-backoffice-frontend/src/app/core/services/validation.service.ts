import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root',
})
export class ValidationService {
  constructor(private http: HttpClient) {}

  all(
    filters: Record<string, number | string | null>[] = []
  ): Observable<ServerResponseType> {
    let url = apiUrl('/validation-ced');

    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType>(url);
  }

  getMonitoringById(id: number): Observable<ServerResponseType> {
    const url = apiUrl(`/validation-ced/${id}`);
    return this.http.get<ServerResponseType>(url);
  }
  validate(justif_id: number) {
    const url = apiUrl('/validation-ced/validation');
    return this.http.post<ServerResponseType>(url, {
      justif_id,
      state: 'validate',
    });
  }

  reject(justif_id: number, data: { consignes: string }) {
    const url = apiUrl('/validation-ced/validation');
    const d = {
      consignes: data.consignes,
      justif_id: justif_id,
      state: 'rejet',
    };

    return this.http.post<ServerResponseType>(url, d);
  }
}
