import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class CommuneService {
  constructor(private http: HttpClient) {}

  getCommunes(page = 1, liste = 'paginate'): Observable<ServerResponseType> {
    let url = apiUrl('/communes', 'base');
    url = `${url}?liste=${liste}`;
    if (liste === 'paginate') {
      url = `${url}&page=${page}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/communes/' + id, 'base');
    return this.http.get<ServerResponseType>(url);
  }

  deleteAdmin(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/communes/' + id, 'base');
    return this.http.delete<ServerResponseType>(url);
  }

  deleteManyAdmins(ids: number[]): Observable<ServerResponseType> {
    const url = apiUrl('/communes/deletes', 'base');
    const data = {
      user_ids: ids.join(';'),
    };
    return this.http.post<ServerResponseType>(url, data);
  }

  update(data: any, id: number) {
    const url = apiUrl('/communes/' + id, 'base');
    return this.http.put<ServerResponseType>(url, data);
  }
}
