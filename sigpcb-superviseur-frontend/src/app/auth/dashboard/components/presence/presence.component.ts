import { Component, EventEmitter, OnDestroy, Output } from '@angular/core';
import { CodeInspectionService } from 'src/app/core/services/code-inspection.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { PhotoService } from 'src/app/core/services/photo.service';
import { redirectTo } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-presence',
  templateUrl: './presence.component.html',
  styleUrls: ['./presence.component.scss'],
})
export class PresenceComponent implements OnDestroy {
  @Output('signature') onSignature = new EventEmitter<boolean>();
  canScan = false;
  result: {
    nom: string;
    prenoms: string;
    npi: string;
    avatar: string;
    examen_id: string;
    candidat_salle_id: string;
    salle_id: string;
  } | null = null;

  stopScan = false;
  constructor(
    private readonly codeInspectionService: CodeInspectionService,
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly photoService: PhotoService
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
    this.codeInspectionService
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
      this.errorHandler.startLoader();
      const formData = new FormData();
      formData.append('candidat_salle_id', this.result.candidat_salle_id);
      formData.append('examen_id', this.result.examen_id);
      formData.append('salle_compo_id', this.result.salle_id);

      this.codeInspectionService
        .emarges(formData)
        .pipe(
          this.errorHandler.handleServerErrors((error) => {
            this.result = null;
            redirectTo('/dashboard', 3000);
          })
        )
        .subscribe((response) => {
          this.onSignature.emit(true);
          this.errorHandler.stopLoader();
          this.result = null;
        });
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
      });
    }
  }
}
