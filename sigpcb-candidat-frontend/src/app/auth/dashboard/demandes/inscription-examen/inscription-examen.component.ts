import { Component, Input, OnInit } from '@angular/core';
import { PrestationService } from 'src/app/core/prestation/prestation.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { redirectTo } from 'src/app/helpers/helpers';
type UserType = 'civil' | 'militaire';
type Page = 'user-type' | 'todo-action' | 'prepare-my-self';
type Todo =
  | 'inscription'
  | 'reinscription'
  | 'inscription-conduite'
  | 'composition-conduite'
  | 'composition-code'
  | 'inscription-absence';

@Component({
  selector: 'app-inscription-examen',
  templateUrl: './inscription-examen.component.html',
  styleUrls: ['./inscription-examen.component.scss'],
})
export class InscriptionExamenComponent implements OnInit {
  user: any;
  isTodoSelected: boolean = false;
  constructor(
    private readonly storage: StorageService,
    private readonly authService: AuthService,
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly candidatService: CandidatService,
    private readonly prestationService: PrestationService
  ) {}
  ngOnInit(): void {
    this.user = this.authService.storageService().get('auth');
    if (this.user) this._getDossierSession();
    this.prestationService.modalOpened$.subscribe(() => {
      this.page = 'todo-action';
    });
  }
  userType: UserType = 'civil';
  showNavigation = true;
  @Input() page: Page = 'todo-action';
  dossier_session: any;

  selectedTodo: Todo | null = null;

  whoAm(type: UserType) {
    this.userType = type;
  }

  private _getDossierSession() {
    this.errorHandler.startLoader();
    this.candidatService
      .getDossierSession()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          // Filtrer les langues dont le statut est true
          this.dossier_session = response.data;
          this.errorHandler.stopLoader();
        }
      });
  }

  gotoPage(page: Page) {
    if (this.selectedTodo === 'inscription-conduite') {
      redirectTo('/dashboard/inscription-conduite', 0);
    } else if (this.selectedTodo === 'inscription-absence') {
      redirectTo('/dashboard/inscription-absence', 0);
    } else {
      if (this.selectedTodo === 'inscription') {
        redirectTo('/dashboard/inscription-au-permis', 0);
      } else {
        this.page = page;
      }
    }
  }

  whatToDo(todo: Todo, type_examen: any) {
    this.selectedTodo = todo;
    this.storage.store('todo', todo);
    this.storage.store('type_examen', type_examen);
    if (this.selectedTodo === 'composition-conduite' || 'composition-code') {
      this.showNavigation = false;
    }
    this.isTodoSelected = true;
  }

  handle(event: Event) {
    event.preventDefault();
    switch (this.selectedTodo) {
      case 'inscription':
        redirectTo('/dashboard/inscription-au-permis');
        break;
      case 'reinscription':
        redirectTo('/dashboard/inscription-au-permis');
        break;
      case 'inscription-absence':
        redirectTo('/dashboard/inscription-absence');
        break;
      default:
    }
  }

  redirectTo(path: string) {
    redirectTo(path);
  }
}
