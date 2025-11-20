import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class UsersService {
  constructor(private http: HttpClient) {}

  getRejetsInfos(rejetId: string): Observable<ServerResponseType> {
    const url = apiUrl('/my-infos/rejets/' + rejetId);
    return this.http.get<ServerResponseType>(url);
  }

  rejetsInfos(rejetId: string, form: FormData): Observable<ServerResponseType> {
    const url = apiUrl('/my-infos/' + rejetId);
    return this.http.post<ServerResponseType>(url, form);
  }

  myInfos() {
    const url = apiUrl('/my-infos');
    return this.http.get<ServerResponseType>(url);
  }
  updateAeInfos(form: FormData, rejetId?: string) {
    let url = apiUrl('/my-infos');

    if (rejetId) {
      url += '/rejets/' + rejetId;
    }
    return this.http.post<ServerResponseType>(url, form);
  }
}
