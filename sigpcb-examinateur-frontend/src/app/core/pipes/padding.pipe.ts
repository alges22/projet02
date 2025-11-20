import { Pipe, PipeTransform } from '@angular/core';
import { numberPad } from 'src/app/helpers/helpers';

@Pipe({
  name: 'padding',
})
export class PaddingPipe implements PipeTransform {
  transform(
    value: string | number | null | undefined = 0,
    length: number = 3,
    padChar: string = '0'
  ): string {
    if (value === undefined || value === null) {
      return '';
    }
    return numberPad(value, length, padChar);
  }
}
