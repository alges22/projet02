import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';
import { Subject } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class CounterService {
  private readonly updateNotifier = new Subject<void>();

  constructor(private readonly http: HttpClient) {}
  getCounter(parts: string[]) {
    let params = parts.join(',');
    return this.http.get<ServerResponseType>(
      apiUrl(`/counts?counts=${params}`)
    );
  }
  // Méthode pour notifier les abonnés
  refreshCount() {
    this.updateNotifier.next();
  }

  // Observable pour s'abonner aux notifications
  onRefreshCount() {
    return this.updateNotifier.asObservable();
  }
}
