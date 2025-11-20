import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class PageService {
  private readonly pages = [
    'informations',
    'start-compo',
    'questions',
    'results',
    'thanks',
  ] as const;
  private currentPageSubject = new BehaviorSubject<string | null>(null); // Page par défaut

  constructor() {}

  // Méthode pour changer la page
  changePage(page: string) {
    if (this.pages.includes(page as any)) {
      this.currentPageSubject.next(page);
    } else {
      console.warn(`Page "${page}" n'est pas valide.`);
    }
  }

  // Méthode pour obtenir un observable des changements de page
  onPageChange(): Observable<string | null> {
    return this.currentPageSubject.asObservable();
  }
}
