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

  getCommunes(page = 1, liste = 'all'): Observable<ServerResponseType> {
    let url = apiUrl('/communes');
    url = `${url}?liste=${liste}`;
    if (liste === 'paginate') {
      url = `${url}&page=${page}`;
    }
    return this.http.get<ServerResponseType>(url);
  }
}
