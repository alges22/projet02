import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { Dossier } from 'src/app/core/interfaces/candidat';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
type State = 'validate' | 'rejected' | 'failed';
@Component({
  selector: 'app-justif-infos',
  templateUrl: './justif-infos.component.html',
  styleUrls: ['./justif-infos.component.scss'],
})
export class JustifInfosComponent {
  @Output('validate') validateEvent = new EventEmitter<{
    justifId: number;
    state: State;
    candidat: any;
  }>();
  @Input('data') data: any = null;
  constructor() {}

  //Candidat
  candidat: any = null;

  @Input() page = 'init' as 'validate' | 'rejected' | 'init';

  dossier: Dossier | null = null;
  //
  onValidate() {
    this.validateEvent.emit({
      justifId: this.data.id,
      state: 'validate',
      candidat: this.candidat,
    });
  }

  onRejected() {
    this.validateEvent.emit({
      justifId: this.data.id,
      state: 'rejected',
      candidat: this.candidat,
    });
  }
  ngOnInit(): void {
    this.candidat = this.data.candidat;
  }

  open() {
    $(`#suivi-${this.data.id}`).modal('show');
  }
}
