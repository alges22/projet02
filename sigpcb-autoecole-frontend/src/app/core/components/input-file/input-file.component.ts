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
import { truncate } from 'src/app/helpers/helpers';

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
  @Input() multiple = false;
  @Input() defaultPath: string[] = [];
  @Output() changeEvent = new EventEmitter<File | undefined | File[]>();
  @Input() placeholder = 'Choisissez un fichier' as string | null;
  private _input: HTMLInputElement | null = null;
  inputId = '';
  isSelected: boolean = false;
  selectedURL: string = '';
  file: File | undefined = undefined;
  files: File[] = [];
  multiplepreviews: string[] = [];
  @Input() type = 'file' as 'img' | 'file' | 'pdf' | 'word';
  constructor(private _ref: ElementRef<HTMLElement>) {}

  ngOnInit(): void {
    this.placeholder = this.placeholder || 'Choisissez un fichier';
  }
  onChange(event: any) {
    this.multiplepreviews = [];
    let file: File | undefined = undefined;
    let files = event.target.files;
    if (!this.multiple) {
      file = files[0];
      if (file) {
        this.placeholder = truncate(file.name);
        this.selectedURL = URL.createObjectURL(file);
        this.isSelected = true;
        this.defaultPath = [];
      }
      this.file = file;
      this.changeEvent.emit(this.file);
    } else {
      files = Array.from(files);
      this.placeholder = `${files.length} fichier(s) sélectionné(s)`;
      for (const f of files) {
        this.selectedURL = URL.createObjectURL(f);
        this.multiplepreviews.push(this.selectedURL);
        this.isSelected = true;
        this.defaultPath = [];
      }
      this.changeEvent.emit(files);
    }
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['defaultPath']) {
      if (this.defaultPath) {
        if (this.defaultPath.length == 1) {
          this.placeholder = truncate(
            this.extractFileNameFromPath(this.defaultPath[0]) ||
              this.placeholder ||
              'Choissez un fichier',
            80
          );
          this.selectedURL = this.defaultPath[0];
          this.multiplepreviews.push(this.selectedURL);
        } else {
          this.placeholder = '0' + this.defaultPath.length + ' joint(s)';
          this.multiplepreviews = [
            ...this.multiplepreviews,
            ...this.defaultPath,
          ];
        }
      }
    }
  }
  openFileInput() {
    if (this._input) {
      this._input.click();
    }
  }

  ngAfterViewInit(): void {
    this._input = this._ref.nativeElement.querySelector('input[type="file"]');

    if (this._input) {
      let timestamp = Date.now();
      this.inputId = `${this.name}-${timestamp}`;
      this._input.addEventListener('click', (event) => {
        event.stopPropagation();
      });
    }
  }

  remove() {
    this.isSelected = false;
    this.file = undefined;
    this.files = [];
    this.selectedURL = '';
    this.defaultPath = [];
    this.multiplepreviews = [];
    this.changeEvent.emit(this.file);
    this.placeholder = 'Choisissez un fichier';
  }
  openModal(src?: string) {
    if (src) {
      if (this.type == 'img') {
        this.selectedURL = src;
        $(`#${this.inputId}`).modal('show');
      } else {
        window.open(src, '_blank');
      }
      return;
    }
    if (this.selectedURL.length) {
      if (this.type == 'img') {
        $(`#${this.inputId}`).modal('show');
      } else {
        window.open(this.selectedURL, '_blank');
      }
    }
  }

  extractFileNameFromPath(path: string): string | null {
    const parts = path.split('/');
    return parts.length > 0 ? parts[parts.length - 1] : null;
  }
}
