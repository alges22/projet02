import { Component } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { ChapitreService } from 'src/app/core/services/chapitre.service';
import { ConfigService } from 'src/app/core/services/config.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';
type Tab = 'question-count' | null;
@Component({
  selector: 'app-param-config',
  templateUrl: './param-config.component.html',
  styleUrls: ['./param-config.component.scss'],
})
export class ParamConfigComponent {
  chapitres: any[] = [];
  config = null as any;
  questionConfig: any[] = [];
  tab = null as Tab;
  constructor(
    private chapitreService: ChapitreService,
    private errorHandler: HttpErrorHandlerService,
    private configService: ConfigService
  ) {}

  ngOnInit(): void {
    this.get();
  }
  getChapitres() {
    this.errorHandler.startLoader();
    this.chapitreService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.chapitres = response.data;
        this.questionConfig = [];
        for (const chap of this.chapitres) {
          const questionToCompose = this.config['questionToCompose'] as any[];
          const found = questionToCompose.find((q) => {
            return q.chapitre_id == chap.id;
          });

          if (found) {
            this.questionConfig.push({
              chapitre: chap,
              count: found.counts,
            });
          } else {
            this.questionConfig.push({
              chapitre: chap,
              count: 0,
            });
          }
        }

        this.errorHandler.stopLoader();
      });
  }
  onQuestionConfig(tg: any, id: number) {
    const number = Number(tg.value);
    if (!isNaN(number) && number >= 0) {
      this.questionConfig.forEach((cp) => {
        if (cp.chapitre.id == id) {
          cp.count = Number(tg.value);
        }
      });
    } else {
      tg.value = '0';
    }
  }

  saveQuestionConfig() {
    const data: Record<number, number> = {};
    for (const cp of this.questionConfig) {
      data[cp.chapitre.id] = cp.count;
    }

    this.errorHandler.startLoader();
    this.configService
      .postQuestionCount({ chapitres: data })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        this.errorHandler.stopLoader();
      });
  }

  get() {
    this.errorHandler.startLoader();
    this.configService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.getChapitres();
        this.config = response.data;
        this.errorHandler.stopLoader();
      });
  }

  openTab(tab: Tab) {
    if (this.tab == tab) {
      this.tab = null;
    } else {
      this.tab = tab;
    }
  }
}
