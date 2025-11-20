import { Component } from '@angular/core';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Agenda } from 'src/app/core/interfaces/examens';
import {
  Resultat,
  ResultatList,
  StatCode,
  StatConduite,
} from 'src/app/core/interfaces/resultats';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ResultatService } from 'src/app/core/services/resultat.service';

@Component({
  selector: 'app-conduites',
  templateUrl: './conduites.component.html',
  styleUrls: ['./conduites.component.scss'],
})
export class ConduitesComponent {
  annexe: AnnexeAnatt | null = null;
  examen: Agenda | null = null;
  listeLoaded = true;
  /**
   * Les paramètres de filtrage
   */
  filter = {
    annexe_id: 0 as null | number,
    examen_id: 0,
    presence_code: null,
    resultat_code: 'success',
  };
  statCode = {} as StatCode;
  onLoadCurrentSession = true;
  currentSession: any = null;
  resultats: any[] = [];
  filtered: ResultatList[] = [];
  categories: CategoryPermis[] = []; //

  constructor(
    private examenService: ExamenService,
    private errorHandler: HttpErrorHandlerService,
    private annexeService: AnnexeAnattService,
    private categoryPermisService: CategoryPermisService,
    private resultatService: ResultatService
  ) {}

  ngOnInit(): void {
    this._annexeChanged();
    this._sessionChanged();
    this._getCategories();
  }

  fetch(filter: string) {
    this.filtered = [];
    const resultats = this.filterResults(this.resultats, filter);
    for (const resultat of resultats) {
      const found = this.filtered.find(
        (r) => r.permis.id == resultat.categorie_permis_id
      );
      if (resultat.resultat_conduite == 'success') {
        resultat.status = 'Admis(e)';
      }
      if (resultat.resultat_conduite == 'failed') {
        if (resultat.presence_conduite == 'absent') {
          resultat.status = 'Absent(e)';
        } else {
          resultat.status = 'Récalé(e)';
        }
      }

      if (!found) {
        this.filtered.push({
          permis: this.findCategory(resultat.categorie_permis_id),
          list: [resultat],
        });
      } else {
        found.list.push(resultat);
      }
    }
  }

  codes() {
    this.errorHandler.startLoader('Chargement du résultat de code');
    // this.codeReady = false;
    this.resultatService
      .codes([this.filter])
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.listeLoaded = true;
        })
      )
      .subscribe((response) => {
        this.listeLoaded = true;
        this.resultats = response.data;
        this.filtered = [];
        this.statCode = {
          presentes: this.resultats.length,
          admis: 0,
          recales: 0,
          abscents: 0,
        };

        for (const resultat of this.resultats) {
          const found = this.filtered.find(
            (r) => r.permis.id == resultat.categorie_permis_id
          );
          if (resultat.resultat_conduite == 'success') {
            this.statCode.admis++;
            resultat.status = 'Admis(e)';
          }
          if (resultat.resultat_conduite == 'failed') {
            this.statCode.recales++;
            if (resultat.presence_conduite == 'absent') {
              this.statCode.abscents++;
              resultat.status = 'Absent(e)';
            } else {
              resultat.status = 'Récalé(e)';
            }
          }

          if (!found) {
            this.filtered.push({
              permis: this.findCategory(resultat.categorie_permis_id),
              list: [resultat],
            });
          } else {
            found.list.push(resultat);
          }
        }

        this.errorHandler.stopLoader();
      });
  }
  _getCategories() {
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
      });
  }
  private _annexeChanged() {
    this.annexeService.onAnnexeChange().subscribe((annexe) => {
      if (annexe !== null) {
        this.annexe = annexe;
        this.filter.annexe_id = annexe.id;
        this.codes();
      }
    });
  }

  private findCategory(id: any) {
    return this.categories.find((c) => c.id == id) as CategoryPermis;
  }

  private _sessionChanged() {
    this.examenService.currentSession().subscribe((session) => {
      this.onLoadCurrentSession = false;

      //Si une session est en cours
      if (!!session) {
        this.currentSession = session;
        this.filter.examen_id = session.id;
      }
      this.codes();
    });
  }

  private filterResults(resultats: any[], type = 'admis') {
    if (type == 'admis') {
      return resultats.filter((resultat) => {
        return resultat.resultat_conduite == 'success';
      });
    } else if (type == 'recales') {
      return resultats.filter((resultat) => {
        return resultat.resultat_conduite == 'failed';
      });
    } else if (type == 'absent') {
      return resultats.filter((resultat) => {
        return (
          resultat.presence_conduite == 'absent' &&
          resultat.resultat_conduite == 'failed'
        );
      });
    } else {
      return resultats;
    }
  }
}
