import { Component, Input } from '@angular/core';
import { CandidatData } from 'src/app/core/interfaces/user.interface';
import { CodeInspectionService } from 'src/app/core/services/code-inspection.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'candidat-status',
  template: `<div>
    <ng-container *ngIf="status">
      <button class="btn btn-light" disabled>
        {{ status }}
      </button>
    </ng-container>
    <ng-container *ngIf="!status">
      <div class="position-relative">
        <div>
          <button class="btn btn-warning btn-sm" (click)="confirming = true">
            Marquer absent (e)
          </button>
        </div>
        <div
          *ngIf="confirming"
          class="border d-inline-block shadow-sm p-3 my-3 position-relative  "
        >
          <h6 class="mb-2">Confirmer l'absence ?</h6>
          <button class="btn btn-success me-2" (click)="confirmAbscence()">
            Oui
          </button>
          <button class="btn btn-danger" (click)="confirming = false">
            Non
          </button>
        </div>
      </div>
    </ng-container>
  </div> `,
  styleUrls: ['./candidat.component.scss'],
})
export class CandidatComponent {
  @Input('data') data!: CandidatData;

  confirming = false;
  constructor(
    private readonly codeInspectionService: CodeInspectionService,
    private readonly errorHandler: HttpErrorHandlerService
  ) {}

  ngOnDestroy(): void {}

  get present() {
    return this.data.presence == 'present';
  }

  get absent() {
    return this.data.presence == 'abscent';
  }

  get presence() {
    if (this.present) {
      return 'Présent(e)';
    }

    if (this.absent) {
      return 'Absent(e)';
    }

    return null;
  }

  get status() {
    if (this.data.closed) {
      return 'Terminée';
    }

    if (this.data.connected) {
      return 'En cours';
    }

    return this.presence;
  }

  confirmAbscence() {
    this.errorHandler.startLoader();
    this.codeInspectionService
      .markAsAbscent({
        candidat_salle_id: this.data.id,
        examen_id: this.data.examen_id,
        salle_compo_id: this.data.salle_compo_id,
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        window.location.reload();
        this.errorHandler.stopLoader();
      });
  }
}
