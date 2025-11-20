import { Component, Input } from '@angular/core';
import { Agenda } from '../../interfaces/examens';
import { AnnexeAnatt } from '../../interfaces/annexe-anatt';
import { DateService } from '../../services/date.service';
import { ResultatList } from '../../interfaces/resultats';

@Component({
  selector: 'resultat-list',
  templateUrl: './resultat-list.component.html',
  styleUrls: ['./resultat-list.component.scss'],
})
export class ResultatListComponent {
  @Input() resultats: ResultatList[] = [];
  @Input() meta = {
    agenda: {} as Agenda | null,
    annexe: {} as AnnexeAnatt | null,
  };
}
