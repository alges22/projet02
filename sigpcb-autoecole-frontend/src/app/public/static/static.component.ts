import { Component, Input, OnInit } from '@angular/core';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-static-module',
  templateUrl: './static.component.html',
  styleUrls: ['./static.component.scss'],
})
export class StaticComponent implements OnInit {
  authentificated: boolean = false;
  @Input('auth') auth: any = null;
  _toggleSidebar = false;

  constructor(
    private authService: AuthService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.authentificated = this.authService.checked();
    this.auth = this.authService.auth();
    if (this.authentificated) {
      this.authService
        .profile()
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {});
    }
  }
  toggleSidebar() {
    this._toggleSidebar = !this._toggleSidebar;
  }
}
