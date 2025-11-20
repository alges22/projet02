import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { Ae, AutoEcole } from '../../interfaces/user.interface';
import { AeService } from '../../services/ae.service';
import { HttpErrorHandlerService } from '../../services/http-error-handler.service';

@Component({
  selector: 'app-auto-ecole',
  templateUrl: './auto-ecole.component.html',
  styleUrls: ['./auto-ecole.component.scss'],
})
export class AutoEcoleComponent implements OnInit {
  aes: AutoEcole[] = [];
  @Input('btn-class') className = 'btn-secondary';
  @Input() ae: AutoEcole | null = null;
  currentAe: Ae | null = null;
  constructor(
    private aeService: AeService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.aeService
      .getAes()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((aes) => {
        this.aes = aes;
        this.currentAe = this.aeService.getAe();
        if (this.currentAe) {
          this.ae =
            this.aes.find((ae) => ae.id == this.currentAe?.auto_ecole_id) ||
            null;
        } else {
          this.ae = this.aes[0] || null;
        }
      });
  }
  onSelect(id: number): void {
    const ae = this.aes.find((a) => a.id == id);

    if (ae) {
      this.ae = ae;
      const licence = ae.licence;
      this.aeService.select(
        {
          name: ae.name,
          codeLicence: !!licence ? licence.code : 'Non disponible',
          auto_ecole_id: ae.id,
          codeAgrement: ae.agrement.code,
          endLicence: !!licence ? licence.date_fin : 'Non disponible',
          annexe: {
            name: !!ae.annexe ? ae.annexe.name : 'Innconue',
            phone: !!ae.annexe ? ae.annexe.phone : null,
            email: !!ae.annexe ? ae.annexe.email : null,
          },
        },
        true
      );
    } else {
      this.ae = null;
      this.aeService.select(null);
    }
  }
}
