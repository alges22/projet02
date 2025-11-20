import {
  Component,
  ElementRef,
  EventEmitter,
  Input,
  OnChanges,
  OnDestroy,
  Output,
  SimpleChanges,
  ViewChild,
} from '@angular/core';
import QrScanner from 'qr-scanner';
@Component({
  selector: 'app-scanner',
  templateUrl: './scanner.component.html',
  styleUrls: ['./scanner.component.scss'],
})
export class ScannerComponent implements OnDestroy, OnChanges {
  @ViewChild('qrScanVideo', { static: false })
  qrScanVideo!: ElementRef<HTMLVideoElement>;
  qrScanner: QrScanner | null = null;
  qrScanElement: HTMLVideoElement | null = null;

  @Output('scanned') scannedEvent = new EventEmitter<string>();
  @Input() stop = false;
  emited = false;
  ngAfterViewInit() {
    this.whenElementReady();

    if (this.qrScanElement) {
      this.qrScanner = new QrScanner(
        this.qrScanElement,
        this.handler.bind(this),
        {
          highlightScanRegion: true,
        }
      );

      this.qrScanner.start();
    }
  }

  private handler(result: any) {
    const code = result.data;
    if (!this.emited) {
      if (typeof code === 'string' && code.length === 12) {
        this.emited = true;
        this.scannedEvent.emit(code);
      }
    }
  }
  ngOnChanges(changes: SimpleChanges): void {
    if (changes['stop']) {
      this.stop = changes['stop'].currentValue;
      if (this.qrScanner) {
        if (this.stop) {
          this.qrScanner.stop();
        } else {
          this.qrScanner.start();
        }
      }
    }
  }

  ngOnDestroy(): void {
    if (this.qrScanner) {
      this.qrScanner.stop();
      this.qrScanner.destroy();
      this.qrScanner = null;
    }
  }
  private whenElementReady() {
    if (this.qrScanVideo) {
      this.qrScanElement = this.qrScanVideo.nativeElement;
    }
  }
}
