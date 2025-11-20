import { Component, EventEmitter, Input, Output } from '@angular/core';
import { Dossier } from 'src/app/core/interfaces/candidat';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import {
  RecrutementCandidat,
  RecrutementDemande,
  RecrutementEntreprise,
} from 'src/app/core/interfaces/recreutement';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutemmentExaminateurService } from 'src/app/core/services/recrutemment-examinateur.service';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-entreprise-fiche',
  templateUrl: './entreprise-fiche.component.html',
  styleUrls: ['./entreprise-fiche.component.scss'],
})
export class EntrepriseFicheComponent {
  @Output('validate') validateEvent = new EventEmitter<{
    entrepriseId: number;
    state: any;
    entreprise: any;
  }>();

  @Output('_edit') editEvent = new EventEmitter<RecrutementEntreprise>();
  previewUrl = '';

  @Input() entreprise: RecrutementEntreprise | null = null;

  onValidate() {
    if (this.entreprise) {
      this.validateEvent.emit({
        entrepriseId: this.entreprise.id,
        state: 'validate',
        entreprise: this.entreprise,
      });
    }
  }

  onRejected() {
    // this.validateEvent.emit({
    //   entrepriseId: this.data.id,
    //   state: 'rejected',
    //   candidat: this.entreprise,
    // });
  }
  ngOnInit(): void {}

  assets(path?: string) {
    return environment.candidat.asset + path;
  }

  edit() {
    if (this.entreprise) {
      this.editEvent.emit(this.entreprise);
    }
  }
}
