import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root',
})
export class ExamenService {
  constructor(private http: HttpClient) {}

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/examens/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  recentExamen(
    mois: number | undefined = undefined
  ): Observable<ServerResponseType> {
    const url = apiUrl('/examens/recent');
    return this.http.get<ServerResponseType>(url);
  }

  getExemens() {
    const url = apiUrl('/examens');
    return this.http.get<ServerResponseType>(url);
  }
}
