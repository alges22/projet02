import { Component, EventEmitter, Input, Output } from '@angular/core';
import { environment } from 'src/environments/environment';
type State = 'validate' | 'rejected' | 'failed';
@Component({
  selector: 'app-licence-fiche',
  templateUrl: './licence-fiche.component.html',
  styleUrls: ['./licence-fiche.component.scss'],
})
export class LicenceFicheComponent {
  @Output('validate') validateEvent = new EventEmitter<{
    nouvellelicenceId: number;
    state: State;
    nouvellelicence: any;
  }>();
  @Input('data') data: any = null;
  previewUrl = '';
  constructor() {}

  //nouvellelicence
  nouvellelicence: any = null;

  @Input() page = 'pending' as 'validate' | 'rejected' | 'pending';
  vehicules: any[] = [];
  onValidate() {
    this.validateEvent.emit({
      nouvellelicenceId: this.data.id,
      state: 'validate',
      nouvellelicence: this.nouvellelicence,
    });
  }

  onRejected() {
    this.validateEvent.emit({
      nouvellelicenceId: this.data.id,
      state: 'rejected',
      nouvellelicence: this.nouvellelicence,
    });
  }
  ngOnInit(): void {
    this.nouvellelicence = this.data;
    this.vehicules = this.data.vehicules;
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
