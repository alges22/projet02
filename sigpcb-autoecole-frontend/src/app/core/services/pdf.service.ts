import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';
@Injectable({
  providedIn: 'root',
})
export class PdfService {
  constructor(private http: HttpClient) {}

  candidats(params: Record<string, number | string | null>[] = []) {
    let url = apiUrl('/documents/candidats');

    if (params.length) {
      url = urlencode(url, params);
    }
    return this.http.post<ServerResponseType>(url, {});
  }

  download(
    params: Record<string, number | string | null> = {},
    type: 'facture' | 'agrement' | 'licence'
  ) {
    let url = apiUrl('/documents/' + type);

    return this.http.post<ServerResponseType>(url, params);
  }
}
