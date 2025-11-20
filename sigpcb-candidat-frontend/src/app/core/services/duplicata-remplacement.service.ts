import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class DuplicataRemplacementService {
  constructor(private http: HttpClient) {}
  post(data: FormData) {
    const url = apiUrl('/eservices/duplicatas/store');
    return this.http.post<ServerResponseType>(url, data);
  }

  submit(data: FormData) {
    const url = apiUrl('/eservices/duplicatas/payment');
    return this.http.post<ServerResponseType>(url, data);
  }

  find(rejetId: string) {
    const url = apiUrl('/eservices/duplicatas/rejet/' + rejetId);
    return this.http.get<ServerResponseType>(url);
  }
  update(data: FormData) {
    const url = apiUrl('/eservices/duplicatas/update/' + data.get('rejet_id'));
    return this.http.post<ServerResponseType>(url, data);
  }
}
