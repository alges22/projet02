import {
  Component,
  EventEmitter,
  Input,
  OnChanges,
  OnInit,
  Output,
  SimpleChanges,
} from '@angular/core';
import { BipService } from '../../services/bip.service';

@Component({
  selector: 'app-chrono',
  templateUrl: './chrono.component.html',
  styleUrls: ['./chrono.component.scss'],
})
export class ChronoComponent implements OnInit, OnChanges {
  @Input() time = 10;
  @Output() ended = new EventEmitter();
  timer: any;
  started = false;
  @Input() square = 180;
  @Input() border = 7;
  @Input('border-color') borderColor = 'var(--bs-primary)';
  @Input() bg = 'var(--bs-white)';
  @Input('start') startChrono = false;
  previousTime = 0;
  @Output() onErrors = new EventEmitter<any>();
  constructor(private chronoService: BipService) {}

  ngOnInit(): void {
    this.start();
  }
  ngOnChanges(changes: SimpleChanges): void {
    if (!this.started) {
      if (this.previousTime > this.time) {
        this.time = this.previousTime;
      }
      if (changes['time']) {
        const c = changes['time'].currentValue;
        this.time = c;
      }
      this.start();
    }
  }
  stopTimer() {
    clearInterval(this.timer);
  }

  start() {
    if (this.startChrono && !this.started && this.time > 0) {
      const otime = this.time;
      this.previousTime = otime;
      this.started = true;
      this.chronoService.start(
        otime,
        () => {
          this.time--;
        },
        () => {
          this.ended.emit();
          this.started = false;
        },
        (error: any) => {
          if (error.name === 'NotAllowedError') {
            this.onErrors.emit(error);
          }
        }
      );
    }
  }

  ngOnDestroy(): void {
    this.stopTimer();
    this.chronoService.stop();
  }
}
