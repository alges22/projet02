import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-permis',
  templateUrl: './permis.component.html',
  styleUrls: ['./permis.component.scss'],
})
export class PermisComponent {
  @Input() permis: string | null = null;
  @Input() checked = false;
  @Input() isSelected: boolean | undefined;
  generateImage() {
    return this.permis ? this.permis.toLocaleLowerCase() : '';
  }

  check() {
    this.checked = !this.checked;
  }
}
