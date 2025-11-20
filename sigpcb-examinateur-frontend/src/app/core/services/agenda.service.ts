import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class AgendaService {
  constructor(private http: HttpClient) {}

  all(): Observable<ServerResponseType> {
    const url = apiUrl('/examens');
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/examens/' + id);
    return this.http.get<ServerResponseType>(url);
  }
}
