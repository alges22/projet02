import {
  Component,
  EventEmitter,
  Input,
  Output,
  OnInit,
  SimpleChanges,
} from '@angular/core';

@Component({
  selector: 'app-switch',
  templateUrl: './switch.component.html',
  styleUrls: ['./switch.component.scss'],
})
export class SwitchComponent implements OnInit {
  @Input() activateId: number = 0;
  @Input('show-alert') show = false;
  private hasChange = false;
  @Input() checked = false;

  @Output() statusChangeEvent = new EventEmitter<{
    id: number;
    status: boolean;
  }>();
  private inputElement!: HTMLInputElement;
  ngOnInit(): void {}

  confirmSwitch() {
    this.checked = Boolean(this.inputElement.checked);

    this.statusChangeEvent.emit({
      id: this.activateId,
      status: this.checked,
    });
    this.show = false;
  }
  cancel() {
    //Si activateId est différent de 0 alors il y a une action en cours
    if (this.hasChange) {
      this.inputElement.click();
      this.hasChange = false;
    }
    this.show = false;
  }

  switch(activateId: number, event: Event) {
    this.show = true;
    this.activateId = activateId;
    this.hasChange = true;
    this.inputElement = event.target as HTMLInputElement;
  }
}
