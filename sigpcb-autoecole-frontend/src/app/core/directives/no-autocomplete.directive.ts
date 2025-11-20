import { Directive, HostBinding } from '@angular/core';

@Directive({
  selector: 'input',
})
export class NoAutocompleteDirective {
  @HostBinding('autocomplete')
  autocomplete = 'off';
}
