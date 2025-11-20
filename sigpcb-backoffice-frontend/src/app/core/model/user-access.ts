import { PermissionExpression } from '../interfaces/role';
export class UserAccess {
  private readonly permissions: PermissionExpression[] = [];

  /**
   * Initialise une instance de UserAccess avec les permissions de l'utilisateur spécifié.
   * @param user L'utilisateur dont les autorisations sont gérées.
   */
  constructor(private readonly user: any) {
    if (user.roles) {
      user.permissions = [];
      user.roles.forEach((role: any) => {
        if (role.permissions) {
          user.permissions = [...user.permissions, ...role.permissions];
        }
      });
      this.user = user;
    }
    if (this.user.permissions) {
      this.permissions = this.user.permissions.map((permission: any) => {
        return permission.name;
      });
    }
  }

  /**
   * Vérifie si l'utilisateur possède une permission spécifique ou une liste de permissions.
   * @param permission La ou les permissions à vérifier.
   * @returns Vrai si l'utilisateur possède la(es) permission(s), sinon faux.
   */
  hasPermission(
    permission: PermissionExpression | PermissionExpression[]
  ): boolean {
    if (this.permissions.includes('all')) {
      return true;
    }

    if (Array.isArray(permission)) {
      return permission.every((element) => this.permissions.includes(element));
    } else {
      return (
        this.permissions.includes(permission) ||
        this.permissions.includes('all')
      );
    }
  }

  /**
   * Vérifie si l'utilisateur possède au moins l'une des permissions spécifiées dans une liste.
   * @param permission La liste des permissions à vérifier.
   * @returns Vrai si l'utilisateur possède au moins une des permissions, sinon faux.
   */
  hasAnyPermission(
    permission: PermissionExpression | PermissionExpression[]
  ): boolean {
    if (Array.isArray(permission)) {
      return permission.some((p) => this.permissions.includes(p));
    } else {
      return this.permissions.includes(permission);
    }
  }

  get getPermissions() {
    return this.permissions;
  }
}
