import { Component } from '@angular/core';
import { AuthService } from 'src/app/core/services/auth.service';

@Component({
  template: `<div
    class="d-flex justify-content-center align-items-center vh-100 text-center"
  >
    Logout ...
  </div>`,
})
export class LogoutComponent {
  constructor(private authService: AuthService) {}
  ngOnInit(): void {
    this.authService.logout();
    window.location.href = '/';
  }
}
