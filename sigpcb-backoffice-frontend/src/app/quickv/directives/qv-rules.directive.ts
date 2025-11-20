import { Directive, HostBinding, Input } from '@angular/core';

@Directive({
  selector: '[qv-rules]',
})
export class QvRulesDirective {
  @Input('qv-rules') qvRules: string = '';

  @HostBinding('attr.data-qv-rules') get qvRulesValues() {
    return this.qvRules;
  }
}
