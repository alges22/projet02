import { ActivatedRoute, ParamMap } from '@angular/router';
import { KeyValueParam } from './types';
import { UrlMaker } from './url-maker';
import { AlertPosition, AlertType } from '../core/interfaces/alert';
import { environment } from 'src/environments/environment';

export const apiUrl = (path: string) => {
  return environment.endpoints.autoecole.concat(path);
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
 *  Cette fonction permettra de rafraichir par exemple la page courante
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

export function hashPhone(phone: string): string {
  // Vérifier si la chaîne a au moins 8 caractères
  if (phone.length >= 8) {
    // Extraire les deux premiers et les deux derniers caractères
    const premiers = phone.substring(0, 2);
    const derniers = phone.substring(phone.length - 2);

    const milieuMasque = '****';

    const numeroMasque = premiers + milieuMasque + derniers;

    return numeroMasque;
  } else {
    // Retourner le numéro tel quel s'il a moins de 8 caractères
    return phone;
  }
}

export function trim(input: any): string {
  if (typeof input !== 'string') {
    input = String(input);
  }
  return input.trim();
}

export function isDigit(value: any): boolean {
  return ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'].includes(value);
}

// Générer un identifiant unique
export function uniqueID() {
  const str = Math.random().toString(36).substring(2);
  return toBase64(str);
}

export function toBase64(str: string): string {
  const encoder = new TextEncoder();
  const utf8Array = encoder.encode(str);

  // Utiliser Array.from pour créer un tableau standard à partir de l'Uint8Array
  const utf8ArrayStandard = Array.from(utf8Array);

  const base64String = btoa(String.fromCharCode.apply(null, utf8ArrayStandard));
  return base64String;
}
type FormDataEntry = {
  name: string;
  value: any;
};
export function toFormData(
  object: any,
  append: FormDataEntry[] = []
): FormData {
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

export function dateCounter(dateExpiration: string): number {
  const dateActuelle = new Date();
  const dateExpirationObj = new Date(dateExpiration);
  const diffTemps = dateExpirationObj.getTime() - dateActuelle.getTime();

  const diffJours = Math.ceil(diffTemps / (1000 * 3600 * 24));

  return diffJours;
}
export function replaceSpace(string: string, by = '') {
  return string.replace(/ /g, by);
}
