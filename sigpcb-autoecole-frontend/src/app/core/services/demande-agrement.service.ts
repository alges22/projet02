import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class DemandeAgrementService {
  constructor(private http: HttpClient) {}

  demande(form: FormData) {
    const url = apiUrl('/demande-agrements');
    return this.http.post<ServerResponseType>(url, form);
  }
  update(form: FormData, agrementId: string) {
    const url = apiUrl('/demande-agrements/rejets/' + agrementId);
    return this.http.post<ServerResponseType>(url, form);
  }
  rejets(agrementID: string) {
    const url = apiUrl('/demande-agrements/rejets/' + agrementID);
    return this.http.get<ServerResponseType>(url);
  }

  ensureData(form: FormData) {
    const url = apiUrl('/demande-agrements/ensure-data');
    return this.http.post<ServerResponseType>(url, form);
  }

  submit(form: FormData) {
    const url = apiUrl('/demande-agrements/submit');
    return this.http.post<ServerResponseType>(url, form);
  }
}
