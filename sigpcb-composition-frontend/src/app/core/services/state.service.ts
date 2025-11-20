import { Injectable } from '@angular/core';
import { AudioService } from './audio.service';
import { BipService } from './bip.service';
import { AlertService } from './alert.service';
import { PageService } from './page.service';

@Injectable({
  providedIn: 'root',
})
export class StateService {
  private unloadWarningListener: any = (event: BeforeUnloadEvent) => {};

  constructor(
    private audioService: AudioService,
    private bipService: BipService,
    public $alert: AlertService,
    private pageService: PageService
  ) {}

  ngOnit() {
    this.audioService.prepare();
    this.bipService.prepare();
  }
  // Méthode pour activer le mode plein écran
  enterFullScreen(): Promise<void> {
    this.setupUnloadWarning();
    if (!document.fullscreenElement) {
      return document.documentElement.requestFullscreen().catch((err) => {
        console.error(
          `Erreur lors de la tentative d'activation du mode plein écran: ${err.message}`
        );
      });
    }
    return Promise.resolve(); // Déjà en plein écran
  }

  // Méthode pour désactiver le mode plein écran
  exitFullScreen(): Promise<void> {
    if (document.fullscreenElement) {
      return document.exitFullscreen().catch((err) => {
        console.error(
          `Erreur lors de la tentative de sortie du mode plein écran: ${err.message}`
        );
      });
    }
    return Promise.resolve(); // Déjà pas en plein écran
  }
  setupUnloadWarning() {
    if (!window) {
      return;
    }
    const listener = (event: BeforeUnloadEvent) => {
      const message =
        'Vous serez automatiquement déconnecté si vous réactualisez la page.';
      event.returnValue = message; // Pour les navigateurs modernes
      return message; // Pour les navigateurs plus anciens
    };

    // Si un ancien écouteur existe, le débrancher
    if (this.unloadWarningListener) {
      window.removeEventListener('beforeunload', this.unloadWarningListener);
    }

    // Brancher le nouvel écouteur
    this.unloadWarningListener = listener;
    window.addEventListener('beforeunload', this.unloadWarningListener);
  }

  removeUnloadWarning() {
    try {
      if (this.unloadWarningListener) {
        window.removeEventListener('beforeunload', this.unloadWarningListener);
        this.unloadWarningListener = null;
      }
    } catch (error) {}
  }

  onNetwork(error: (err: any) => void, available: () => void) {
    // Vérifier l'état de la connexion au démarrage
    if (!navigator.onLine) {
      error('Connexion perdue');
    } else {
      available();
    }

    // Écouter les événements de connexion et de déconnexion
    window.addEventListener('offline', () => {
      error('Connexion perdue');
    });

    window.addEventListener('online', () => {
      available();
    });
  }
  changePage(page: string) {
    this.pageService.changePage(page);
  }
}
