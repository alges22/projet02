import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl } from 'src/app/helpers/helpers';
import { Faq } from '../interfaces/faq';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class FaqService {
  constructor(private http: HttpClient) {}
  get() {
    const url = apiUrl('/faqs');
    return this.http.get<ServerResponseType<Faq[]>>(url);
  }
}
