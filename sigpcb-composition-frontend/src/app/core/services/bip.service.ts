import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root',
})
export class BipService {
  private bip = new Audio('assets/audio/bip.aac');
  private bipend = new Audio('assets/audio/bipend.aac');
  private intervalId: any;

  constructor() {}

  start(
    count: number,
    call: CallableFunction,
    end: CallableFunction,
    fails: (error: any) => void
  ) {
    this.playBip()
      .then(() => {
        this.intervalId = setInterval(() => {
          if (count > 0) {
            this.playBip().then(() => {
              if (typeof call === 'function') {
                call();
              }
              --count;
            });
          }
          if (count <= 0) {
            this.bipend.play();
            if (typeof end === 'function') {
              end();
            }
            this.stop();
          }
        }, 1000);
      })
      .catch((error) => {
        fails(error);
      });
  }

  stop() {
    clearInterval(this.intervalId);
  }

  private playBip(): Promise<void> {
    return new Promise((resolve, reject) => {
      this.bip
        .play()
        .then(() => {
          this.bip.onended = () => {
            resolve(); // Résoudre la promesse lorsque l'audio se termine
          };
        })
        .catch((error) => {
          reject(error);
        });
    });
  }

  prepare() {
    this.bip.load();
    this.bipend.load();
  }
}
