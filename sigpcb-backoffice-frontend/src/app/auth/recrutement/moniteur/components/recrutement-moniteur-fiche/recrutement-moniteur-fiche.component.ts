import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Moniteur } from 'src/app/core/interfaces/recreutement';
import { environment } from 'src/environments/environment';
type State = 'validate' | 'rejected' | 'failed';

@Component({
  selector: 'app-recrutement-moniteur-fiche',
  templateUrl: './recrutement-moniteur-fiche.component.html',
  styleUrls: ['./recrutement-moniteur-fiche.component.scss'],
})
export class RecrutementMoniteurFicheComponent {
  candidat: any;
  demandeexaminateur: any;
  @Output('validate') validateEvent = new EventEmitter<{
    moniteurId: number;
    demandeur: any;
    state: State;
  }>();
  @Input('data') data: Moniteur | null = null;
  previewUrl = '';
  categorie_permis: CategoryPermis | null = null;
  selectedRestrictionIds: number[] = [];
  @Input('categories') categories: any;
  @Input('uadmins') uadmins: any;
  @Input('titres') titres: any;
  constructor() {}

  @Input() page = 'pending' as 'validate' | 'rejected' | 'pending';

  onValidate() {
    if (this.data) {
      this.validateEvent.emit({
        moniteurId: this.data.id,
        demandeur: this.candidat,
        state: 'validate',
      });
    }
  }

  onRejected() {
    if (this.data) {
      this.validateEvent.emit({
        moniteurId: this.data.id,
        demandeur: this.candidat,
        state: 'rejected',
      });
    }
  }
  ngOnInit(): void {
    this.demandeexaminateur = this.data;
    this.candidat = this.data?.demandeur_info;
  }

  openSuiviModal(url?: string) {
    if (url) {
      this.previewUrl = url;
    }
    $(`#suivi-${this.data?.id}`).modal('show');
  }

  assets(path?: string) {
    return environment.examinateur.asset + path;
  }

  getNameCategories(ids: string): string {
    const idsArray = JSON.parse(ids)
      .split(',')
      .map((id: any) => parseInt(id, 10));
    const noms = idsArray.map((id: any) => {
      const categorie = this.categories.find((c: any) => c.id === id);
      return categorie ? categorie.name : null;
    });
    return noms.filter(Boolean).join(', ');
  }
}
