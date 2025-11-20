import { Injectable } from '@angular/core';
import { Breadcrumb } from '../interfaces/breadcrumb';
import { BehaviorSubject, Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class BreadcrumbService {
  private breadcrumbsSubject: BehaviorSubject<Breadcrumb[]> =
    new BehaviorSubject<Breadcrumb[]>([]);
  private breadcrumbs$: Observable<Breadcrumb[]> =
    this.breadcrumbsSubject.asObservable();

  private titleSubject: BehaviorSubject<string> = new BehaviorSubject<string>(
    ''
  );
  private title$: Observable<string> = this.titleSubject.asObservable();
  constructor() {}

  setBreadcrumbs(title: string, breadcrumbs: Breadcrumb[]): void {
    this.breadcrumbsSubject.next(breadcrumbs);
    this.titleSubject.next(title);
  }

  getBreadcrumbs() {
    return this.breadcrumbs$;
  }

  getTitle() {
    return this.title$;
  }
  hasBreadcrumb(): boolean {
    return (
      this.breadcrumbsSubject.value.length > 0 &&
      this.titleSubject.value.length > 0
    );
  }
}
