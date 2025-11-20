import { Promoteur } from 'src/app/core/interfaces/user.interface';
import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-avatar',
  templateUrl: './avatar.component.html',
  styleUrls: ['./avatar.component.scss'],
})
export class AvatarComponent {
  @Input() promoteur: Promoteur | null = null;
}
