import { Component } from '@angular/core';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss'],
})
export class DashboardComponent {
  constructor(
    private candidatService: CandidatService,
    private authService: AuthService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this._getUserPermis();
  }
  private _getUserPermis() {
    this.candidatService
      .getUserPermis()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          let has_permis: boolean;
          if (response.data.length) {
            has_permis = true;
          } else {
            has_permis = false;
          }
          this.authService.storageService().store('permis', {
            has_permis: has_permis,
          });
        }
      });
  }
}
