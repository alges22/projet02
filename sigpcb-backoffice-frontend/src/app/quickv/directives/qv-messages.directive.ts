import { Directive, HostBinding, Input } from '@angular/core';

@Directive({
  selector: '[qv-messages]',
})
export class QvMessagesDirective {
  constructor() {}
  @Input('qv-messages') qvFeedback: string = '';

  @HostBinding('attr.data-qv-messages') get qvFeedbackValues() {
    return this.qvFeedback;
  }
}
