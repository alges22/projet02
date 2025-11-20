import {
  Component,
  OnInit,
  Input,
  ViewChild,
  ElementRef,
  AfterViewInit,
  OnDestroy,
} from '@angular/core';
import { Router } from '@angular/router';
import { ProfileData } from 'src/app/core/interfaces/profiles';
import { ProfileService } from 'src/app/core/services/profile.service';
import { StateService } from 'src/app/core/services/state.service';

@Component({
  selector: 'app-informations',
  templateUrl: './informations.component.html',
  styleUrls: ['./informations.component.scss'],
})
export class InformationsComponent implements OnInit {
  @ViewChild('nextbutton') nextButtonRef: ElementRef<HTMLButtonElement> | null =
    null;

  @Input() profileData!: ProfileData;

  constructor(private stateService: StateService) {}

  ngOnInit(): void {
    setTimeout(() => {
      this.gotoStartCompo();
    }, 40000);
  }
  onPress() {
    this.stateService.enterFullScreen();
    this.stateService.changePage('start-compo');
  }
  /**
   * Après  une minute redirigé le convert après une 1min
   */
  gotoStartCompo() {
    if (this.nextButtonRef) {
      if (this.nextButtonRef.nativeElement) {
        this.nextButtonRef.nativeElement.click();
      }
    }
  }
}
