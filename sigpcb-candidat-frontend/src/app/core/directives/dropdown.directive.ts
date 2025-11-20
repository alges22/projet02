import {
  Directive,
  OnInit,
  ElementRef,
  AfterViewInit,
  HostListener,
} from '@angular/core';
import * as bootstrap from 'bootstrap';

@Directive({
  selector: '[data-bs-toggle]',
})
export class DropdownDirective implements OnInit, AfterViewInit {
  private nativeElement!: HTMLElement;
  constructor(private ref: ElementRef<HTMLElement>) {
    this.nativeElement = ref.nativeElement;
  }

  ngOnInit(): void {}
  ngAfterViewInit(): void {
    const button = this.nativeElement.querySelector('.dropdown-btn');
    if (button) {
      const drp = new bootstrap.Dropdown(this.nativeElement);
      button.addEventListener('click', () => {
        drp.toggle();
      });
    }
  }
  @HostListener('click')
  toggle() {
    new bootstrap.Dropdown(this.ref.nativeElement);
  }
}
