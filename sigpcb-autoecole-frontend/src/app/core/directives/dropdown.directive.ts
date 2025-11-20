import { Element } from '@angular/compiler';
import { Directive, ElementRef, HostListener } from '@angular/core';
import * as bootstrap from 'bootstrap';

@Directive({
  selector: '[data-bs-toggle]',
})
export class DropdownDirective {
  constructor(private el: ElementRef<HTMLElement>) {}

  @HostListener('click')
  toggle() {
    new bootstrap.Dropdown(this.el.nativeElement);
  }
}
