import { environment } from './../../environments/environment';

import { ActivatedRoute, ParamMap } from '@angular/router';
import { AlertPosition, AlertType } from '../core/interfaces/alert';
import { ApiEndpoint } from '../core/types/api-endpoint';
import { KeyValueParam } from './types';
import { UrlMaker } from './url-maker';

export const apiUrl = (path: string) => {
  return environment.endpoints.candidat.concat(path);
};

export function resp<T>(response: any): T | undefined {
  if (response.data) {
    return response.data;
  }
  return undefined;
}

export const parseUrl = (url: string, params?: KeyValueParam[]) => {
  if (params) {
    const urlMaker = new UrlMaker(url);
    for (let i = 0; i < params.length; i++) {
      urlMaker.addQuery(params[i].key, params[i].value);
    }
    url = urlMaker.generateUrl();
  }

  return url;
};

export const routeParam = (route: ActivatedRoute) => {
  return new Promise<ParamMap>((resolve, reason) => {
    return route.paramMap.subscribe((param) => {
      return resolve(param);
    });
  });
};

export function is_string(value: any): boolean {
  return typeof value === 'string';
}
export function firstElements<T>(array: T[], count: number): T[] {
  return array.slice(0, count);
}

export function is_array(value: any): boolean {
  return Array.isArray(value);
}

export function deleteCookie(name: string) {
  document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;`;
}

export const dispatchErrors = (
  element: HTMLElement,
  event: string,
  errors: any
) => {
  const nEvent = new CustomEvent(event, {
    detail: errors,
  });

  element.dispatchEvent(nEvent);
};
/**
 * Les évéments sont capturé dans AlertDirective
 * @param alertDetails
 */
export const emitAlertEvent = (
  message: string = '',
  type: AlertType = 'warning',
  position: AlertPosition = 'bottom-right',
  fixed?: boolean
) => {
  const alertElement = document.querySelector('#alert-global');
  const alertDetails = {
    message: message,
    type: type,
    position: position,
    fixed: fixed,
  };
  if (alertElement) {
    dispatchErrors(alertElement as HTMLElement, 'alert-occure', alertDetails);
  }
};
/**
 * Redirige l'utilsateur suivant une url, sinon vers l'url précédente dans un délai spécifier
 * @param url
 * @param delay
 */
export function redirectTo(url?: string, delay: number = 0): void {
  if (url) {
    setTimeout(() => {
      window.location.href = url;
    }, delay);
  } else {
    setTimeout(() => {
      window.history.back();
    }, delay);
  }
}
export function toFormData(object: any, append: any[] = []): FormData {
  if (typeof object !== 'object' || !object) {
    throw new Error('The data to FormData must be a valid object');
  }
  const formData = new FormData();

  // Ajouter les propriétés de l'objet à FormData
  Object.keys(object).forEach((key) => {
    formData.append(key, object[key]);
  });
  if (Array.isArray(append)) {
    append.forEach(({ name, value }) => {
      formData.append(name, value);
    });
  }
  return formData;
}

/**
 *  Cette fonction permettra de rafraichir par exemple la page courante
 * @param delay
 */
export function refresh(delay: number = 5000) {
  setTimeout(() => {
    location.reload();
  }, delay);
}

/***
 * Affiche les fichiers
 */
export const asset = (path: string, api: ApiEndpoint = 'candidat') => {
  return environment.candidat.asset.concat(path);
};

export function truncate(string: string, limit = 30, concat = '...'): string {
  if (string.length <= limit) {
    return string;
  }

  return string.slice(0, limit) + concat;
}

export function stringAfterLast(string: string, after: string): string {
  const index = string.lastIndexOf(after);

  if (index === -1) {
    return '';
  }

  return string.slice(index + after.length);
}

export const isFile = (value: any) => {
  return value instanceof File || value instanceof Blob;
};

export function uniqueID(length = 32) {
  length = length || 16; // Vous pouvez définir la longueur souhaitée ici
  let result = '';
  const characters =
    'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

  for (let i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * characters.length));
  }

  return result;
}

export function utcNow(): string {
  const now = new Date();
  const utcNow = new Date(
    now.getUTCFullYear(),
    now.getUTCMonth(),
    now.getUTCDate(),
    now.getUTCHours(),
    now.getUTCMinutes(),
    now.getUTCSeconds(),
    now.getUTCMilliseconds()
  );

  const formattedDate = utcNow.toISOString();
  return formattedDate;
}
