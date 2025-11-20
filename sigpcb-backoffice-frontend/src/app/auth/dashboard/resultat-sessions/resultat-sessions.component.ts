import { Component, Input, OnInit } from '@angular/core';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { Agenda } from 'src/app/core/interfaces/examens';
import {
  Resultat,
  StatCode,
  StatConduite,
} from 'src/app/core/interfaces/resultats';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ResultatService } from 'src/app/core/services/resultat.service';

@Component({
  selector: 'app-resultat-sessions',
  templateUrl: './resultat-sessions.component.html',
  styleUrls: ['./resultat-sessions.component.scss'],
})
export class ResultatSessionsComponent implements OnInit {
  /**
   * Les paramètres de filtrage
   */
  filter = {
    annexe_id: null as null | number,
    examen_id: 0,
    presence_conduite: null,
    resultat_conduite: null,
    resultat_code: null,
  };
  statConduite: StatConduite | null = null;
  statCode: StatCode | null = null;
  ready = false;

  resultats: any[] = [];
  conduiteReady = false;
  codeReady = false;
  activeTab: string = 'admis-conduite';

  @Input('examen') examen!: Agenda;
  annexe: AnnexeAnatt | null = null;
  @Input() annexes: AnnexeAnatt[] = [];
  constructor(
    private resultatService: ResultatService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.filter.examen_id = this.examen.id;
    this.fetch();
  }

  setActiveTab(tab: string): void {
    this.activeTab = tab;
    this.fetch();
    this.getResultats();
  }

  fetch() {
    if (this.filter.annexe_id && this.filter.examen_id) {
      this._getStatConduite();
      this._getStatCode();
    }
  }

  private _getStatCode() {
    this.ready = false;
    this.resultatService
      .statCode([
        {
          annexe_id: this.filter.annexe_id,
          examen_id: this.filter.examen_id,
        },
      ])
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.ready = true;
        })
      )
      .subscribe((response) => {
        this.ready = true;
        this.statCode = response.data;
      });
  }

  private _getStatConduite() {
    this.ready = false;
    this.resultatService
      .statConduite([
        {
          annexe_id: this.filter.annexe_id,
          examen_id: this.filter.examen_id,
        },
      ])
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.ready = true;
        })
      )
      .subscribe((response) => {
        this.ready = true;
        this.statConduite = response.data;
      });
  }

  conduites() {
    this.errorHandler.startLoader('Chargement du résultat de conduite');
    this.conduiteReady = false;
    this.resultatService
      .conduites([this.filter])
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.conduiteReady = true;
        })
      )
      .subscribe((response) => {
        this.conduiteReady = true;
        if (response.status) {
          this.resultats = response.data;
        }
        this.errorHandler.stopLoader();
      });
  }

  codes() {
    this.errorHandler.startLoader('Chargement du résultat du code');
    this.codeReady = false;
    this.resultatService
      .codes([this.filter])
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.codeReady = true;
        })
      )
      .subscribe((response) => {
        this.codeReady = true;

        if (response.status) {
          this.resultats = response.data;
        }
        this.errorHandler.stopLoader();
      });
  }
  annexeSelected(event: any): void {
    this.filter.annexe_id = event.target.value;

    this.annexe =
      this.annexes.find((annexe) => annexe.id === this.filter.annexe_id) ||
      null;
    this.fetch();
    this.getResultats();
  }

  private getResultats() {
    if (this.filter.annexe_id && this.filter.examen_id) {
      if (this.activeTab == 'admis-code') {
        this.codes();
      } else {
        this.conduites();
      }
    }
  }
}
