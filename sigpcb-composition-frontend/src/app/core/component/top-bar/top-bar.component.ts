import { Component, Input } from '@angular/core';
import { ProfileData } from '../../interfaces/profiles';

@Component({
  selector: 'app-top-bar',
  templateUrl: './top-bar.component.html',
  styleUrls: ['./top-bar.component.scss'],
})
export class TopBarComponent {
  @Input() profileData!: ProfileData;
}
