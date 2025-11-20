import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class AgrementService {
  constructor(private http: HttpClient) {}
  get(): Observable<ServerResponseType> {
    const url = apiUrl('/agrements/auto-ecoles');
    return this.http.get<ServerResponseType>(url);
  }
}
