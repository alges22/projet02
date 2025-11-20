import { Directive, HostBinding, Input } from '@angular/core';

@Directive({
  selector: '[qv-rules]',
})
export class QvRulesDirective {
  @Input('qv-rules') qvRules: string = '';

  @Input('qv-invalid-class') qvInvalidClass = 'is-invalid';

  @HostBinding('attr.data-qv-rules') get qvRulesValues() {
    return this.qvRules;
  }

  @HostBinding('attr.data-qv-invalid-class') get qvInvalidClassValues() {
    return this.qvInvalidClass;
  }
}
