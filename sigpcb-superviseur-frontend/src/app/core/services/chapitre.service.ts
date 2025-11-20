import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class ChapitreService {
  constructor(private http: HttpClient) {}

  get(): Observable<ServerResponseType> {
    const url = apiUrl('/chapitres-base');
    return this.http.get<ServerResponseType>(url);
  }
}
