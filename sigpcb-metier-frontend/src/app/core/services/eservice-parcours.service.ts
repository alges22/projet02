import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { data } from 'jquery';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class EserviceParcoursService {
  constructor(private http: HttpClient) {}

  get() {
    const url = apiUrl('/candidats-eservices-parcours', 'candidat');
    return this.http.get<ServerResponseType>(url);
  }
}
