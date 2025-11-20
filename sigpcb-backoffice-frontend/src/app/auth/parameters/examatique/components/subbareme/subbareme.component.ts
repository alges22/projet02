import { Component, Input, OnDestroy, OnInit } from '@angular/core';
import * as bootstrap from 'bootstrap';
import { BaremeConduiteService } from 'src/app/core/services/bareme-conduite.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-subbareme',
  templateUrl: './subbareme.component.html',
  styleUrls: ['./subbareme.component.scss'],
})
export class SubbaremeComponent implements OnInit, OnDestroy {
  @Input() baremeId!: number;
  subbaremes: { name: string; id: number; eliminatoire: boolean }[] = [];
  form = {} as {
    name: string;
    bareme_conduite_id: number;
    id: number;
    eliminatoire: boolean;
  };
  onLoad = true;
  constructor(
    private baremeconduiteService: BaremeConduiteService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.get();
  }

  get(onLoad = true) {
    if (this.baremeId) {
      this.onLoad = onLoad;
      this.baremeconduiteService
        .getSubaremes(this.baremeId)
        .pipe(
          this.errorHandler.handleServerErrors(() => {
            this.onLoad = false;
          })
        )
        .subscribe((response) => {
          this.subbaremes = response.data;
          this.onLoad = false;
        });
    }
  }
  ngOnDestroy(): void {
    this.subbaremes = [];
  }

  onEliminatoireChange(target: any) {
    if (target) {
      this.form.eliminatoire = target.checked;
    }
  }
  openModal(subbareme?: any) {
    if (subbareme) {
      this.form = { ...subbareme };
      console.log(this.form, subbareme);
    } else {
      this.form = {
        name: '',
        bareme_conduite_id: this.baremeId,
        id: 0,
        eliminatoire: false,
      }; // Réinitialiser le formulaire pour la création
    }

    $(`#subbareme-modal-${this.baremeId}`).modal('show');
  }
  post() {
    this.baremeconduiteService
      .postSubbareme(this.form)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get(false);
        this.closeModal();
      });
  }

  update() {
    this.baremeconduiteService
      .updateSubbareme(this.form)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get(false);
        this.closeModal();
      });
  }

  delete(id: number) {
    this.baremeconduiteService
      .deleteSubbareme(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get(false);
      });
  }
  closeModal() {
    $(`#subbareme-modal-${this.baremeId}`).modal('hide');
  }
}
