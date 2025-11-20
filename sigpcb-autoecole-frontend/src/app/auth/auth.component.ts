import { Ae } from './../core/interfaces/user.interface';
import { Component, OnInit } from '@angular/core';
import { AuthService } from '../core/services/auth.service';
import { HttpErrorHandlerService } from '../core/services/http-error-handler.service';
import { AutoEcole } from '../core/interfaces/user.interface';
import { AeService } from '../core/services/ae.service';
import { replaceSpace } from '../helpers/helpers';

@Component({
  selector: 'app-auth',
  templateUrl: './auth.component.html',
  styleUrls: ['./auth.component.scss'],
})
export class AuthComponent implements OnInit {
  aes: AutoEcole[] = [];
  ae: Ae | null = null;
  phone: string | null = null;
  constructor(
    private authService: AuthService,
    private errorHandler: HttpErrorHandlerService,
    private aeService: AeService
  ) {}
  ngOnInit(): void {
    this.authService
      .profile()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const aes: AutoEcole[] = response.data.auto_ecoles ?? [];
        this.aeService.setAes(aes ?? []);
        const ae = this.aeService.getAe();
        if (!ae) {
          if (aes) {
            if (aes.length) {
              const autoEcole = aes[0];
              const licence = autoEcole.licence;
              let ae = {
                codeAgrement: autoEcole.agrement.code,
                codeLicence: !!licence ? licence.code : 'Non disponible',
                endLicence: !!licence ? licence.date_fin : 'Non disponible',
                auto_ecole_id: autoEcole.id,
                name: autoEcole.name,
                annexe: {
                  name: !!autoEcole.annexe ? autoEcole.annexe.name : 'Innconue',
                  phone: !!autoEcole.annexe ? autoEcole.annexe.phone : null,
                  email: !!autoEcole.annexe ? autoEcole.annexe.email : null,
                },
              };

              this.aeService.select(ae);
              this.ae = ae;

              if (this.ae) {
                if (this.ae.annexe) {
                  if (this.ae.annexe.phone) {
                    this.phone = this.ae.annexe.phone;
                  }
                }
              }
            }
          }
        }
      });
  }

  openWhatsApp() {
    if (this.ae && this.phone) {
      const text = encodeURIComponent(
        `Bonjour CA ANaTT,\n\Je vous écris de l'école: *${this.ae?.name}*,\ncode agrément: *${this.ae?.codeAgrement}*\n\n`
      );
      window.open(
        'https://wa.me/' +
          '+229' +
          replaceSpace(this.phone, '') +
          '?text=' +
          text,
        '_blank'
      );
    }
  }
}
