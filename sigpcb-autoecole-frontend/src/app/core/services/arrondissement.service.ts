import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class ArrondissementService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/arrondissements', 'base');

    return this.http.post<ServerResponseType>(url, data);
  }

  getArrondissements(
    pageNumber = 1,
    liste = 'paginate'
  ): Observable<ServerResponseType> {
    let url = apiUrl('/arrondissements', 'base');
    if (liste === 'paginate') {
      url = `${url}?liste=${liste}&page=${pageNumber}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/arrondissements/' + id, 'base');
    return this.http.get<ServerResponseType>(url);
  }

  deleteAdmin(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/arrondissements/' + id, 'base');
    return this.http.delete<ServerResponseType>(url);
  }

  deleteManyAdmins(ids: number[]): Observable<ServerResponseType> {
    const url = apiUrl('/arrondissements/deletes', 'base');
    const data = {
      user_ids: ids.join(';'),
    };
    return this.http.post<ServerResponseType>(url, data);
  }

  update(data: any, id: number) {
    const url = apiUrl('/arrondissements/' + id, 'base');
    return this.http.put<ServerResponseType>(url, data);
  }
}
