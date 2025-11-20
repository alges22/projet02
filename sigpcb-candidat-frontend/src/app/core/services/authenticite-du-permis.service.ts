import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class AuthenticiteDuPermisService {
  constructor(private http: HttpClient) {}
  post(data: FormData) {
    const url = apiUrl('/eservices/authenticites/store');
    return this.http.post<
      ServerResponseType<{
        fedapay: {
          url: string;
        };
      }>
    >(url, data);
  }

  update(data: FormData) {
    const url = apiUrl(
      '/eservices/authenticites/update/' + data.get('rejet_id')
    );
    return this.http.post<ServerResponseType>(url, data);
  }
  submit(data: FormData) {
    const url = apiUrl('/eservices/authenticites/payment');
    return this.http.post<ServerResponseType>(url, data);
  }
  find(rejetId: string) {
    const url = apiUrl('/eservices/authenticites/rejet/' + rejetId);
    return this.http.get<ServerResponseType>(url);
  }
}
