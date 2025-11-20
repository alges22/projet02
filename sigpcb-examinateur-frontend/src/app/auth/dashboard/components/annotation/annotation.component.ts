import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CandidatData } from 'src/app/core/interfaces/user.interface';
import { ConduiteInspectionService } from 'src/app/core/services/conduite-inspection.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
type Epreuve = {
  name: string;
  id: number;
  sub_baremes: { name: string; eliminatoire: boolean; id: number }[];
  noted?: boolean;
  poids: number;
};

@Component({
  selector: 'app-annotation',
  templateUrl: './annotation.component.html',
  styleUrls: ['./annotation.component.scss'],
})
export class AnnotationComponent {
  constructor(
    private conduiteInspection: ConduiteInspectionService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  @Input('data') data!: CandidatData;
  onLoading = false;
  epreuves: Epreuve[] = [];
  juriescandidat: any[] = [];
  modalEpreuve = 'openEpreuveModal';
  epreuve: Epreuve | null = null;
  epreuveNumber: any;
  touched = false;
  onPosting = false;
  subBaremeSelected: { name: string; eliminatoire: boolean; id: number }[] = [];

  @Output() onfinished = new EventEmitter();

  openAnnotation(epreuve: any, epreuveNumber: number) {
    this.epreuve = epreuve;
    this.epreuveNumber = epreuveNumber;
    this.subBaremeSelected = [];
    this.onPosting = false;
    this.touched = false;
  }

  ngOnInit(): void {
    this._initializeComponent();
  }

  private _initializeComponent() {
    this._getJuriesCandidat(() => {
      this._getEpreuvesByCategory(() => {}, this.data.categorie_permis_id);
    }, this.data.id);
  }

  @Output() validationEvent = new EventEmitter<void>();
  validate(emit = false) {
    this.onLoading = true;
    const data = {
      sub_bareme_id: this.subBaremeSelected.map((b) => b.id),
      jury_candidat_id: this.data.id,
      bareme_conduite_id: this.epreuve?.id,
    };

    this.conduiteInspection
      .postAnnotation(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.onLoading = false;
        this._initializeComponent();
        if (emit) {
          this.validationEvent.emit();
        }
      });
  }

  private _getEpreuvesByCategory(call: CallableFunction, categoryId: any) {
    this.conduiteInspection
      .getEpreuvesByCategory(categoryId)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const baremes: any[] = response.data.baremes;
        if (baremes.length) {
          const mappedBaremes = baremes.map((bareme: any) => {
            // Cherchez une correspondance dans juriescandidat en fonction de l'ID du bareme
            const match = this.juriescandidat.find(
              (jury: any) => jury.bareme_conduite_id === bareme.id
            );
            // Si une correspondance est trouvée, ajoutez le tableau de mentions à l'élément
            if (match) {
              bareme.noted = true;
            }

            return bareme; // Retournez l'élément modifié ou non
          });
          this.epreuves = mappedBaremes;
          call();
          if (this.finished) {
            this.onfinished.emit();
          }
        } else {
          this.errorHandler.emitAlert(
            `Aucune épreuve trouvée pour cette catégorie de permis`,
            'danger',
            'middle'
          );
        }
        this.errorHandler.stopLoader();
      });
  }

  private _getJuriesCandidat(call: CallableFunction, juryCandidatId: any) {
    this.errorHandler.startLoader();
    this.conduiteInspection
      .getJuriesCandidat(juryCandidatId)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const data = response.data;
        if (Array.isArray(data)) {
          this.juriescandidat = data;
        } else {
          this.juriescandidat = data?.reponses ?? [];
        }
        call();
      });
  }

  onSelectSubBareme(sub: { name: string; eliminatoire: boolean; id: number }) {
    this.touched = true;
    if (this.subBaremeSelected.includes(sub)) {
      this.subBaremeSelected = this.subBaremeSelected.filter(
        (el) => el !== sub
      );
    } else {
      this.subBaremeSelected.push(sub);
    }

    if (sub.eliminatoire) {
      this.subBaremeSelected = [];
      this.subBaremeSelected.push(sub);
    } else {
      const eliminatoire = this.subBaremeSelected.find((b) => b.eliminatoire);
      if (eliminatoire) {
        this.subBaremeSelected = this.subBaremeSelected.filter(
          (el) => el == sub
        );
      }
    }
  }

  cancel() {}

  get noteParBareme() {
    if (this.epreuve) {
      const poids = Number(this.epreuve.poids);
      const subs = (this.epreuve.sub_baremes ?? []).filter(
        (b) => !b.eliminatoire
      );
      if (!isNaN(poids)) {
        if (subs.length > 0) {
          const responses = this.subBaremeSelected.filter(
            (s) => !s.eliminatoire
          );
          const result = (poids / subs.length) * responses.length;
          return Number(result.toFixed(2));
        }
      }
    }

    return -1;
  }
  get finished() {
    return this.epreuves.every((e) => e.noted);
  }
}
