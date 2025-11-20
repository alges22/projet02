import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { ProrogationPermis } from 'src/app/core/interfaces/services';
import { environment } from 'src/environments/environment';
type State = 'validate' | 'rejected' | 'failed';

@Component({
  selector: 'app-prorogation-fiche',
  templateUrl: './prorogation-fiche.component.html',
  styleUrls: ['./prorogation-fiche.component.scss'],
})
export class ProrogationFicheComponent {
  candidat: any;
  @Output('validate') validateEvent = new EventEmitter<{
    data: ProrogationPermis;
    state: State;
  }>();
  @Input('data') data: ProrogationPermis | null = null;
  previewUrl = '';
  categorie_permis: CategoryPermis | null = null;
  selectedRestrictionIds: number[] = [];
  @Input('categories') categories: any;
  constructor() {}

  @Input() page = 'pending' as 'validate' | 'rejected' | 'pending';

  onValidate() {
    if (this.data) {
      this.validateEvent.emit({
        data: this.data,
        state: 'validate',
      });
    }
  }

  onRejected() {
    if (this.data) {
      this.validateEvent.emit({
        data: this.data,
        state: 'rejected',
      });
    }
  }
  ngOnInit(): void {
    this.candidat = this.data?.demandeur_info;
  }

  openSuiviModal(url?: string) {
    if (url) {
      this.previewUrl = url;
    }
    $(`#suivi-${this.data?.id}`).modal('show');
  }

  assets(path?: string) {
    return environment.candidat.asset + path;
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
