import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';
import { StatRapport } from '../interfaces/statistiques';

@Injectable({
  providedIn: 'root',
})
export class StatisticService {
  constructor(private http: HttpClient) {}

  get(filters?: Record<string, number | string | null>) {
    let url = apiUrl('/statistics/candidats', 'admin');

    if (filters) {
      url = urlencode(url, [filters]);
    }
    return this.http.get<
      ServerResponseType<{ langues: any[]; data: StatRapport[] }>
    >(url);
  }

  charts(slug: 'candidats', filters: Record<string, number | string | null>) {
    let url = apiUrl('/charts/' + slug);

    if (filters) {
      url = urlencode(url, [filters]);
    }
    return this.http.get<ServerResponseType>(url);
  }
}
