import { Component, Input, OnInit } from '@angular/core';
import { AuthService } from 'src/app/core/services/auth.service';

@Component({
  selector: 'app-admin-topbar',
  templateUrl: './admin-topbar.component.html',
  styleUrls: ['./admin-topbar.component.scss'],
})
export class AdminTopbarComponent implements OnInit {
  @Input('default') defaultMenuId = 0;
  menuSelected: number | null = null;
  menus = [
    {
      label: 'Administrateurs',
      icon: 'person',
      href: '/parametres/administrateurs',
      active: true,
      menuId: 0,
    },

    {
      label: 'Rôles',
      icon: 'diagram-3',
      href: '/parametres/administrateurs/roles',
      active: false,
      menuId: 1,
    },
    {
      label: 'Titres',
      icon: 'ui-checks',
      href: '/parametres/administrateurs/titres',
      active: false,
      menuId: 2,
    },

    {
      label: 'Unité Administrative',
      icon: 'houses',
      href: '/parametres/administrateurs/unite-admins',
      active: false,
      menuId: 3,
    },
  ];

  constructor(private authService: AuthService) {}

  ngOnInit(): void {
    this.menuSelected = this.defaultMenuId;
  }

  onSelectMenu(event: Event, menuId: number) {
    event.preventDefault();
    this.menus.forEach((m) => (m.active = false));
    this.menuSelected = menuId;
  }

  //   isMenuAuthorized(menu: any): boolean {
  //   const userRoles: any = this.authService.storageService().get('userRoles');
  //   const allowedRoutes = [ '/parametres/administrateurs/titres', '/parametres/administrateurs/unite-admins'];
  //   if (userRoles && userRoles.some((role: any) => role.name === 'admin')) {
  //     // const allowedRoutes = [ '/parametres/administrateurs/titres', '/parametres/administrateurs/unite-admins'];
  //     return allowedRoutes.includes(menu.href);
  //   }
  //   if (userRoles && userRoles.some((role: any) => role.name === 'super-admin')) {
  //     allowedRoutes.push(...['/parametres/administrateurs/home','/parametres/administrateurs/roles']);
  //     return allowedRoutes.includes(menu.href);
  //   }
  //   // Si l'utilisateur n'a pas le rôle "admin", ne pas afficher le menu
  //   return false;
  // }
}
