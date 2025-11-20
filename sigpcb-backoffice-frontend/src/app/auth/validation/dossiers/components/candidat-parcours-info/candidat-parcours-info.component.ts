import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { Dossier } from 'src/app/core/interfaces/candidat';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { environment } from 'src/environments/environment';
type State = 'validate' | 'rejected' | 'failed';
@Component({
  selector: 'app-candidat-parcours-info',
  templateUrl: './candidat-parcours-info.component.html',
  styleUrls: ['./candidat-parcours-info.component.scss'],
})
export class CandidatParcoursInfoComponent implements OnInit {
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

  dossier: Dossier | null = null;
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
    this.candidat = this.data.candidat;

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
    return environment.candidat.asset + path;
  }

  getSelectedRestrictionNames(): string {
    const selectedRestrictionNames = this.data.restrictionss.map((r: any) => {
      return r.name;
    });
    return selectedRestrictionNames.join(', ');
  }
}
