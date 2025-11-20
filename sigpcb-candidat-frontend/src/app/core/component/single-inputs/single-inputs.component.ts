import {
  AfterViewInit,
  Component,
  ElementRef,
  EventEmitter,
  Input,
  OnChanges,
  OnDestroy,
  OnInit,
  Output,
  SimpleChanges,
} from '@angular/core';

@Component({
  selector: 'app-single-inputs',
  templateUrl: './single-inputs.component.html',
  styleUrls: ['./single-inputs.component.scss'],
})
export class SingleInputsComponent
  implements OnInit, OnChanges, OnDestroy, AfterViewInit
{
  constructor(private _element: ElementRef<HTMLElement>) {}
  @Input() count = 1;
  /**
   * each input value
   */
  inputs: { name: string; value: number | null }[] = [];
  /**
   * Authorized values
   */
  _autorizedCaract = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
  @Output() isValid = new EventEmitter<{
    isValid: boolean;
    values: (number | null)[];
  }>();
  //input defaut value
  @Input() width = '100%';
  @Input() height = '50px';
  @Input() margin = '5px';
  @Input() values: number[] = [];
  //Call nginit
  ngOnInit(): void {
    this._fill();
    if (this.count > 0) {
      this.width = `${100 / this.count}%`;
    }
  }
  ngAfterViewInit(): void {
    this._focusFirstInput();
  }
  ngOnChanges(changes: SimpleChanges): void {
    if (changes['values']) {
      for (let i = 0; i < this.values.length; i++) {
        const name = `l_${i}`;
        const value = this.values[i];
        this.inputs[i] = { name: name, value: value };
        this._focusFirstInput();
      }
    }
  }

  validate(e: Event) {
    let event = e as InputEvent;
    let input = event.target as HTMLInputElement;
    let value: any = input.value;

    //Pasted
    if (value.length === this.count) {
      this.onPaste(value);
    } else {
      value = value[0];
      if (this._autorizedCaract.includes(value)) {
        value = Number(value);
        this._moveCursor(input.name);
        this._setInput(input.name, value);
        input.value = value;
      } else {
        if (value === '' || value === undefined) {
          this.inputs = this.inputs.map((ip) => {
            if (ip.name === input.name) {
              ip.value = null;
            }
            return ip;
          });

          this._performDeleting(input);
        }
      }
      this.emitIsValid();
    }
  }

  ngOnDestroy(): void {
    this.values = [];
    this.inputs = [];
  }
  valid() {
    return this.inputs.every((ip) => ip.value !== null);
  }

  private emitIsValid() {
    this.isValid.emit({
      isValid: this.valid() && this.inputs.length === this.count,
      values: this.inputs.map((ip) => ip.value),
    });
  }

  private _setInput(name: string, value: any) {
    for (let i = 0; i < this.inputs.length; i++) {
      const ip = this.inputs[i];
      if (ip.name === name) {
        ip.value = value;
      }
      this.inputs[i] = ip;
    }
  }
  private _moveCursor(name: string) {
    const input = this._element.nativeElement.querySelector<HTMLInputElement>(
      `input[name=${name}]`
    );

    if (input) {
      const nextnput = $(input).next('input').get(0);
      if (nextnput) {
        nextnput.focus();
      }
    }
  }
  onPaste(pastedText: string) {
    // Récupérer les données collées
    if (pastedText) {
      if (pastedText.length === this.count) {
        const codes = pastedText.split('');
        if (codes.every((code) => this._autorizedCaract.includes(code))) {
          for (let i = 0; i < this.inputs.length; i++) {
            const ip = this.inputs[i];
            ip.value = Number(codes[i]);
            this.inputs[i] = ip;
            $(`input[name="${ip.name}"]`).val(ip.value);
          }
          const lastInput = $(`input[name="l_${this.count - 1}"]`)
            .first()
            .get(0);
          if (lastInput) {
            lastInput.focus();
          }
          this.emitIsValid();
        }
      }
    }
  }
  private _isArrowOrDeleteKey(event: KeyboardEvent): boolean {
    const arrowKeys = ['ArrowLeft', 'ArrowRight'];
    const deleteKeys = ['Delete', 'Backspace'];

    return arrowKeys.includes(event.key) || deleteKeys.includes(event.key);
  }

  private _performDeleting(input: HTMLInputElement) {
    const currentIndex = this.inputs.findIndex((ip) => ip.name === input.name);

    if (currentIndex > 0) {
      // Delete input content and move cursor to the previous input
      const previousInput =
        this._element.nativeElement.querySelector<HTMLInputElement>(
          `input[name=${this.inputs[currentIndex - 1].name}]`
        );
      if (previousInput && input.value.length == 0) {
        previousInput.focus();
      }
    }
  }

  private _moveCursorOnArrowPressed(event: KeyboardEvent) {
    const input = event.target as HTMLInputElement;
    const currentIndex = this.inputs.findIndex((ip) => ip.name === input.name);

    if (event.key === 'ArrowLeft') {
      if (currentIndex > 0) {
        // Move cursor to the previous input
        const previousInput =
          this._element.nativeElement.querySelector<HTMLInputElement>(
            `input[name=${this.inputs[currentIndex - 1].name}]`
          );
        if (previousInput) {
          previousInput.focus();
        }
      }
    } else if (event.key === 'ArrowRight') {
      if (currentIndex < this.inputs.length - 1) {
        // Move cursor to the next input
        const nextInput =
          this._element.nativeElement.querySelector<HTMLInputElement>(
            `input[name=${this.inputs[currentIndex + 1].name}]`
          );
        if (nextInput) {
          nextInput.focus();
        }
      }
    }
  }

  onKeyPress(event: any) {
    if (this._isArrowOrDeleteKey(event)) {
      this._moveCursorOnArrowPressed(event);
      if (event.key == 'Delete' || event.key == 'Backspace') {
        this._performDeleting(event.target);
      }
      this.emitIsValid();
    }
  }
  private _fill() {
    if (!this.values.length) {
      for (let i = 0; i < this.count; i++) {
        const name = `l_${i}`;
        this.inputs[i] = { name: name, value: null };
      }
    } else {
      for (let i = 0; i < this.values.length; i++) {
        const name = `l_${i}`;
        const value = this.values[i];
        this.inputs[i] = { name: name, value: value };
      }
    }
  }
  private _focusFirstInput() {
    const firstInput =
      this._element.nativeElement.querySelector<HTMLInputElement>(
        'input[name=l_0]'
      );
    if (firstInput) {
      firstInput.focus();
    }
  }
}
