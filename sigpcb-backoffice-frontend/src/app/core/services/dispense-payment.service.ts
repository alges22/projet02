import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class DispensePaymentService {
  constructor(private readonly http: HttpClient) {}

  post(data: { npi: string }) {
    const url = apiUrl('/dispense-paiements');
    return this.http.post<ServerResponseType>(url, data);
  }

  get(data: Record<string, string | number> = {}) {
    const url = apiUrl('/dispense-paiements');
    return this.http.get<ServerResponseType>(url, {
      params: data,
    });
  }

  action(data: { action: string; id: number }) {
    const url = apiUrl('/dispense-paiements/' + data.id);
    return this.http.put<ServerResponseType>(url, data);
  }
}
