import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root',
})
export class AudioService {
  private audio = new Audio();
  isPlaying = false;
  constructor() {}

  start() {
    this.load();
  }
  play(src: string, catchError: (error: any) => void) {
    this.audio.src = src;
    this.load();
    return this.audio
      .play()
      .then(() => {
        this.isPlaying = true;
      })
      .catch((err) => {
        this.isPlaying = false;
        if (err.name == 'NotAllowedError') {
          return catchError(err);
        }
      });
  }

  stop() {
    this.audio.src = '';
    this.audio.muted = true;
  }

  remove() {
    this.audio.remove();
  }

  private load() {
    this.audio.load();
  }

  ended(call: CallableFunction) {
    this.audio.onended = () => {
      this.isPlaying = false;
      call();
    };
  }

  prepare() {
    this.load();
  }

  pause() {
    this.audio.pause();
  }

  resume() {
    this.audio.play();
  }
}
