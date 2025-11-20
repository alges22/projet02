import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { environment } from 'src/environments/environment';
type State = 'validate' | 'rejected' | 'failed';

@Component({
  selector: 'app-candidat-info',
  templateUrl: './candidat-info.component.html',
  styleUrls: ['./candidat-info.component.scss'],
})
export class CandidatInfoComponent {
  @Output('validate') validateEvent = new EventEmitter<{
    suiviId: number;
    state: State;
    candidat: any;
  }>();
  @Input('data') data: any = null;
  previewUrl = '';
  categorie_permis: CategoryPermis | null = null;
  selectedRestrictionIds: number[] = [];
  constructor() {}

  //Candidat
  candidat: any = null;

  //Dossier session
  dossier_session: any = null;

  @Input() page = 'pending' as 'validate' | 'rejected' | 'pending';

  dossier: any;
  //
  chapitres: any[] = [];
  onValidate() {
    this.validateEvent.emit({
      suiviId: this.data.id,
      state: 'validate',
      candidat: this.candidat,
    });
  }

  onRejected() {
    this.validateEvent.emit({
      suiviId: this.data.id,
      state: 'rejected',
      candidat: this.candidat,
    });
  }
  ngOnInit(): void {
    console.log(this.data);
    this.candidat = this.data.candidat_info;

    // this.selectedRestrictionIds = JSON.parse(this.data.restriction_medical).map(
    //   (id: any) => parseInt(id, 10)
    // );
    // console.log(this.selectedRestrictionIds);
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
    return environment.recrutement.asset + path;
  }

  getSelectedRestrictionNames(): string {
    const selectedRestrictionNames = this.data.restrictionss.map((r: any) => {
      return r.name;
    });
    return selectedRestrictionNames.join(', ');
  }
}
