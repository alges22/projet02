import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class LangueService {
  constructor(private readonly http: HttpClient) {}

  all(): Observable<ServerResponseType> {
    const url = apiUrl('/langues');

    return this.http.get<ServerResponseType>(url);
  }

  status(data: any) {
    const url = apiUrl('/langues/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/langues/' + id);
    return this.http.get<ServerResponseType>(url);
  }
}
