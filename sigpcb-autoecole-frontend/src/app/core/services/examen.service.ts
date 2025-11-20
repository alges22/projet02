import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { Agenda } from '../interfaces/date';

@Injectable({
  providedIn: 'root',
})
export class ExamenService {
  // Crée un BehaviorSubject avec une valeur initiale de null
  private selectedExamSubject: BehaviorSubject<Agenda | null> =
    new BehaviorSubject<Agenda | null>(null);

  constructor(private http: HttpClient) {}

  getExemens(filters: Record<string, number | string | null>[] = []) {
    let url = apiUrl('/examens');

    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType>(url);
  }

  // Méthode pour sélectionner un examen
  selectExam(exam: Agenda | null): void {
    this.selectedExamSubject.next(exam);
  }

  // Méthode pour obtenir l'examen actuellement sélectionné
  onSelectedExam() {
    return this.selectedExamSubject.asObservable();
  }
}
