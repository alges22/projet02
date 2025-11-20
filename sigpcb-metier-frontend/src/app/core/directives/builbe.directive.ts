import { Directive, ElementRef, Input, OnInit } from '@angular/core';
import * as bootstrap from 'bootstrap';

@Directive({
  selector: '[info]',
})
export class BuilbeDirective implements OnInit {
  private nativeElement!: HTMLElement;

  @Input() info!: string;

  @Input() position = 'top';

  constructor(private ref: ElementRef<HTMLElement>) {
    this.nativeElement = ref.nativeElement;
  }
  ngOnInit(): void {
    if (this.info) {
      const toolip = new bootstrap.Tooltip(this.nativeElement, {
        title: this.info,
        placement: this.placement(),
      });
    }
  }

  private placement(): any {
    return this.position;
  }
}
