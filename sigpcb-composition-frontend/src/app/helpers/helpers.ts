import { ActivatedRoute, ParamMap } from '@angular/router';
import { KeyValueParam } from './types';
import { UrlMaker } from './url-maker';
import { AlertPosition, AlertType } from '../core/interfaces/alert';
import { environment } from 'src/environments/environment';

export const apiUrl = (path: string) => {
  return environment.endpoints.compo.concat(path);
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

/**
 *  Cette fonction permettra de rafraichir par exemple la  courante
 * @param delay
 */
export function refresh(delay: number = 0) {
  setTimeout(() => {
    location.reload();
  }, delay);
}

/***
 * Affiche les fichiers
 */
export const asset = (path: string) => {
  return path;
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

export const round = (nombreDecimal: number) => {
  if (nombreDecimal >= 0.5) {
    return Math.ceil(nombreDecimal);
  } else {
    return Math.floor(nombreDecimal);
  }
};
