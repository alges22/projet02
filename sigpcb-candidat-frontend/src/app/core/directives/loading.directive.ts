import {
  AfterViewInit,
  Directive,
  ElementRef,
  HostListener,
  Input,
} from '@angular/core';

@Directive({
  selector: '[loading]',
})
export class LoadingDirective implements AfterViewInit {
  private nativeElement!: HTMLElement;
  constructor(refElement: ElementRef<HTMLElement>) {}
  buttons: string[] = [];
  @Input('display') display = 'inline-block';
  private buttonsElements: HTMLElement[] = [];
  @HostListener('on-loading')
  showLoader() {
    this.nativeElement.style.display = 'none';
    this.disableBtn();
  }

  @HostListener('stop-loading')
  hideLoader() {
    this.nativeElement.style.display = this.display;
    this.enableButton();
  }

  private disableBtn() {
    this.buttonsElements.forEach((el) => el.setAttribute('disabled', 'true'));
  }

  private enableButton() {
    this.buttonsElements.forEach((el) => el.removeAttribute('disabled'));
  }

  ngAfterViewInit(): void {
    for (const cls of this.buttons) {
      const el = document.querySelector<HTMLElement>(cls);
      if (el) {
        this.buttonsElements.push(el);
      }
    }
  }
}
