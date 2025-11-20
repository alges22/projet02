import { NavigationEnd, Router } from '@angular/router';
import { NavbarLink } from './../../core/interfaces/navbar-link';
import { Component, OnInit } from '@angular/core';
import { AuthService } from 'src/app/core/services/auth.service';

@Component({
  selector: 'app-auth-sidebar',
  templateUrl: './auth-sidebar.component.html',
  styleUrls: ['./auth-sidebar.component.scss'],
})
export class AuthSidebarComponent implements OnInit {
  constructor(private router: Router, private authService: AuthService) {}
  currentUrl: string | null = null;
  activate!: NavbarLink;
  hasEvent = false;
  firstMenus: NavbarLink[] = [
    {
      label: 'Tableau de bord',
      icon: 'house',
      href: '/dashboard',
      navid: 'tab-bord',
      active: true,
      children: [],
    },
    {
      label: 'Mon profil',
      icon: 'person',
      href: '/profiles',
      navid: 'my-profile',
      active: false,
      children: [],
    },
    {
      label: 'Paramètres',
      icon: 'gear',
      href: 'parametres',
      navid: 'parameters',
      active: false,
      open: true,
      children: [
        {
          label: 'Paramétrage de base',
          icon: '',
          href: '/parametres/base',
          navid: 'parametres-base',
        },
        {
          label: "Gestion d'accès",
          icon: '',
          href: '/parametres/administrateurs',
          navid: 'administrateurs-home',
        },
        {
          label: 'Signatures',
          icon: '',
          href: '/parametres/signatures',
          navid: 'parametres-signatures',
        },
        {
          label: 'Unités territoriales',
          icon: '',
          href: '/parametres/territoriales',
          navid: 'parametres-territoriales',
        },
        {
          label: 'Examatique',
          icon: '',
          href: '/parametres/examatique',
          navid: 'parametres-examatique',
        },
      ],
    },
  ];

  ngOnInit(): void {
    this.setRouteState();
  }

  private checkUserRole(targetRole: string): boolean {
    const userRoles = JSON.parse(
      JSON.stringify(this.authService.storageService().get('userRoles'))
    ); // Récupération des rôles de l'utilisateur depuis le localstorage
    if (userRoles && userRoles.some((role: any) => role.name === targetRole)) {
      // Vérification si l'utilisateur a le rôle "admin"
      return true;
    }
    return false;
  }

  adminRoute(): boolean {
    return this.checkUserRole('admin');
  }

  superAdminRoute(): boolean {
    return this.checkUserRole('super-admin');
  }

  ngAfterViewInit(): void {
    this.parentLinks();
    var dropBtn = $('.dropBtn');
    for (let i = 0; i < dropBtn.length; i++) {
      const element = dropBtn[i];
      $(element).on('click', function (e) {
        e.preventDefault();
        const angle = $(element).children('.angle');
        $(angle).toggleClass('k90deg');
        $(element).siblings().toggle();
      });
    }
  }
  hasChildren(navlink: NavbarLink) {
    return navlink.children && navlink.children.length;
  }

  private parentLinks() {
    $<HTMLLIElement>('.nav-item').each((i, linkItem) => {
      $(linkItem).find('.parent-link').removeClass('active');

      const childrenLink = $(linkItem)
        .find('.dropdown-content > a')
        .get()
        .map((a) => {
          return a.getAttribute('href');
        });

      //Si le lien courant
      if (
        childrenLink.some((lk) => {
          const currentUrl = this.currentUrl ?? window.location.pathname;

          if (currentUrl) {
            return this.currentUrl?.indexOf(lk ?? '') !== -1;
          } else {
            return false;
          }
        })
      ) {
        const jqLinkItem = $(linkItem).find('.parent-link');

        jqLinkItem.addClass('active');
        const navid = jqLinkItem.data('navid');
        this.firstMenus.forEach((m) => {
          if (m.navid == navid) {
            m.open = true;
          }
        });
      }
    });
  }

  private setRouteState() {
    if (!this.hasEvent) {
      this.currentUrl = window.location.pathname;
    }
    this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        this.currentUrl = event.url;
        this.parentLinks();
      }
    });
  }
}
