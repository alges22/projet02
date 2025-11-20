import { Component } from '@angular/core';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-inscription-absence',
  templateUrl: './inscription-absence.component.html',
  styleUrls: ['./inscription-absence.component.scss'],
})
export class InscriptionAbsenceComponent {
  currentPage: 'infos' | 'recapitulatif' | 'completed' = 'infos';
  user: any;
  imageSrc: string = '';
  isloadingSave = false;
  examens: any[] = [];
  examenId = '';
  pieceJustificative: File | null = null;
  ficheMedicale: File | null = null;
  private _pieceJustificativeUrl: string | null = null;
  private _ficheMedicaleUrl: string | null = null;

  constructor(
    private readonly authService: AuthService,
    private readonly candidatService: CandidatService,
    private readonly errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this._getUserData();
    this._getExamens();
  }

  private _getUserData() {
    const userHome: any = this.authService.storageService().get('auth');
    if (userHome) {
      this.errorHandler.startLoader();
      this.authService
        .checknpi({ npi: userHome.npi })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.user = response.data;
          this.errorHandler.stopLoader();
        });
    }
  }

  private _getExamens() {
    this.errorHandler.startLoader();
    this.candidatService
      .getSessions()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.examens = response.data;
        this.errorHandler.stopLoader();
      });
  }

  get examenSelected() {
    return this.examens.find((exam) => exam.id.toString() === this.examenId);
  }

  onPieceJustificativeChange(file: File | undefined) {
    this.pieceJustificative = file ?? null;
    this._pieceJustificativeUrl = null;
  }

  onFicheMedicaleChange(file: File | undefined) {
    this.ficheMedicale = file ?? null;
    this._ficheMedicaleUrl = null;
  }

  get pieceJustificativeUrl() {
    if (this._pieceJustificativeUrl) return this._pieceJustificativeUrl;
    if (!this.pieceJustificative) return null;
    this._pieceJustificativeUrl = URL.createObjectURL(this.pieceJustificative);
    return this._pieceJustificativeUrl;
  }

  get ficheMedicaleUrl() {
    if (this._ficheMedicaleUrl) return this._ficheMedicaleUrl;
    if (!this.ficheMedicale) return null;
    this._ficheMedicaleUrl = URL.createObjectURL(this.ficheMedicale);
    return this._ficheMedicaleUrl;
  }

  formIsValid() {
    return this.examenId && this.pieceJustificative && this.ficheMedicale;
  }

  gotoPage(page: 'infos' | 'recapitulatif' | 'completed') {
    if (page === 'recapitulatif' && !this.formIsValid()) {
      this.errorHandler.emitAlert(
        'Veuillez remplir tous les champs requis',
        'danger',
        'middle',
        true
      );
      return;
    }
    this.currentPage = page;
  }

  save() {
    if (!this.formIsValid()) return;

    const formData = new FormData();
    formData.append('examen_id', this.examenId);
    formData.append('piece_justificatve', this.pieceJustificative as Blob);
    formData.append('fiche_medical', this.ficheMedicale as Blob);

    this.isloadingSave = true;
    this.candidatService
      .justifierAbsence(formData)
      .pipe(
        this.errorHandler.handleServerErrors(() => {
          this.isloadingSave = false;
        })
      )
      .subscribe((response) => {
        if (response.status) {
          this.currentPage = 'completed';
          this.isloadingSave = false;
        }
      });
  }

  openImageModal(imageSrc: string) {
    if (imageSrc) {
      this.imageSrc = imageSrc;
      $('#openImageModal').modal('show');
    }
  }
}
