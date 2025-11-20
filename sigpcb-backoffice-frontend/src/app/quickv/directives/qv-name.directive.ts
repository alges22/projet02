import { Directive, HostBinding, Input } from '@angular/core';

@Directive({
  selector: '[qv-name]',
})
export class QvNameDirective {
  @Input('qv-name') qvName: string = '';

  @HostBinding('attr.data-qv-name') get qvNameValues() {
    return this.qvName;
  }
}
