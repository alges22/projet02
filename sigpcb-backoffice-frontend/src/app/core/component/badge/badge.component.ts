import { Component, Input } from '@angular/core';

@Component({
  selector: 'badge',
  template: `<span [class]="'badge-customer badge-customer-' + background">{{
    text
  }}</span>`,
  styleUrls: ['./badge.component.scss'],
})
export class BadgeComponent {
  @Input() text = '';
  @Input() type: string = 'primary';

  @Input() badge: { type: string; text: string } | null = null;

  ngOnInit() {
    if (this.badge) {
      this.type = this.badge.type || this.type;
      this.text = this.badge.text || this.text;
    }
  }

  get background() {
    const statues: Record<string, string> = {
      init: 'info',
      rejected: 'danger',
      validated: 'success',
      used: 'primary',
    };
    return statues[this.type] ?? 'warning';
  }
}
