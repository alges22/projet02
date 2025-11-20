import {
  Component,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import { ConduiteInspectionService } from 'src/app/core/services/conduite-inspection.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-note',
  templateUrl: './note.component.html',
  styleUrls: ['./note.component.scss'],
})
export class NoteComponent implements OnInit {
  @Input() session: any;
  @Input() jury: any;
  onLoadVagues = true;
  onLoading = false;
  candidats = [] as any[];
  constructor(
    private conduiteInspection: ConduiteInspectionService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    console.log(this.session, this.jury);
    var data: any = {};
    data.jury_id = this.jury;
    data.examen_id = this.session;
    this._getCandidats(data);
  }

  private _getCandidats(data: any) {
    this.errorHandler.startLoader();
    this.conduiteInspection
      .getCandidatsNotes(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoading = false;
          this.onLoadVagues = false;
        })
      )
      .subscribe((response) => {
        const data = response.data;
        this.candidats = data;
        this.errorHandler.stopLoader();
      });
  }
}
