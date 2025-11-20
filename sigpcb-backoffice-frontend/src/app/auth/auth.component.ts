import { Component, ElementRef, OnInit } from '@angular/core';
import { AuthService } from '../core/services/auth.service';
import { NavigationService } from '../core/services/navigation.service';
import { NavItem } from '../core/interfaces/navbar-link';
import { HttpErrorHandlerService } from '../core/services/http-error-handler.service';
import { NavigationEnd, Router } from '@angular/router';
import { UserAccess } from '../core/model/user-access';
import { UserAccessService } from '../core/services/user-access.service';

@Component({
  selector: 'app-auth',
  templateUrl: './auth.component.html',
  styleUrls: ['./auth.component.scss'],
})
export class AuthComponent implements OnInit {
  links: NavItem[] = [];
  user: any = null;
  roles: any[] = [];
  constructor(
    private readonly elementRef: ElementRef<HTMLElement>,
    private readonly authService: AuthService,
    private readonly navigationService: NavigationService,
    private readonly router: Router,
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly userAccessService: UserAccessService
  ) {}

  openSidebar = false;

  ngOnInit(): void {
    this.errorHandler.startLoader('Chargement en cours ...');
    this.authService
      .profile()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.user = response.data;
        //Extraction des permission
        const userAccess = new UserAccess(this.user);
        // Détermination de quel lien sera affiché
        this.links = this.navigationService.setup(userAccess);

        // Stockage des permissions utilisateur
        this.userAccessService.setPermissions(userAccess.getPermissions);

        this.roles = this.user.roles;
        this.whenRouteChange();
        this.errorHandler.stopLoader();
      });
  }
  toggleSidebar(): void {
    this.openSidebar = !this.openSidebar;
  }

  ngAfterViewInit(): void {
    this.closeSidebarOnOutsideClick();
  }

  /**
   * Cette méthode appelle la méthode onPress sur les liens
   * @param i
   * @param event
   */
  onPress(i: number, event: Event) {
    event.preventDefault();
    this.navigationService.toggle(i);
  }
  private closeSidebarOnOutsideClick(): void {
    $(document).on('click', (e) => {
      if (this.openSidebar) {
        const sidebar =
          this.elementRef.nativeElement.querySelector('#sidebar-content');

        const buttonEl =
          this.elementRef.nativeElement.querySelector('#mobile-sidebar');
        if (sidebar && buttonEl) {
          if (!buttonEl.contains(e.target) && !sidebar.contains(e.target)) {
            this.openSidebar = false;
          }
        }
      }
    });
  }

  whenRouteChange() {
    this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        this.navigationService.observe(event.url);
      }
    });
  }
}
