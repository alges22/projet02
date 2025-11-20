import { User } from 'src/app/core/interfaces/user.interface';
import { AuthService } from './../../core/services/auth.service';
import { AfterViewInit, Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-auth-navbar',
  templateUrl: './auth-navbar.component.html',
  styleUrls: ['./auth-navbar.component.scss'],
})
export class AuthNavbarComponent implements OnInit, AfterViewInit {
  auth: any;
  constructor(private authservice: AuthService) {}
  ngOnInit(): void {
    this.auth = this.authservice.auth();
  }
  ngAfterViewInit(): void {
    const mobileNavBtn = document.getElementById('mobile-nav');
    const menu = document.getElementById('side-menu');
    if (mobileNavBtn) {
      $(mobileNavBtn).on('click', (e) => {
        e.stopPropagation();
        this.toggleSidebar(menu, mobileNavBtn);
      });
    }

    document.addEventListener('click', (e) => {
      if (menu && !$(menu).hasClass('d-none')) {
        const target = e.target as HTMLElement;
        if (!menu.contains(target)) {
          this.toggleSidebar(menu, mobileNavBtn);
        }
      }
    });
  }

  private toggleSidebar(menu: any, mobileNavBtn: any) {
    if (menu) {
      $(menu).toggleClass('d-none');
      const jqIcon = $(mobileNavBtn).find('.bi');

      if (jqIcon.hasClass('bi-list')) {
        jqIcon.removeClass('bi-list');
        jqIcon.addClass('bi-x');
      } else {
        jqIcon.removeClass('bi-x');
        jqIcon.addClass('bi-list');
      }
    }
  }
}
