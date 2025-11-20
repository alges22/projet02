import { Component, EventEmitter, Input, Output } from '@angular/core';
import { environment } from 'src/environments/environment';

import { Dossier } from 'src/app/core/interfaces/candidat';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
type State = 'validate' | 'rejected' | 'failed';
@Component({
  selector: 'app-agreement-fiche',
  templateUrl: './agreement-fiche.component.html',
  styleUrls: ['./agreement-fiche.component.scss'],
})
export class AgreementFicheComponent {
  // @Output('validate') validateEvent = new EventEmitter<{
  //   aggreementId: number;
  //   state: State;
  //   agrement: any;
  // }>();
  // @Input('data') data!: Agreement;
  // constructor() {}

  // //agrement
  // agrement: any = null;

  // @Input() page = 'init' as 'validate' | 'rejected' | 'init';

  // dossier: Dossier | null = null;
  // //
  // onValidate() {
  //   this.validateEvent.emit({
  //     aggreementId: this.data.id,
  //     state: 'validate',
  //     agrement: this.agrement,
  //   });
  // }

  // onRejected() {
  //   this.validateEvent.emit({
  //     aggreementId: this.data.id,
  //     state: 'rejected',
  //     agrement: this.agrement,
  //   });
  // }
  // ngOnInit(): void {
  //   this.agrement = this.data;
  // }

  // open() {
  //   $(`#suivi-${this.data.id}`).modal('show');
  // }
  @Output('validate') validateEvent = new EventEmitter<{
    agrementId: number;
    state: State;
    agrement: any;
  }>();
  @Input('data') data: any = null;
  previewUrl = '';
  categorie_permis: CategoryPermis | null = null;
  selectedRestrictionIds: number[] = [];
  constructor() {}

  //agrement
  agrement: any = null;

  //Dossier session
  dossier_session: any = null;

  @Input() page = 'pending' as 'validate' | 'rejected' | 'pending';

  dossier: Dossier | null = null;
  //
  chapitres: any[] = [];
  onValidate() {
    this.validateEvent.emit({
      agrementId: this.data.id,
      state: 'validate',
      agrement: this.agrement,
    });
  }

  onRejected() {
    this.validateEvent.emit({
      agrementId: this.data.id,
      state: 'rejected',
      agrement: this.agrement,
    });
  }
  ngOnInit(): void {
    this.agrement = this.data;
    this.dossier_session = this.data.dossier_session;
    this.categorie_permis = this.data.categorie_permis;
    this.dossier = this.data.dossier;
    this.chapitres = this.data.chapitres ?? [];
  }

  openSuiviModal(url?: string) {
    if (url) {
      this.previewUrl = url;
    }
    $(`#suivi-${this.data.id}`).modal('show');
  }

  assets(path?: string) {
    return environment.autoecole.asset + path;
  }

  getSelectedRestrictionNames(): string {
    const selectedRestrictionNames = this.data.restrictionss.map((r: any) => {
      return r.name;
    });
    return selectedRestrictionNames.join(', ');
  }
}
