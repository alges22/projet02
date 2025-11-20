import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class ChapitreService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/chapitres', );
    return this.http.post<ServerResponseType>(url, data);
  }

  get(): Observable<ServerResponseType> {
    const url = apiUrl('/chapitres', );
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/chapitres/' + id, );
    return this.http.get<ServerResponseType>(url);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/chapitres/' + id, );
    return this.http.delete<ServerResponseType>(url);
  }

  deleteMany(ids: number[]): Observable<ServerResponseType> {
    const url = apiUrl('/chapitres/deletes', );
    const data = {
      user_ids: ids.join(';'),
    };
    return this.http.post<ServerResponseType>(url, data);
  }

  update(data: any, id: number) {
    const url = apiUrl('/chapitres/' + id, );
    return this.http.put<ServerResponseType>(url, data);
  }
}
