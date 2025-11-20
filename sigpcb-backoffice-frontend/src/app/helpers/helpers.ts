import { environment } from './../../environments/environment';

import { ActivatedRoute, ParamMap } from '@angular/router';
import { KeyValueParam } from './types';
import { UrlMaker } from './url-maker';
import { ApiEndpoint } from '../core/types/api-endpoint';
import { AlertPosition, AlertType, IAlert } from '../core/interfaces/alert';

export const apiUrl = (path: string, from: ApiEndpoint = 'admin') => {
  if (from == 'entreprise') {
    return environment.endpoints.entreprise.concat(path);
  }

  return environment.endpoints.admin.concat(path);
};
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
export function redirectTo(url?: string, delay: number = 3000): void {
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
export const asset = (path: string, api: string = 'admin') => {
  if (api == 'admin') {
    return environment.admin.asset.concat(path);
  } else if (api == 'base') {
    return environment.base.asset.concat(path);
  }
  return '';
};

export function truncate(string: string, limit = 50, end = '...'): string {
  if (string.length <= limit) {
    return string;
  }

  const truncatedString = string.slice(0, limit);
  return truncatedString + end;
}

export function numberPad(
  number: number | string | number,
  length: number = 3,
  padChar: string = '0'
): string {
  let str = String(number); // Convertir en chaîne de caractères

  if (str.length >= length) {
    return str; // Aucun besoin de remplissage si la longueur est déjà suffisante
  }

  const padding = Array(length - str.length + 1).join(padChar); // Chaîne de remplissage

  return padding + str; // Remplissage à gauche avec des zéros
}

export function urlencode(
  url: string,
  filters: Record<string, number | string | null>[] = []
) {
  const queryParams = [];

  // Ajouter les filtres à la liste des paramètres de requête
  for (const filter of filters) {
    for (const key in filter) {
      const value = filter[key];
      if (value !== null) {
        queryParams.push(
          `${encodeURIComponent(key)}=${encodeURIComponent(value)}`
        );
      }
    }
  }

  // Combiner les paramètres de requête en une chaîne
  const queryString = queryParams.join('&');

  // Ajouter la chaîne de requête à l'URL
  if (queryString.length > 0) {
    return `${url}?${queryString}`;
  } else {
    return url;
  }
}

export function isDateValid(dateString: string): boolean {
  try {
    const date = new Date(dateString);
    return !isNaN(date.getTime());
  } catch (error) {
    return false; // An error occurred, date is invalid
  }
}

export function ucfirst(inputString: string): string {
  if (inputString.length === 0) {
    return inputString; // Return unchanged if the string is empty
  }

  const firstChar = inputString.charAt(0).toUpperCase();
  const restOfString = inputString.slice(1);

  return firstChar + restOfString;
}

export function getHoursFromDate(dateString: string) {
  const date = new Date(dateString);
  const hours = date.getUTCHours();
  const minutes = date.getUTCMinutes();
  const seconds = date.getUTCSeconds();

  const formattedHours = String(hours).padStart(2, '0');
  const formattedMinutes = String(minutes).padStart(2, '0');
  const formattedSeconds = String(seconds).padStart(2, '0');

  return `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
}

export const notNull = (data: any) => {
  return data !== null;
};

export const notUndefined = (data: any) => {
  return data !== undefined;
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
