import { Component, Input } from '@angular/core';
import { AnnexeAnattService } from '../../services/annexe-anatt.service';
import { ExamenService } from '../../services/examen.service';
import { AnnexeAnatt } from '../../interfaces/annexe-anatt';
import { Agenda } from '../../interfaces/examens';
import { StorageService } from '../../services/storage.service';

@Component({
  selector: 'app-annexe-session',
  templateUrl: './annexe-session.component.html',
  styleUrls: ['./annexe-session.component.scss'],
})
export class AnnexeSessionComponent {
  @Input() annexes: AnnexeAnatt[] = [];
  @Input() agendas: Agenda[] = [];
  aselected = 0;
  eselected = 0;
  constructor(
    private annexeAnattService: AnnexeAnattService,
    private examenService: ExamenService,
    private storage: StorageService
  ) {}
  /**
   * Sélectionne une annexe
   */
  annexeSelected(event: any): void {
    const annexe =
      this.annexes.find((an) => an.id == event.target.value) || null;
    this.annexeAnattService.selectAnnexe(annexe);
    this.storage.store('current-annexe', annexe);
  }

  sessionSelected(event: any): void {
    const session = this.agendas.find(
      (agenda) => agenda.id == event.target.value
    );
    this.examenService.setupCurrentSession(session);
    this.storage.store('current-session', session);
  }
}
