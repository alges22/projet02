import { Component, OnInit } from '@angular/core';
import { CandidatData } from 'src/app/core/interfaces/user.interface';
import { ConduiteInspectionService } from 'src/app/core/services/conduite-inspection.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { PhotoService } from 'src/app/core/services/photo.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-dash-home',
  templateUrl: './dash-home.component.html',
  styleUrls: ['./dash-home.component.scss'],
})
export class DashHomeComponent implements OnInit {
  activeTab: string = 'listeEmargement';
  openStat = false;
  modalJury = 'jury';
  auth: any = null;
  jurys: any;
  sessions: any;
  session = '';
  jury = '';
  onLoading = false;
  ontEmarges: any[] = [];
  canEmarge = false;
  recapts = {} as {
    session: {
      id: number;
      label: string;
    };
    jury: {
      id: number;
      name: string;
      annexe: { id: number; name: string } | null;
    };
    examinateur: string;
    epreuve: string;
  };

  vagues_agendas = [] as {
    date: string;
    candidats_count: number;
    vagues_count: string;
  }[];

  stats = {
    jour_total: 0,
    candidat_total: 0,
    vague_total: 0,
    absents: 0,
    candidat_emages: 0,
  };

  vagues = [] as any[];
  candidats = [] as CandidatData[];

  selectedIndex = null as null | number;
  currentSession: any = null;
  onLoadCurrentSession = true;

  onLoadCandidats = true;

  onLoadRecapts = true;
  onLoadAgendas = true;
  onLoadVagues = true;
  onStoppingCompo = false;
  abscenceId = 0;
  cache: {
    auto_ecole_id: number;
    name: string;
    candidats: CandidatData[];
  }[] = [];
  constructor(
    private readonly storage: StorageService,
    private readonly conduiteInspection: ConduiteInspectionService,
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly photoService: PhotoService
  ) {}
  ngOnInit(): void {
    this.auth = this.storage.get('auth');
    this._getSessions();
  }

  setActiveTab(tab: string) {
    this.activeTab = tab; // Définissez l'onglet actif lorsqu'un en-tête de tabulation est cliqué
  }

  private _getSessions() {
    this.conduiteInspection
      .getSessions()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.sessions = response.data;
        $(`#${this.modalJury}`).modal('show');
      });
  }

  selectSession(sessionId: any): void {
    if (sessionId) {
      this._getJurys(sessionId);
    } else {
      this.jurys = [];
    }
  }

  private _getJurys(sessionId: number) {
    this.conduiteInspection
      .getJurys(sessionId)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.jurys = response.data;
      });
  }

  private _getRecpats(data: any) {
    this.onLoadRecapts = true;
    this.conduiteInspection
      .getRecpats(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadRecapts = false;
        })
      )
      .subscribe((response) => {
        this.recapts = response.data;
        this.onLoadRecapts = false;
      });
  }

  private _getAgendas(data: any) {
    this.onLoadAgendas = true;
    this.conduiteInspection
      .getAgendas(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadAgendas = false;
        })
      )
      .subscribe((response) => {
        const data = response.data;
        this.vagues_agendas = data.agendas;
        this.stats.candidat_total = data.candidats_total;
        this.stats.vague_total = data.vagues_total;
        this.stats.jour_total = data.date_count;
        this.stats.candidat_emages = data.candidat_emages;
        this.onLoadAgendas = false;
      });
  }

  private _getVagues(data: any) {
    this.onLoadVagues = true;
    this.conduiteInspection
      .getVagues(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadVagues = false;
        })
      )
      .subscribe((response) => {
        const data = response.data;
        this.vagues = data;
        this.onLoadVagues = false;
        $(`#${this.modalJury}`).modal('hide');
      });
  }

  private _getCandidats(data: any) {
    this.errorHandler.startLoader();
    this.conduiteInspection
      .getCandidats(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoading = false;
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        const data = response.data;
        this.candidats = data;
        this.fetchPhotos();
        this.updateCache();
        this.errorHandler.stopLoader();
      });
  }

  openVague(i: number) {
    if (this.selectedIndex === i) {
      this.selectedIndex = null;
    } else {
      this.selectedIndex = i;
    }
  }

  continue(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    let data: any = {};
    data.jury_id = this.jury;
    data.examen_id = this.session;
    this._getRecpats(data);
    this._getCandidats(data);
    this._getAgendas(data);
    this._getVagues(data);
  }

  markAsAbscent(candidat: CandidatData) {
    this.errorHandler.startLoader();
    this.conduiteInspection
      .markAsAbscent(candidat.npi)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.candidats = this.candidats.map((c) => {
          if (c.id === candidat.id) {
            c.dossier_session.presence_conduite = 'absent';
          }
          return c;
        });

        this.updateCache();
        emitAlertEvent(response.message, 'success');
        this.errorHandler.stopLoader();
      });
  }

  private emarges(candidat: CandidatData) {
    this.errorHandler.startLoader('Signature en cours ...');
    const formData = new FormData();
    formData.append('jury_candidat_id', candidat.id as any);
    formData.append('npi', candidat.npi);
    this.conduiteInspection
      .emarges(formData)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        //candidats
        this.candidats = this.candidats.map((c) => {
          if (c.id === candidat.id) {
            c.dossier_session.presence_conduite = 'present';
          }
          return c;
        });
        this.updateCache();
        this.errorHandler.stopLoader();
      });
  }

  onPresent(npi: string) {
    const candidat = this.candidats.find((c) => c.npi === npi);

    if (candidat) {
      candidat.dossier_session.presence_conduite = 'present';
      this.emarges(candidat);
    }
  }

  stopComp() {
    this.onStoppingCompo = true;
    this.conduiteInspection
      .stopCompo({
        jury_id: this.jury,
        examen_id: this.session,
      })
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onStoppingCompo = false;
        })
      )
      .subscribe((response) => {
        this.onStoppingCompo = false;
        emitAlertEvent(response.message, 'success');

        setTimeout(() => {
          window.location.reload();
        }, 1000);
      });
    // }
  }

  onfinished() {
    let data: any = {};
    data.jury_id = this.jury;
    data.examen_id = this.session;
    this._getCandidats(data);
  }

  get presenceDone() {
    return this.candidats.every((c) => !!c.dossier_session.presence_conduite);
  }

  private updateCache() {
    const ordered: {
      auto_ecole_id: number;
      name: string;
      candidats: CandidatData[];
    }[] = [];
    for (const candidat of this.candidats) {
      const existingAutoEcole = ordered.find(
        (ae) => ae.auto_ecole_id === candidat.auto_ecole_id
      );
      if (existingAutoEcole) {
        existingAutoEcole.candidats.push(candidat);
      } else {
        ordered.push({
          auto_ecole_id: candidat.auto_ecole_id,
          name: candidat.auto_ecole_name,
          candidats: [candidat],
        });
      }
    }
    this.cache = ordered;
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
