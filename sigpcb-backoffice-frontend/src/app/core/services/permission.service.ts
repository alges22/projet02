import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root'
})
export class PermissionService {

  constructor(private http: HttpClient) {}

  get(): Observable<ServerResponseType> {
    const url = apiUrl('/permissions');

    return this.http.get<ServerResponseType>(url);
  }
}
