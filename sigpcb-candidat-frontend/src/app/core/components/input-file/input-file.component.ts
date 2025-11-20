import {
  AfterViewInit,
  Component,
  ElementRef,
  EventEmitter,
  Input,
  OnChanges,
  OnInit,
  Output,
  SimpleChanges,
  ViewChild,
} from '@angular/core';
import { trim } from 'lodash';
import { stringAfterLast, truncate, uniqueID } from 'src/app/helpers/helpers';
import { TrivuleInput } from 'trivule';

@Component({
  selector: 'app-input-file',
  templateUrl: './input-file.component.html',
  styleUrls: ['./input-file.component.scss'],
})
export class InputFileComponent implements AfterViewInit, OnInit, OnChanges {
  pdfSrc = '';
  @ViewChild('inputBox') inputBox!: ElementRef<HTMLElement>;
  @Input() name = '';
  @Input() accept = '';
  @Input() required = '';
  @Output() changeEvent = new EventEmitter<File | undefined>();
  @Input() placeholder = 'Choisissez un fichier';
  @Input() maxSize = 2048;
  currentSize: string | null = null; //en MB, KB
  private _input: HTMLInputElement | null = null;
  inputId = '';
  isSelected: boolean = false;
  @Input() selectedImage: string = '';
  file: File | undefined = undefined;
  @Input() rules: {
    rule: string;
    message?: string;
  }[] = [];
  trivuleInput: TrivuleInput | undefined = undefined;
  @Output('_validate') validateEvent = new EventEmitter<boolean>();

  ngOnInit(): void {
    this.inputId = trim(uniqueID(16));
    this.setImageUrl();
  }
  ngOnChanges(changes: SimpleChanges): void {
    this.validate();
    if (changes) {
      this.setImageUrl();
    }
  }
  onChange(event: any) {
    const file = event.target?.files[0] as File | undefined;
    if (file && file.type.startsWith('image/')) {
      this.placeholder = truncate(file.name) + stringAfterLast(file.name, '.');
      this.isSelected = true;
      this.selectedImage = URL.createObjectURL(file);
    }
    this.file = file;
    this.changeEvent.emit(this.file);
  }

  openFileInput() {
    if (this._input) {
      this._input.click();
    }
  }

  ngAfterViewInit(): void {
    this.validate();
  }

  remove() {
    this.placeholder = 'Choisissez un fichier';
    this.isSelected = false;
    this.file = undefined;
    this.changeEvent.emit(this.file);
  }
  openModal() {
    if (this.selectedImage.length) {
      $(`#${this.inputId}`).modal('show');
    }
  }
  private setImageUrl() {
    if (!!this.selectedImage && this.selectedImage.length > 1) {
      this.isSelected = true;
    }
  }
  validate() {
    if (this.inputBox) {
      if (!this._input) {
        this._input =
          this.inputBox.nativeElement.querySelector('input[type="file"]');
        if (this._input) {
          this._input.addEventListener('click', (event) => {
            event.stopPropagation();
          });

          this.trivuleInput = new TrivuleInput(this._input, {
            feedbackElement: '.text-feedback',
          });

          this.trivuleInput.onPasses(() => {
            this.validateEvent.emit(true);
            this.inputBox.nativeElement.classList.remove('border-danger');
            this.inputBox.nativeElement.classList.add('border-primary');
          });
          this.trivuleInput.onFails(() => {
            this.inputBox.nativeElement.classList.remove('border-primary');
            this.inputBox.nativeElement.classList.add(
              'border',
              'border-danger'
            );
            this.validateEvent.emit(false);
          });
        }
      }
    }

    const rules = this.rules;
    if (!!rules && !!this.trivuleInput) {
      for (const r of rules) {
        this.trivuleInput.prependRule(r);
      }
    }
  }
}
