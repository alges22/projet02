import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class CategoryPermisService {
  constructor(private http: HttpClient) {}

  all(): Observable<ServerResponseType> {
    const url = apiUrl('/categorie-permis-base');

    return this.http.get<ServerResponseType>(url);
  }
}
