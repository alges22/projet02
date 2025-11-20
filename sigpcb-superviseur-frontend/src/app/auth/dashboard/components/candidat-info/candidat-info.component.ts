import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { CandidatData } from 'src/app/core/interfaces/user.interface';
import { CodeInspectionService } from 'src/app/core/services/code-inspection.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { PhotoService } from 'src/app/core/services/photo.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-candidat-info',
  templateUrl: './candidat-info.component.html',
  styleUrls: ['./candidat-info.component.scss'],
})
export class CandidatInfoComponent implements OnInit {
  @Input('data') data: any;
  @Input() body: any = {};
  @Input() candidats: CandidatData[] = [];
  selectedForEmargeId: number | null = null;
  canEmarge = false;
  @Output('refresh') onRefreshEvent = new EventEmitter<'agendas' | 'vagues'>();
  @Output('signature') onSignature = new EventEmitter<boolean>();
  presence = null as 'present' | 'abscent' | null;
  @Input() status = 'new';
  @Input() page = 'all' as 'all' | 'emargement';
  confirmed = false;
  @Input() ready = false;
  constructor(
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly codeInspection: CodeInspectionService,
    private readonly photoService: PhotoService
  ) {}
  ngOnInit(): void {
    this.fetchPhotos();
  }

  private requestBody(data: any) {
    let body = {};
    if (this.body) {
      body = { ...data, ...this.body };
    }
    return body;
  }

  confirmation() {
    this.confirmed = !this.confirmed;
  }

  getProgression(item: any) {
    const response_count = item.reponses_count ?? 0;
    const total = item.questions_count;

    if (total < 1) {
      return 0;
    }

    const p = (response_count / total) * 100;
    return p;
  }

  hide(item: CandidatData) {
    //Si questions_count est null et item.presence existe cela veut dire qu'on est entrain de marquer la présence

    if (this.page == 'all') {
      if (this.status === 'pending') {
        if (item.closed) {
          return true;
        }
      }
      if (this.ready) {
        return false;
      }
      return item.closed || (!item.questions_count && item.presence);
    }
    return false;
  }

  openSession(candidat: any) {
    if (!candidat) {
      emitAlertEvent(
        'Candidat introuvable dans la liste de vos candidats, vérifiez le numéro NPI',
        'danger',
        'middle',
        true
      );
      return;
    }
    this.errorHandler.startLoader('Ouverture de session en cours ...');
    if (candidat) {
      this.codeInspection
        .openSession(
          this.requestBody({
            candidat_salle_id: candidat.id,
          })
        )
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          emitAlertEvent(response.message, 'success');
          this.errorHandler.stopLoader();
          this.onRefreshEvent.emit();
        });
    }
  }

  private fetchPhotos() {
    this.photoService
      .get(this.candidats.map((candidat) => candidat.npi))
      .subscribe((response) => {
        this.candidats = this.candidats.map((candidat) => {
          const photos = response.data as { npi: string; image: string }[];
          if (photos) {
            const photo = photos.find((p) => p.npi == candidat.npi);
            if (photo?.image && photo.image.length > 10) {
              candidat.candidat.avatar = 'data:image/png;base64,' + photo.image;
            }
          }

          return candidat;
        });
      });
  }
}
