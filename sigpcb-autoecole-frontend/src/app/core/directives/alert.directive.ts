import { IAlert } from 'src/app/core/interfaces/alert';
import {
  AfterViewInit,
  Directive,
  ElementRef,
  HostListener,
  OnInit,
} from '@angular/core';

@Directive({
  selector: '#alert-global',
})
export class AlertDirective implements OnInit, AfterViewInit {
  ialert: IAlert = {
    type: 'warning',
    message: 'Alert !',
    position: 'bottom-right',
    timeout: 10000,
    fixed: false,
  };
  constructor(private refElement: ElementRef<HTMLElement>) {}

  ngOnInit(): void {
    //
  }

  ngAfterViewInit(): void {
    this.setStyles();
    this.noActionAlert();
    this.setMessage();
    this.setPositions();
    this.closeAlert();
  }
  @HostListener('alert-occure', ['$event'])
  initAlert(e: CustomEvent) {
    this.ialert = e.detail as IAlert;
    this.setStyles();
    this.setMessage();
    this.setPositions();

    //Ceci en dernier
    this.showAlertBox();
  }

  private showAlertBox() {
    if (this.refElement.nativeElement.classList.contains('middle')) {
      this.refElement.nativeElement.style.display = 'flex';
    } else {
      $(this.refElement.nativeElement).fadeIn();
    }
    if (!this.ialert.fixed) {
      setTimeout(() => {
        $(this.refElement.nativeElement).fadeOut();
      }, this.ialert.timeout ?? 10000);
    }
  }

  private noActionAlert() {
    const alertBody =
      this.refElement.nativeElement.querySelector('.alert-content');
    if (this.refElement.nativeElement.classList.contains('middle')) {
      document.addEventListener('click', (event) => {
        const target = event.target as HTMLElement;
        if (alertBody) {
          if (!alertBody.contains(target)) {
            alertBody.classList.add('scale');
          }

          setTimeout(() => {
            alertBody.classList.remove('scale');
          }, 10000);
        }
      });
    }
  }

  private setStyles() {
    ['warning', 'success', 'danger'].forEach((type) => {
      const iconEl = this.refElement.nativeElement.querySelector<HTMLElement>(
        `.text-${type}`
      );
      if (iconEl) {
        iconEl.style.display = 'none';
        const alertContent =
          this.refElement.nativeElement.querySelector('.alert-content');
        if (alertContent) {
          alertContent.classList.remove(`border-${type}`);
        }
      }
    });
    // Remets l'icon courant
    const iconEl = this.refElement.nativeElement.querySelector<HTMLElement>(
      `.text-${this.ialert.type}`
    );
    if (iconEl) {
      iconEl.style.display = 'inline-block';
    }
    // Remets les couleurs de border
    const alertContent =
      this.refElement.nativeElement.querySelector('.alert-content');
    if (alertContent) {
      alertContent.classList.add(`border-${this.ialert.type}`);
    }
  }

  private closeAlert() {
    const closeBtn =
      this.refElement.nativeElement.querySelector<HTMLElement>('.close-btn');
    if (closeBtn) {
      closeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        $(this.refElement.nativeElement).fadeOut();
      });
    }
  }

  private setMessage() {
    const alertContent =
      this.refElement.nativeElement.querySelector<HTMLElement>(
        '.alert-message'
      );

    if (alertContent) {
      alertContent.innerHTML =
        this.ialert.message.length < 1
          ? 'Un problème est survenu, prière réactualiser la page et réessayer'
          : this.ialert.message;
    }
  }

  private setPositions() {
    ['top-left', 'middle', 'top-right', 'bottom-left', 'bottom-right'].forEach(
      (pos) => {
        if (this.refElement.nativeElement.classList.contains(pos)) {
          this.refElement.nativeElement.classList.remove(pos);
        }
      }
    );
    this.refElement.nativeElement.classList.add(this.ialert.position);
  }
}
