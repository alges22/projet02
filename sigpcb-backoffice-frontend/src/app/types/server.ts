import { ElementRef } from '@angular/core';
import { ServerResponseType } from './../core/types/server-response.type';
export type ServerResponseCallback = (
  response: ServerResponseType,
  elementId: string
) => void;
