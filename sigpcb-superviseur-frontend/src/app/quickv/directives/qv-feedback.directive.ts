import { Directive, HostBinding, Input } from '@angular/core';

@Directive({
  selector: '[qv-feedback]',
})
export class QvFeedbackDirective {
  @Input('qv-feedback') qvFeedback: string = '';

  @HostBinding('attr.data-qv-feedback') get qvFeedbackValues() {
    return this.qvFeedback;
  }
}
