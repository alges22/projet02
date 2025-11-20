import { BrowserEventServiceService } from './browser-event-service.service';
import { Injectable, ElementRef } from '@angular/core';
import { of } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { ServerResponseCallback } from 'src/app/types/server';
import { AlertPosition, AlertType, IAlert } from '../interfaces/alert';

@Injectable({
  providedIn: 'root',
})
export class HttpErrorHandlerService {
  constructor(private browservice: BrowserEventServiceService) {}

  handleServerErrors(
    callbackAction?: ServerResponseCallback,
    formId?: string,
    emitAlert = true
  ) {
    return this.handleServerError(formId ?? '', callbackAction, emitAlert);
  }
  /**
   * Traite les erreurs de serveurs
   * @returns
   */
  handleServerError(
    formId: string,
    callbackAction?: ServerResponseCallback,
    emitAlert = true
  ) {
    return catchError((responseError) => {
      if (formId && formId !== '') {
        this.browservice.emitErrorsEvent(formId, responseError.error);
      }
      if (callbackAction) {
        callbackAction(responseError.error, formId);
      }
      const error = responseError.error;
      if (!error.status) {
        if (emitAlert) {
          let message = '';
          let entete = error.message;
          //Au cas ou des erreurs seront présentes dans l'objet message
          if (typeof error.errors === 'object' && error.errors !== undefined) {
            for (const k in error.errors) {
              let sm = '';
              if (Object.prototype.hasOwnProperty.call(error.errors, k)) {
                const err = error.errors[k];
                if (Array.isArray(err)) {
                  sm = err
                    .map((mes) => {
                      return `<li>${mes}</li>`;
                    })
                    .join(' ');
                } else if (typeof err === 'string') {
                  sm = sm.concat(`<li>${err}</li>`);
                }
              }
              message = message.concat(sm);
            }
            const messageFormat = `${entete}<ul class="text-danger mx-3 text-start mt-3">${message}</ul>`;

            message = messageFormat;
          } else {
            message = entete;
          }
          this.emitAlert(message, 'danger', 'middle', true);
          this.browservice.hideLoader();
        }
        throw new Error('Server error');
      }
      return of(responseError.error);
    });
  }

  /**
   * Déclenche un éveènement qui va effacer les messages
   * @param formId
   */
  clearServerErrorsMessages(formId: string) {
    this.browservice.emitClearMessagesEvent(formId);
  }

  emitAlert(
    message = '',
    type: AlertType = 'warning',
    postion: AlertPosition = 'bottom-right',
    fixed = false
  ) {
    this.browservice.emitAlertEvent({
      message: message,
      type: type,
      position: postion,
      fixed: fixed,
    });
  }

  emitSuccessAlert(
    message: string,
    type: AlertType = 'success',
    postion: AlertPosition = 'bottom-right',
    fixed = false
  ) {
    this.browservice.emitAlertEvent({
      message: message,
      type: type,
      position: postion,
      fixed: fixed,
    });
  }

  emitDangerAlert(
    message: string,
    type: AlertType = 'danger',
    postion: AlertPosition = 'middle',
    fixed = true
  ) {
    this.browservice.emitAlertEvent({
      message: message,
      type: type,
      position: postion,
      fixed: fixed,
    });
  }

  startLoader(message?: string) {
    this.browservice.showLoader(message);
  }

  stopLoader() {
    this.browservice.hideLoader();
  }
}
