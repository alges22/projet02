import { Component, OnInit } from '@angular/core';
import { Vague } from 'src/app/core/interfaces';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Langue } from 'src/app/core/interfaces/langue';
import { CandidatData } from 'src/app/core/interfaces/user.interface';
import { CodeInspectionService } from 'src/app/core/services/code-inspection.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-dash-home',
  templateUrl: './dash-home.component.html',
  styleUrls: ['./dash-home.component.scss'],
})
export class DashHomeComponent implements OnInit {
  auth: any = null;
  body: any = null;
  timer: any;
  annexe: AnnexeAnatt | null = null;
  modalPage = 'select-annexe' as 'select-annexe' | 'salle';
  page = 'all' as 'all' | 'emargement';
  salles: any[] = [];
  showStatistics = false;
  salle: any = null;
  recapts = {} as {
    session: {
      id: number;
      label: string;
    };
    salle: {
      id: number;
      name: string;
      annexe: { id: number; name: string } | null;
    };
    inspecteur: string;
    epreuve: string;
  };

  vagues_agendas = [] as {
    date: string;
    candidats_count: number;
    vagues_count: string;
    candidats: any[];
  }[];

  stats = {
    jour_total: 0,
    candidat_total: 0,
    vague_total: 0,
    absents: 0,
    candidat_emages: 0,
  };

  vagues = [] as {
    vague: Vague;
    candidats: CandidatData[];
    candidats_count: number;
    categorie_permis: CategoryPermis;
    date_compo: string;
  }[];
  tapedNpi: string = '';
  selectedIndex = null as null | number;
  currentSession: any = null;
  onLoadRecapts = true;
  onLoadAgendas = true;
  onLoadVagues = true;
  onStartingCompo = false;
  onStoppingCompo = false;
  throwStopCompoAlert = false;
  stopped = false;
  examens: any[] = [];
  deconnexionData = {
    npi: '',
    motif: '',
    confirmed: false,
  };
  onResetting = false;
  constructor(
    private readonly storage: StorageService,
    private readonly codeInspection: CodeInspectionService,
    private readonly errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.auth = this.storage.get('auth');

    //Si les salles étaients sélectionnées
    const body = this.storage.get('body');
    if (body) {
      this.body = body;
    }

    //Récupère les salle et examens
    this._getSalles((session: any, salle: any) => {
      this.__init__(session, salle);
    });
  }

