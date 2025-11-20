import { IAlert } from 'src/app/core/interfaces/alert';
import { Injectable } from '@angular/core';
import { dispatchErrors } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class BrowserEventServiceService {
  constructor() {}

  /**
   * Les évenements sont générés dans NeedValidationDirective
   * @param elementId
   * @param errors
   */
  emitErrorsEvent(elementId: string, errors: any) {
    const element = document.getElementById(elementId);
    if (element) {
      dispatchErrors(element as HTMLElement, 'error-occure', errors);
    }
  }
  /**
   * Les évenements sont générés dans NeedValidationDirective
   * @param elementId
   */
  emitClearMessagesEvent(elementId: string) {
    const element = document.getElementById(elementId);
    if (element) {
      dispatchErrors(element as HTMLElement, 'clear-messages', {});
    }
  }
  /**
   * Les évéments sont capturé dans AlertDirective
   * @param alertDetails
   */
  emitAlertEvent(alertDetails: IAlert) {
    const alertElement = document.querySelector('#alert-global');
    if (alertElement) {
      dispatchErrors(alertElement as HTMLElement, 'alert-occure', alertDetails);
    }
  }
  /**
   * Les évéments sont capturé dans LoaderDirective
   */
  showLoader(message?: string) {
    const alertElement = document.querySelector('#global-loader');
    if (alertElement) {
      dispatchErrors(alertElement as HTMLElement, 'show-loader', {
        message: message ?? 'Chargement en cours',
      });
    }
  }

  /**
   * Les évéments sont capturé dans LoaderDirective
   */
  hideLoader() {
    const alertElement = document.querySelector('#global-loader');
    if (alertElement) {
      dispatchErrors(alertElement as HTMLElement, 'hide-loader', {});
    }
  }
}
