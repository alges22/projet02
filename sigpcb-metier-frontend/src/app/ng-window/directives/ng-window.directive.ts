import { AfterViewInit, Directive, ElementRef } from '@angular/core';
@Directive({
  selector: '[ng-window]',
})
export class NgWindowDirective implements AfterViewInit {
  private parentElement: HTMLElement | null = null;
  private nativeElement!: HTMLElement;
  constructor(private refElement: ElementRef<HTMLElement>) {
    this.nativeElement = refElement.nativeElement;
    this.parentElement = this.nativeElement.parentElement;
  }

  pageList!: NodeListOf<HTMLElement>;
  btnList!: NodeListOf<HTMLButtonElement>;

  ngAfterViewInit(): void {
    this.setElements();
    this.onSelect();
  }

  private setElements() {
    this.pageList =
      this.refElement.nativeElement.querySelectorAll('app-ng-window-page');
    this.btnList = this.refElement.nativeElement.querySelectorAll(
      '[data-window-action]'
    );

    this.pageList.forEach((p) => {
      $(p).hide();
      if ($(p).data('windowCurrent')) {
        $(p).show();
      }
    });
  }
  private onSelect() {
    this.btnList.forEach((button) => {
      $(button).on('click', (e) => {
        e.preventDefault();
        this.selectBtns($(button).data('windowAction'));
      });
    });
  }

  private selectBtns(i: number) {
    this.btnList.forEach((b) => $(b).removeClass('current'));
    const b = Array.from(this.btnList).find((btn) => {
      return $(btn).data('windowAction') === i;
    });
    if (b) {
      $(b).addClass('current');

      this.selectPage(b);
    }
  }
  private selectPage(button: HTMLButtonElement) {
    const action = $(button).data('windowAction');
    this.pageList.forEach((p) => {
      $(p).hide();
      if ($(p).data('windowPage') == action) {
        $(p).show();
      }
    });
  }

  private emitGotoPrev(el: HTMLElement) {
    const prevPageId = $(el).data('ngWindowPrev');
    this.parentElement?.dispatchEvent(
      new CustomEvent('ng-window.goto.prev', {
        detail: prevPageId,
        bubbles: true,
        cancelable: true,
      })
    );
  }
  private emitGotoNext(el: HTMLElement) {
    const nextPageId = $(el).data('ngWindowPrev');
    this.parentElement?.dispatchEvent(
      new CustomEvent('ng-window.goto.next', {
        detail: nextPageId,
        bubbles: true,
        cancelable: true,
      })
    );
  }

  private onGotoCurrent() {
    this.parentElement?.addEventListener('ng-window.goto', (e) => {});
  }
}
