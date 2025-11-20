import { Component, EventEmitter, Input, Output } from '@angular/core';
import { isArray } from 'lodash';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { EchangePermis } from 'src/app/core/interfaces/services';
import { environment } from 'src/environments/environment';
type State = 'validate' | 'rejected' | 'failed';
@Component({
  selector: 'app-echange-permis-fiche',
  templateUrl: './echange-permis-fiche.component.html',
  styleUrls: ['./echange-permis-fiche.component.scss'],
})
export class EchangePermisFicheComponent {
  candidat: any;
  @Output('validate') validateEvent = new EventEmitter<{
    data: EchangePermis;
    state: State;
  }>();
  @Input('data') data: EchangePermis | null = null;
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
    // var categorie_permis_ids = JSON.parse(this.data?.categorie_permis_ids)
    //   .split(',')
    //   .map((id: any) => parseInt(id, 10));
    // // console.log(categorie_permis_ids);

    // const categoriesNoms = categorie_permis_ids.map((id: any) => {
    //   const categorie = this.categories.find((c: any) => c.id === id);
    //   return categorie ? categorie.name : null;
    // });

    // console.log(categoriesNoms);
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
