import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class LangueService {
  constructor(private http: HttpClient) {}

  all(): Observable<ServerResponseType> {
    const url = apiUrl('/langues-base');

    return this.http.get<ServerResponseType>(url);
  }
}
