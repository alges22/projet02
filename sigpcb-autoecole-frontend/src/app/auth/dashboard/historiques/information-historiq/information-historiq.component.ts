import { Component, EventEmitter, Input, Output } from '@angular/core';
import { Notification } from 'src/app/core/interfaces/user.interface';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { PdfService } from 'src/app/core/services/pdf.service';

@Component({
  selector: 'app-information-historiq',
  templateUrl: './information-historiq.component.html',
  styleUrls: ['./information-historiq.component.scss'],
})
export class InformationHistoriqComponent {
  @Input() active = false;
  @Input() pos = 0;
  @Input() event_at: string | null = null;
  @Output() onActive = new EventEmitter<{ pos: number; active: boolean }>();
  @Input() historiques: Notification[] = [];
  centerButton: {
    type: 'link' | 'modal' | 'method';
    text: string;
    id?: string;
    href?: string;
    meta: any;
  } | null = null;
  lastAction: {
    theme: 'success' | 'warning' | 'danger';
    label: string;
  } = {
    theme: 'warning',
    label: 'En cours',
  };
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private pdf: PdfService
  ) {}

  ngOnInit(): void {
    this.setLastAction();
  }
  toggle() {
    this.active = !this.active;
    this.onActive.emit({
      active: this.active,
      pos: this.pos,
    });
  }

  private setLastAction() {
    const historiques = this.historiques.sort((a, b) => {
      const dateA = new Date(a.created_at).getTime();
      const dateB = new Date(b.created_at).getTime();

      return dateB - dateA;
    });

    if (historiques.length) {
      const h = historiques[0];

      if (h) {
        switch (h.action) {
          case 'information-update-rejected':
            this.lastAction = {
              theme: 'danger',
              label: 'Rejetée',
            };
            this.centerButton = {
              type: 'link',
              text: 'Corriger la demande',
              meta: h.meta,
              href: '/dashboard/my-informations/' + h.meta.demandeRejet.id,
            };
            break;
          case 'information-update-validate':
            this.lastAction = {
              theme: 'success',
              label: 'Mise à jour',
            };
            break;

          default:
            break;
        }
      }
    }
  }
  download(demand: any) {
    this.errorHandler.startLoader('Téléchargement ...');

    let param: Record<string, string> = {
      licenceId: demand,
    };

    this.pdf
      .download(param, 'licence')
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        window.open(response.data, '_blank');
      });
  }
}
