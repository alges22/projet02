import { ServerResponseType } from './../types/server-response.type';
import { Directive, ElementRef, HostListener, OnInit } from '@angular/core';

@Directive({
  selector: 'form',
})
export class NeedValidationsDirective implements OnInit {
  error!: ServerResponseType;
  constructor(private el: ElementRef<HTMLElement>) {}
  count = 0;
  ngOnInit(): void {
    //
  }
  @HostListener('error-occure', ['$event'])
  displayErrors(event: CustomEvent) {
    this.error = event.detail;
    this.showGlobalMessage();
    this.updateErrorMessage();
  }

  private updateErrorMessage() {
    if (typeof this.error.errors === 'object') {
      for (const name in this.error.errors) {
        if (Object.prototype.hasOwnProperty.call(this.error.errors, name)) {
          const message = this.error.errors?.[name];
          const element = this.el.nativeElement.querySelector(
            `[data-qv-feedback=${name}]`
          );
          if (element) {
            if (message) {
              element.innerHTML =
                typeof message !== 'string' ? message[0] : message;
            }
          }
        }
      }
    }
  }
  @HostListener('clear-messages', ['$event'])
  clearErrorMessage() {
    const globalMessage = this.el.nativeElement.querySelector<HTMLElement>(
      '[data-global-message]'
    );
    if (globalMessage !== null) {
      $(globalMessage).html('');
      $(globalMessage).fadeOut();
    }
    const errorMessages =
      this.el.nativeElement.querySelectorAll<HTMLElement>(`[data-qv-feedback]`);

    errorMessages.forEach((element) => {
      element.innerText = '';
    });
  }

  private showGlobalMessage() {
    const globalMessage = this.el.nativeElement.querySelector<HTMLElement>(
      '[data-global-message]'
    );
    if (this.error.message && globalMessage !== null) {
      $(globalMessage).html(this.error.message);
      $(globalMessage).fadeIn();
    }
  }
}
