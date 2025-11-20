import { Directive, ElementRef, HostListener } from '@angular/core';

@Directive({
  selector: '[prevent]',
})
export class PreventLinkDirective {
  constructor(private ref: ElementRef<HTMLAnchorElement>) {}

  @HostListener('click', ['$event'])
  prevent(event: Event) {
    event.preventDefault();
  }
}
