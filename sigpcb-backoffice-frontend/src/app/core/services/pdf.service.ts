import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';
@Injectable({
  providedIn: 'root',
})
export class PdfService {
  constructor(private http: HttpClient) {}

  programmationCode(params: Record<string, number | string | null>[] = []) {
    let url = apiUrl('/documents/programmation-code');

    if (params.length) {
      url = urlencode(url, params);
    }
    return this.http.post<ServerResponseType>(url, {});
  }

  programmationConduite(params: Record<string, number | string | null>[] = []) {
    let url = apiUrl('/documents/programmation-conduite');

    if (params.length) {
      url = urlencode(url, params);
    }
    return this.http.post<ServerResponseType>(url, {});
  }

  rapportStatistique(
    params: Record<string, number | string | null>,
    slug = 'rapport-statistics'
  ) {
    let url = apiUrl('/documents/' + slug);

    if (params) {
      url = urlencode(url, [params]);
    }
    return this.http.post<ServerResponseType>(url, {});
  }

  agendas(params: Record<string, number | string | null>) {
    let url = apiUrl('/documents/agendas');

    if (params) {
      url = urlencode(url, [params]);
    }
    return this.http.post<ServerResponseType>(url, {});
  }
  download(
    slug: 'resultat-examen' | 'resultat-permis-excel',
    params: Record<string, string | null | number> = {}
  ) {
    let url = apiUrl('/documents/' + slug);

    if (params) {
      url = urlencode(url, [params]);
    }
    return this.http.post<ServerResponseType>(url, {});
  }
}
