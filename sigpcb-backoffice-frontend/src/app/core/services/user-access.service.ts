import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { PermissionExpression } from '../interfaces/role';

@Injectable({
  providedIn: 'root',
})
export class UserAccessService {
  private readonly permissionsSubject = new BehaviorSubject<
    PermissionExpression[]
  >([]);
  permissions$: Observable<PermissionExpression[]> =
    this.permissionsSubject.asObservable();

  constructor() {}

  /**
   * Met à jour les permissions de l'utilisateur.
   * @param permissions - Une liste de permissions.
   */
  setPermissions(permissions: PermissionExpression[]): void {
    this.permissionsSubject.next(permissions);
  }

  /**
   * Vérifie si toutes les permissions données sont présentes.
   * @param permissions - Les permissions à vérifier (sous forme de paramètres multiples).
   * @returns true si toutes les permissions sont présentes, sinon false.
   */
  hasPermissions(...permissions: PermissionExpression[]): boolean {
    const currentPermissions = this.permissionsSubject.getValue();
    return permissions.every((permission) =>
      currentPermissions.includes(permission)
    );
  }
  hasOneOf(...permissions: PermissionExpression[]): boolean {
    const currentPermissions = this.permissionsSubject.getValue();
    return permissions.some((permission) =>
      currentPermissions.includes(permission)
    );
  }
}
