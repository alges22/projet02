import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class DispensePaiementService {
  constructor(private readonly http: HttpClient) {}

  check(): Observable<ServerResponseType> {
    let url = apiUrl('/check-validated-dispense');
    return this.http.get<ServerResponseType>(url);
  }

  skipePayment(data: { examen_id: string | number }) {
    let url = apiUrl('/candidat-paiement/dispenses');
    return this.http.post<ServerResponseType>(url, data);
  }
}
