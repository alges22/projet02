import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class RestrictionService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/restrictions', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  all(): Observable<ServerResponseType> {
    const url = apiUrl('/restrictions', 'admin');

    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/restrictions/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  update(data: any, id: number) {
    const url = apiUrl('/restrictions/' + id, 'admin');
    return this.http.put<ServerResponseType>(url, data);
  }
  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/restrictions/' + id, 'admin');
    return this.http.delete<ServerResponseType>(url);
  }
}
