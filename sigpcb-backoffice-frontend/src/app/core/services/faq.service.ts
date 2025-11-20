import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';
import { Faq } from '../interfaces/faq';

@Injectable({
  providedIn: 'root',
})
export class FaqService {
  constructor(private http: HttpClient) {}

  get(filters: Record<string, number | string | null>[] = []) {
    let url = apiUrl('/faqs');
    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType<Faq[]>>(url);
  }
  post(data: Faq): Observable<ServerResponseType> {
    let url = apiUrl('/faqs');

    if (data.id) {
      url = apiUrl('/faqs/' + data.id);
      return this.http.put<ServerResponseType<Faq>>(url, data);
    }
    return this.http.post<ServerResponseType<Faq>>(url, data);
  }
  delete(faq: Faq) {
    const url = apiUrl('/faqs/' + faq.id);
    return this.http.delete<ServerResponseType>(url);
  }
}
