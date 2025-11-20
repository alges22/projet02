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
  template: `<video
    #qrScanVideo
    class="border border-3 mx-auto qr-camera"
  ></video>`,
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

    this.startScanner();
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
      if (this.stop) {
        this.stopScanner();
      } else {
        this.startScanner();
      }
    }
  }

  ngOnDestroy(): void {
    this.stopScanner();
  }

  private startScanner(): void {
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

  private stopScanner(): void {
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
