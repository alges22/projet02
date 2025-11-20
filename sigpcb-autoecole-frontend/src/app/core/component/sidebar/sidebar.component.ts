import {
  Component,
  ElementRef,
  Input,
  SimpleChanges,
  ViewChild,
} from '@angular/core';
import { redirectTo } from 'src/app/helpers/helpers';
import { AuthService } from '../../services/auth.service';
import { HttpErrorHandlerService } from '../../services/http-error-handler.service';
import { Ae, AutoEcole } from '../../interfaces/user.interface';
import { AeService } from '../../services/ae.service';
import * as bootstrap from 'bootstrap';

@Component({
  selector: 'app-sidebar',
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.scss'],
})
export class SidebarComponent {
  @Input() auth: any = null;
  @Input() toggle = false;
  @ViewChild('sidebar') sidebarRef!: ElementRef<HTMLElement>;
  @ViewChild('suiviModalRef') suiviModalRef!: ElementRef<HTMLElement>; //
  aes: AutoEcole[] = [];
  selectedAe: number | null = null;

  constructor(
    private authService: AuthService,
    private errorHandler: HttpErrorHandlerService,
    private aeService: AeService
  ) {}
  ngOnInit(): void {
    this.auth = this.authService.auth();
  }
  ngOnChanges(changes: SimpleChanges): void {
    if (changes['toggle'] && !changes['toggle'].firstChange) {
      this.toggleSidebar();
    }
  }
  userConnected() {
    return !!this.auth;
  }

  toggleSidebar() {
    this.sidebarRef.nativeElement.classList.toggle('active');
  }
  monitoring() {
    if (!this.auth) {
      redirectTo('/dashboard');
    } else {
      this.authService
        .monitoringAes({
          npi: this.auth.npi,
        })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.aes = response.data;
          if (this.suiviModalRef?.nativeElement) {
            const modal = new bootstrap.Modal(this.suiviModalRef.nativeElement);
            modal.show();
          }
        });
    }
  }
  continous() {
    let modal: any;
    if (this.suiviModalRef?.nativeElement) {
      modal = new bootstrap.Modal(this.suiviModalRef.nativeElement);
    }
    if (this.selectedAe) {
      const ae: any = this.aes.find((a) => a.id == this.selectedAe);
      if (ae) {
        const licence = ae.licence;
        this.aeService.select({
          name: ae.name,
          auto_ecole_id: ae.id,
          codeAgrement: ae.agrement?.code,
          codeLicence: !!licence ? licence.code : 'Non disponible',
          endLicence: !!licence ? licence.date_fin : 'Non disponible',
          annexe: {
            name: !!ae.annexe ? ae.annexe.name : 'Innconue',
            phone: !!ae.annexe ? ae.annexe.phone : null,
            email: !!ae.annexe ? ae.annexe.email : null,
          },
        });

        if (ae.is_owner) {
          this.auth.type = 'promoteur';
        } else {
          this.auth.type = 'moniteur';
        }
        this.authService.storageService().remove('auth');
        this.authService.storageService().store<any>('auth', this.auth);

        redirectTo('/gestions/monitoring');
      }
    }
    modal?.hide();
  }
}
