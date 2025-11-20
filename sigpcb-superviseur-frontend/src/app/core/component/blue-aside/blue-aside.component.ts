import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-blue-aside',
  templateUrl: './blue-aside.component.html',
  styleUrls: ['./blue-aside.component.scss'],
})
export class BlueAsideComponent {
  lang: any;
  @Input('auth') auth = null;
  constructor() {}

  ngOnInit(): void {}
}
