import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class CounterService {
  constructor(private http: HttpClient) {}
  /**
   * Permet de prendre des counts faciles
   * @param parts
   * @param condition condition de count
   * @returns
   */
  getCounter(
    parts: string[],
    condition: Record<string, number | string | null>[] = []
  ) {
    let url = apiUrl(`/counts`);
    //Les counts
    let params = parts.join(',');

    condition.push({
      counts: params,
    });
    if (condition.length) {
      url = urlencode(url, condition);
    }
    return this.http.get<ServerResponseType>(url);
  }

  authCount(
    parts: string[],
    condition: Record<string, number | string | null>[] = []
  ) {
    let url = apiUrl(`/auth-counts`);
    //Les counts
    let params = parts.join(',');

    condition.push({
      counts: params,
    });
    if (condition.length) {
      url = urlencode(url, condition);
    }
    return this.http.get<ServerResponseType>(url);
  }
}
