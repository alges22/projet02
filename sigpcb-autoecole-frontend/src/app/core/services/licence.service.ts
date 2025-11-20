import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class LicenceService {
  constructor(private http: HttpClient) {}

  demande(form: FormData) {
    const url = apiUrl('/licences/demandes');
    return this.http.post<ServerResponseType>(url, form);
  }

  rejets(licenceId: string) {
    const url = apiUrl('/licences/demandes/rejets/' + licenceId);
    return this.http.get<ServerResponseType>(url);
  }

  update(form: FormData, licenseId: string) {
    const url = apiUrl('/licences/demandes/rejets/' + licenseId);
    return this.http.post<ServerResponseType>(url, form);
  }
}
