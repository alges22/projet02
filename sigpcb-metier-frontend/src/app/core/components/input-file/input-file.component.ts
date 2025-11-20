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
} from '@angular/core';
import { trim } from 'lodash';
import { stringAfterLast, truncate, uniqueID } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-input-file',
  templateUrl: './input-file.component.html',
  styleUrls: ['./input-file.component.scss'],
})
export class InputFileComponent implements AfterViewInit, OnInit, OnChanges {
  pdfSrc = '';
  @Input() name = '';
  @Input() accept = '';
  @Input() required = '';
  @Output() changeEvent = new EventEmitter<File | undefined>();
  @Input() placeholder = 'Choisissez un fichier';
  private _input: HTMLInputElement | null = null;
  inputId = '';
  isSelected: boolean = false;
  @Input() selectedImage: string = '';
  file: File | undefined = undefined;
  constructor(private _ref: ElementRef<HTMLElement>) {}

  ngOnInit(): void {
    this.inputId = trim(uniqueID(16));
    this.setImageUrl();
  }
  ngOnChanges(changes: SimpleChanges): void {
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
    this._input = this._ref.nativeElement.querySelector('input[type="file"]');

    if (this._input) {
      this._input.addEventListener('click', (event) => {
        event.stopPropagation();
      });
    }
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
}
