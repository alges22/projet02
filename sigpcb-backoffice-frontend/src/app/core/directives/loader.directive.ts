import { Directive, HostListener, ElementRef } from '@angular/core';

@Directive({
  selector: '#global-loader',
})
export class LoaderDirective {
  constructor(private ref: ElementRef<HTMLElement>) {}

  @HostListener('show-loader', ['$event'])
  showLoader(event: CustomEvent) {
    const detail = event.detail as any;

    // Si le détail et le message existe
    if (detail) {
      $(this.ref.nativeElement)
        .find('.loader-text')
        .html(detail.message ?? 'Chargement en cours');
    }
    this.ref.nativeElement.style.display = 'flex';
  }
  @HostListener('hide-loader')
  hideLoader() {
    this.ref.nativeElement.style.display = 'none';
  }
}