  /**
   * Récupère les récap
   */
  private _getRecpats() {
    this.onLoadRecapts = true;
    this.codeInspection
      .getRecpats(this.requestBody({}))
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

  private _getAgendas() {
    this.onLoadAgendas = true;
    this.codeInspection
      .getAgendas(this.requestBody({}))
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
        this.stats.absents = data.absents;
        this.stats.candidat_emages = data.candidat_emages;
        this.onLoadAgendas = false;
      });
  }

  private _getVagues(load = true) {
    this.onLoadVagues = load;
    this.codeInspection
      .getVagues(
        this.requestBody({
          menu: this.page,
        })
      )
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadVagues = false;
        })
      )
      .subscribe((response) => {
        const data = response.data;
        this.vagues = data;
        //Affiche uniquement les vagues actives

        if (this.page != 'emargement') {
          this.vagues = this.vagues.filter((v) => {
            return v.vague.status !== 'closed';
          });
        }
        this.onLoadVagues = false;
        if (this.vagues.length == 0) {
          clearInterval(this.timer);
        }
      });
  }

  openVague(i: number) {
    if (this.selectedIndex === i) {
      this.selectedIndex = null;
    } else {
      this.selectedIndex = i;
      this._getVagues(false);
    }
  }

  startComp() {
    if (this.status != 'new' && this.status != 'closed') {
      this.pauseCompo();
      return;
    }
    const item = this.vagues[0];
    const salle_compO = this.recapts.salle;
    if (item) {
      this.onStartingCompo = true;
      this.codeInspection
        .startCompo({
          vague_id: item.vague.id,
          salle_compo_id: salle_compO.id,
        })
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.onStartingCompo = false;
          })
        )
        .subscribe((response) => {
          this.onStartingCompo = false;
          emitAlertEvent(response.message, 'success');
        });
    }
  }

  stopCompo() {
    const item = this.vagues[0];
    const salle_compO = this.recapts.salle;

    if (item) {
      this.onStoppingCompo = true;
      this.codeInspection
        .stopCompo({
          vague_id: item.vague.id,
          salle_compo_id: salle_compO.id,
        })
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.onStoppingCompo = false;
          })
        )
        .subscribe((response) => {
          this.onStoppingCompo = false;
          emitAlertEvent(response.message, 'success');
          this.stopped = true;
          this.throwStopCompoAlert = false;
          this._getVagues();
        });
    }
  }

  refresh() {
    this._getVagues();
    this._getAgendas();
    this.freshVagueEachMin();
  }

  openDeconnectedCandidat() {
    $('#deconnecter-candidat').modal('show');
  }

  private freshVagueEachMin() {
    this.timer = setInterval(() => {
      this._getVagues(false);
    }, 10000); // 1000 millisecondes = 1 s
  }

  ngOnDestroy(): void {
    this.clearInterval();
  }

  clearInterval() {
    clearInterval(this.timer);
  }
  openSetupModal() {
    $('#setup-modal').modal('show');
  }

  private _getSalles(call: CallableFunction) {
    this.codeInspection
      .getSalles()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const data = response.data;
        this.salles = data.salles;
        this.examens = data.sessions || [];
        let salle = null;
        let examen = null;
        if (this.body) {
          salle = this.salles.find((s) => s.id == this.body.salle_compo_id);
          examen = this.examens.find((e) => e.id == this.body.examen_id);
        } else {
          this.openSetupModal();
        }

        call(examen, salle);
      });
  }

  /**
   * Sélectionner une salle à surveiller
   * @param target
   */
  selectSalle(target: any) {
    const id = target.value;
    const salle = this.salles.find((salle) => salle.id == id) || null;
    this.salle = salle;
  }

  /**
   * Sélectionner un examen
   *
   * @param target
   */
  selectExamen(target: any) {
    const id = target.value;
    const examen = this.examens.find((examen) => examen.id == id) || null;
    this.currentSession = examen;
  }
  setup() {
    this.body = {
      examen_id: this.currentSession.id,
      salle_compo_id: this.salle.id,
    };
    this.storage.store('body', this.body);
    this.__init__(this.currentSession, this.salle);
    $('#setup-modal').modal('hide');
  }

  private __init__(session: any, salle: any) {
    if (!!session && !!salle) {
      this.currentSession = session;
      this.salle = salle;
      this.body = {
        examen_id: this.currentSession.id,
        salle_compo_id: this.salle.id,
      };
      this._getRecpats();
      this._getAgendas();
      this._getVagues();
      this.freshVagueEachMin();
    }
  }

  private requestBody(data: any) {
    let body = {};
    if (this.body) {
      body = { ...data, ...this.body };
    }
    return body;
  }
  setPage(page: 'all' | 'emargement') {
    this.page = page;
    this._getVagues();
  }

  confirmDeconnection(tg: any) {
    this.deconnexionData.confirmed = tg.checked;
  }
  canDeconnectCandidat() {
    return (
      this.deconnexionData.confirmed &&
      this.deconnexionData.npi.length >= 10 &&
      !!this.deconnexionData.motif.length
    );
  }

  stopCandidatCompo() {
    const candidat = this.findCandidat(this.deconnexionData.npi);
    if (!candidat) {
      emitAlertEvent(
        'Candidat introuvable dans la liste de vos candidats, vérifiez le numéro NPI',
        'danger',
        'middle',
        true
      );
      return;
    }
    this.errorHandler.startLoader();
    this.codeInspection
      .stopCandidatCompo(
        this.requestBody({
          candidat_salle_id: candidat.id,
          motif: this.deconnexionData.motif,
        })
      )
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        $('#deconnecter-candidat').modal('hide');
        this.errorHandler.stopLoader();
        this.deconnexionData = {
          confirmed: false,
          npi: '',
          motif: '',
        };
        this._getVagues();
      });
  }

  private findCandidat(npi: string) {
    let candidat: any = null;
    for (const vague of this.vagues) {
      const candidats: any[] = vague['candidats'];
      candidat = candidats.find((c) => c.candidat.npi == npi);
      if (candidat) {
        break;
      }
    }
    return candidat;
  }
  onSignature() {
    this.clearInterval();
  }

  pauseCompo() {
    this.errorHandler.startLoader('Chargement ...');

    this.codeInspection
      .pause(
        this.requestBody({
          paused: !this.paused,
        })
      )
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        emitAlertEvent(response.message, 'success');
        this._getVagues();
      });
  }

  get paused() {
    const currentVague = this.vagues[0];
    if (currentVague && currentVague.vague) {
      return currentVague.vague.status === 'paused';
    }

    return false;
  }

  get status() {
    return this.vagues[0]?.vague?.status ?? 'new';
  }

  get presenceDone() {
    const vague = this.vagues[0];
    if (vague) {
      return vague.candidats.every((c) => c.presence);
    }

    return false;
  }

  onPresence() {
    this._getAgendas();
    this._getVagues();
  }
  resetCompo() {
    const vague = this.vagues[0];
    if (vague) {
      this.onResetting = true;
      this.codeInspection
        .resetCompo(
          this.requestBody({
            vague_id: vague.vague.id,
          })
        )
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.onResetting = false;
          })
        )
        .subscribe((response) => {
          this.onResetting = false;
          emitAlertEvent(response.message, 'success');
          this._getVagues();
          $('#reset-compo-modal').modal('hide');
        });
    }
  }
  openResetModal() {
    $('#reset-compo-modal').modal('show');
  }
}
