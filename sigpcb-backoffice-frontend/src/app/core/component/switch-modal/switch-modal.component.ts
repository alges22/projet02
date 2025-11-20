import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'app-switch-modal',
  templateUrl: './switch-modal.component.html',
  styleUrls: ['./switch-modal.component.scss'],
})
export class SwitchModalComponent {
  @Input() activateId: number = 0;
  @Input('show-alert') show = false;
  hasChange: boolean = false;
  @Input() checked: boolean = false;
  @Input() motif = '';

  @Output() statusChangeEvent = new EventEmitter<{
    // id: number;
    status: boolean;
    hasChange: boolean;
    // motif: string;
  }>();
  private inputElement!: HTMLInputElement;
  private switchState: boolean = false;
  ngOnInit(): void {}

  // active() {
  //   this.checked = this.switchState;
  //   this.statusChangeEvent.emit({
  //     id: this.activateId,
  //     status: this.checked,
  //     motif: '',
  //   });
  // }

  // desactive() {
  //   this.checked = this.switchState;
  //   this.statusChangeEvent.emit({
  //     id: this.activateId,
  //     status: this.checked,
  //     motif: this.motif,
  //   });
  //   $('#motif-modal').modal('hide');
  // }
  // cancel() {
  //   //Si activateId est différent de 0 alors il y a une action en cours
  //   console.log(this.hasChange);
  //   if (this.hasChange) {
  //     console.log(this.switchState);
  //     this.switchState = !this.switchState;
  //     this.hasChange = false;
  //   }
  //   this.show = false;
  // }

  cancel() {
    // Si activateId est différent de 0 alors il y a une action en cours
    console.log(this.hasChange);
    // if (this.hasChange) {
    //   console.log(this.switchState);

    //   // Stocker l'état de this.hasChange avant de le réinitialiser
    //   const hasChangeTemp = this.hasChange;
    //   console.log(hasChangeTemp);

    //   // Réinitialiser this.hasChange
    //   this.hasChange = false;

    //   // Si l'interrupteur a été changé mais l'action n'a pas été confirmée, rétablissez son état précédent
    //   if (hasChangeTemp && this.switchState !== this.checked) {
    //     this.switchState = this.checked;
    //   }
    // }
  }

  // switch(activateId: number, event: Event) {
  //   this.show = true;
  //   this.activateId = activateId;
  //   this.hasChange = true;

  //   if (event && event.target) {
  //     // Vérification que l'élément cible est une case à cocher
  //     if (event.target instanceof HTMLInputElement) {
  //       this.inputElement = event.target;
  //       this.switchState = event.target.checked;
  //       console.log(this.hasChange, this.switchState);
  //       if (!this.switchState) {
  //         $('#motif-modal').modal('show');
  //       } else {
  //         this.active();
  //       }
  //     }
  //   }
  // }

  // switch(activateId: number, event: Event) {
  //   this.show = true;
  //   this.activateId = activateId;
  //   this.hasChange = true;
  //   if (event && event.target) {
  //     // Vérification que l'élément cible est une case à cocher
  //     if (event.target instanceof HTMLInputElement) {
  //       this.inputElement = event.target;
  //       this.switchState = event.target.checked;
  //       console.log(this.hasChange, this.switchState);
  //       if (!this.switchState) {
  //         $('#motif-modal').modal('show');
  //       } else {
  //         this.active();
  //       }
  //     }
  //   }
  // }
  switch(activateId: number, event: Event) {
    // this.show = true;
    // this.activateId = activateId;
    this.hasChange = true;
    this.inputElement = event.target as HTMLInputElement;
    console.log(this.hasChange, this.inputElement.checked);
    this.statusChangeEvent.emit({
      // id: this.activateId,
      status: this.inputElement.checked,
      // motif: this.motif,
      hasChange: this.hasChange,
    });
  }
}
