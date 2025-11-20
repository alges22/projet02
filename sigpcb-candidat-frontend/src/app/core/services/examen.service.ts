import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';
import { Examen } from '../interfaces/global';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class ExamenService {
  constructor(private http: HttpClient) {}

  get(): Observable<ServerResponseType<Examen[]>> {
    const url = apiUrl('/examens');
    return this.http.get<ServerResponseType<Examen[]>>(url);
  }
}
