import { Component, ElementRef, AfterViewInit, OnInit } from '@angular/core';
import { DossierSession } from 'src/app/core/interfaces/dossier-candidat';
import { Ae } from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { dateCounter } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-gestion',
  templateUrl: './gestion.component.html',
  styleUrls: ['./gestion.component.scss'],
})
export class GestionComponent implements AfterViewInit, OnInit {
  constructor(
    private authService: AuthService,
    private errorHandler: HttpErrorHandlerService,
    private candidatService: CandidatService,
    private elementRef: ElementRef<HTMLElement>
  ) {}
  auth: any;
  openSidebar = false;
  expireLicence: number | null = null;
  licenceTextColor = 'muted';
  onSearching = false;
  searchInput: string = '';
  candidatFound: DossierSession[] = [];
  ae: Ae | null = null;
  toggleSidebar(): void {
    this.openSidebar = !this.openSidebar;
  }
  ngOnInit(): void {
    this.ae = this.authService.storageService().get('ae');
    if (this.ae) {
      this.expireLicence = dateCounter(this.ae.endLicence);
      if (this.expireLicence < 10) {
        this.licenceTextColor = 'danger';
      } else if (this.expireLicence < 30) {
        this.licenceTextColor = 'warning';
      } else {
        this.licenceTextColor = 'success';
      }
    }
    this._profile();
  }
  ngAfterViewInit(): void {
    this.closeSidebarOnOutsideClick();
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

  private _profile() {
    this.auth = this.authService.auth();

    this.authService
      .profile()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.auth = response.data;
      });
  }

  search() {
    if (this.searchInput.length > 2) {
      this.onSearching = true;
      this.candidatService
        .getDossiers([
          {
            search: this.searchInput,
          },
        ])
        .subscribe((response) => {
          this.candidatFound = response.data.paginate_data.data;
          this.onSearching = false;

          $('#search-modal').modal('show');
        });
    }
  }
}
