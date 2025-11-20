import { Directive, HostBinding, Input } from '@angular/core';

@Directive({
  selector: '[qv-submit]',
})
export class QvSubmitDirective {
  @Input('qv-submit') qvRules: string = '';

  @HostBinding('attr.data-qv-submit') get qvRulesValues() {
    return this.qvRules;
  }
}
