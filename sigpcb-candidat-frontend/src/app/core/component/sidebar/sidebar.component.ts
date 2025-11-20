import {
  Component,
  ElementRef,
  Input,
  SimpleChanges,
  ViewChild,
} from '@angular/core';

@Component({
  selector: 'app-sidebar',
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.scss'],
})
export class SidebarComponent {
  @Input() auth: any = null;
  @Input() toggle = false;
  @ViewChild('sidebar') sidebarRef!: ElementRef<HTMLElement>;
  ngOnChanges(changes: SimpleChanges): void {
    if (changes['toggle'] && !changes['toggle'].firstChange) {
      this.toggleSidebar();
    }
  }
  userConnected() {
    return !!this.auth;
  }

  toggleSidebar() {
    this.sidebarRef.nativeElement.classList.toggle('active');
  }
}
