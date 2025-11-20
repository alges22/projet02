import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class TypePieceService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/type-pieces');
    return this.http.post<ServerResponseType>(url, data);
  }

  all(): Observable<ServerResponseType> {
    const url = apiUrl('/type-pieces');

    return this.http.get<ServerResponseType>(url);
  }
  status(data: any) {
    const url = apiUrl('/type-pieces/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/type-pieces/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  update(data: any, id: number) {
    const url = apiUrl('/type-pieces/' + id);
    return this.http.put<ServerResponseType>(url, data);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/type-pieces/' + id);
    return this.http.delete<ServerResponseType>(url);
  }
}
