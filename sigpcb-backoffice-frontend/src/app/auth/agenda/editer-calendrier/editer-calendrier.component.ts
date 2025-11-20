import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { Component, OnInit } from '@angular/core';
import { Agenda } from 'src/app/core/interfaces/examens';
import { AgendaService } from 'src/app/core/services/agenda.service';
import { DateService } from 'src/app/core/services/date.service';
import { FullMonth } from 'src/app/core/interfaces/date';
import { ActivatedRoute, Router } from '@angular/router';
import {
  getHoursFromDate,
  isDateValid,
  ucfirst,
} from 'src/app/helpers/helpers';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';

@Component({
  selector: 'app-editer-calendrier',
  templateUrl: './editer-calendrier.component.html',
  styleUrls: ['./editer-calendrier.component.scss'],
})
export class EditerCalendrierComponent implements OnInit {
  start_month: FullMonth = 'Janvier';
  agenda = { numero: 1, type: 'ordinaire' } as Agenda;
  onPosting = false;
  heading = 'Ajouter une session';
  hours: Record<string, string> = {
    date_code: '00:00:00',
    date_conduite: '00:00:00',
    debut_etude_dossier_at: '00:00:00',
    fin_etude_dossier_at: '00:00:00',
    debut_gestion_rejet_at: '00:00:00',
    fin_gestion_rejet_at: '00:00:00',
    date_convocation: '00:00:00',
  };

  fields = [
    'date_code',
    'date_conduite',
    'debut_etude_dossier_at',
    'fin_etude_dossier_at',
    'debut_gestion_rejet_at',
    'fin_gestion_rejet_at',
    'date_convocation',
  ];
  concernedAnnexes: AnnexeAnatt[] = [];
  annexes: AnnexeAnatt[] = [];
  showAnnexeList: boolean = false;
  constructor(
    private agendaService: AgendaService,
    private errorHandler: HttpErrorHandlerService,
    private dateService: DateService,
    private route: ActivatedRoute,
    private router: Router,
    private annexeService: AnnexeAnattService
  ) {}
  ngOnInit(): void {
    this.agenda.annee = new Date().getFullYear();
    this.start_month = this.dateService.getFullMonthNow();
    this.getAnnexeAnatt();
    const agendaId = this.route.snapshot.paramMap.get('id') as any;
    if (agendaId) {
      this.agendaService
        .findById(agendaId)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.agenda = this._convertAgendaAttr(response.data);

          this.agenda.mois =
            this.dateService.findOrCorrect(ucfirst(this.agenda.mois)) ||
            this.agenda.mois;
        });
    }
  }
  save(event: Event) {
    this.errorHandler.clearServerErrorsMessages('editer-calendrier');
    event.preventDefault();
    this.mapAgenda();
    this.onPosting = true;
    if (!this.agenda.id) {
      this.agendaService
        .post(this.mapAgenda())
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.onPosting = false;
          }, 'editer-calendrier')
        )
        .subscribe((response) => {
          this.onPosting = false;
          this.errorHandler.emitSuccessAlert(response.message);
          this.heading = `Modifier l'agenda de la session de : <b>${this.agenda.mois}</b>`;
          this.agenda = response.data;
          this.router.navigate(['/agendas/session-examens']);
        });
    } else {
      this.update();
    }
  }

  private update() {
    this.agendaService
      .update(this.mapAgenda(), this.agenda.id)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onPosting = false;
        }, 'editer-calendrier')
      )
      .subscribe((response) => {
        this.onPosting = false;
        this.errorHandler.emitSuccessAlert(response.message);
        this.heading = `Modifier l'agenda de la session de : <b>${this.agenda.mois}</b>`;
        this._convertAgendaAttr(response.data);
        this.router.navigate(['/agendas/session-examens']);
      });
  }
  onMonthSelected(event: FullMonth | null) {
    this.agenda.mois = event as string;
  }

  getCurrentDateTime(): string {
    return '';
  }

  private _convertAgendaAttr(agenda: any) {
    const fields = this.fields;
    const format = (value: string) => {
      const date = new Date(value);
      const year = date.getUTCFullYear();
      const month = String(date.getUTCMonth() + 1).padStart(2, '0');
      const day = String(date.getUTCDate()).padStart(2, '0');

      return `${year}-${month}-${day}`;
    };
    for (const key in agenda) {
      if (Object.prototype.hasOwnProperty.call(agenda, key)) {
        let date_string = agenda[key] as string;

        if (fields.includes(key) && isDateValid(date_string)) {
          //Ajustement des heures
          const hour = getHoursFromDate(date_string);
          this.hours[key] = hour;

          //Ajustement des dates
          date_string = format(date_string);
        }

        agenda[key] = date_string;
      }
    }

    this.setAnnexeConcerned(agenda);

    return agenda;
  }

  private setAnnexeConcerned(agenda: any) {
    let annexeIds = agenda.annexe_ids ?? [];
    if (annexeIds) {
      if (!Array.isArray(annexeIds)) {
        try {
          annexeIds = JSON.parse(annexeIds);
        } catch (error) {}
      }
    }

    if (Array.isArray(annexeIds)) {
      for (const id of annexeIds) {
        const found = this.annexes.find((annexe) => annexe.id == id);
        if (found) {
          this.appendAnnexe(found);
        }
      }
    }
  }

  toIso(name: string, hour: string) {
    this.hours[name] = hour;
  }
  private mapAgenda() {
    let agenda: any = { ...this.agenda };

    for (const key in agenda) {
      if (Object.prototype.hasOwnProperty.call(agenda, key)) {
        if (this.fields.includes(key)) {
          agenda[key] = agenda[key] + 'T' + this.hours[key] + '.000000Z';
        } else {
          agenda[key] = agenda[key];
        }
      }
    }
    agenda['annexe_ids'] = this.concernedAnnexes.map((annexe) => annexe.id);
    return agenda;
  }

  appendAnnexe(annexe: AnnexeAnatt) {
    const index = this.concernedAnnexes.findIndex(
      (item) => item.id === annexe.id
    );

    if (index !== -1) {
      // Si l'annexe est déjà présente, on la retire
      this.concernedAnnexes.splice(index, 1);
    } else {
      // Sinon, on l'ajoute
      this.concernedAnnexes.push(annexe);
    }
  }

  private getAnnexeAnatt() {
    this.annexeService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.annexes = response.data;
        if (this.agenda) {
          this.setAnnexeConcerned(this.agenda);
        }
      });
  }

  selectAllAnnexe() {
    if (this.allSelected()) {
      this.concernedAnnexes = [];
    } else {
      this.concernedAnnexes = [...this.annexes];
    }
  }

  allSelected() {
    return this.concernedAnnexes.length === this.annexes.length;
  }

  checked(annexe: AnnexeAnatt) {
    return !!this.concernedAnnexes.find((a) => a.id == annexe.id);
  }

  get annexeLabel() {
    if (this.concernedAnnexes.length > 0) {
      if (this.allSelected()) {
        return 'Toutes les annexes';
      }
      if (this.concernedAnnexes.length == 1) {
        return this.concernedAnnexes[0].name;
      }

      return `(${this.concernedAnnexes.length}) sélectionnées`;
    }
    return 'Sélectionner une annexe';
  }
}
