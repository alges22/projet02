import { AfterViewInit, Directive, ElementRef } from '@angular/core';

@Directive({
  selector: '[quickv]',
})
export class QuickvDirective implements AfterViewInit {
  constructor(private refElement: ElementRef<HTMLElement>) {}

  ngAfterViewInit(): void {
    //@ts-ignore
    const qvForm = new QvForm(this.refElement.nativeElement);
    qvForm.init();
  }
}
