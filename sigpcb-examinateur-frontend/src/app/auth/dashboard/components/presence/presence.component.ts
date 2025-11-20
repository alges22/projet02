import { Component, EventEmitter, OnDestroy, Output } from '@angular/core';
import { ConduiteInspectionService } from 'src/app/core/services/conduite-inspection.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { PhotoService } from 'src/app/core/services/photo.service';
import { ChangeDetectorRef } from '@angular/core';

@Component({
  selector: 'app-presence',
  templateUrl: './presence.component.html',
  styleUrls: ['./presence.component.scss'],
})
export class PresenceComponent implements OnDestroy {
  @Output('signature') onSignature = new EventEmitter<string>();
  canScan = false;
  result: {
    nom: string;
    prenoms: string;
    npi: string;
    avatar: string;
    jury_candidat_id: string;
  } | null = null;

  stopScan = false;
  constructor(
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly conduiteInspection: ConduiteInspectionService,
    private readonly photoService: PhotoService,
    private readonly cdr: ChangeDetectorRef  // Ajout de ChangeDetectorRef
  ) {}

  scan() {
    this.stopScan = false;
    this.reset();
    $('#presence').modal('show');
  }

  ngOnDestroy(): void {
    $('#presence').modal('hide');
  }

  private reset() {
    this.canScan = true;
  }

  verifyCandidat(code: string) {
    this.errorHandler.startLoader('Vérification en cours ...');
    this.conduiteInspection
      .verifyCandidat({ code: code })
      .pipe(
        this.errorHandler.handleServerErrors(() => {
          this.result = null;
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        this.result = response.data;
        this.fetchPhoto();
        this.errorHandler.stopLoader();
      });
  }

  validate() {
    if (this.result) {
      this.onSignature.emit(this.result.npi);
      setTimeout(() => {
        this.result = null;
      }, 3000);
      // Ferme le modal après validation
      $('#presence').modal('hide');
    }
  }

  cancel() {
    this.stopScan = true;
    this.result = null;
  }

  private fetchPhoto() {
    if (this.result) {
      const result = this.result;
      this.photoService.get([this.result.npi]).subscribe((response) => {
        const photos = response.data as { npi: string; image: string }[];
        if (photos) {
          const photo = photos.find((p) => p.npi == result.npi);
          if (photo?.image && photo.image.length > 10) {
            result.avatar = 'data:image/png;base64,' + photo.image;
          }
        }
        this.result = result;
        this.cdr.detectChanges();  // Force Angular to detect changes and update the view
      });
    }
  }
}
