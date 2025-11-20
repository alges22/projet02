<?php

namespace App\Services\Mail;

use Illuminate\Notifications\Messages\MailMessage;

class Messager extends MailMessage
{

    public $view = "mail.global";
    /**
     * Message d'entête
     *
     * @var string|null
     */
    public $headline = null;

    /**
     * Action
     *
     * @var array
     */
    public $action = ['link' => null, 'text' => null];

    /**
     *
     * @var string[]
     */
    public $lastlines = [];

    /**
     * String
     *
     * @var string|null
     */
    public $footer = null;

    /**
     * String
     *
     * @var string|null
     */
    public $goodbye = null;

    /**
     * L'icone image
     *
     * @var string|null
     */
    public $heroIcon = null;
    /**
     * Salutation
     *
     * @param string|null $greeting
     * @return string|null|$this
     */
    public function greeting($greeting = null)
    {
        if ($greeting) {
            $this->greeting = $greeting;
            return $this;
        } else {
            return $this->greeting;
        }
    }
    /**
     * Le titre du message optionnel
     *
     * @param string|null $headline
     * @return string|null|$this
     */
    public function headline($headline = null)
    {
        if ($headline) {
            $this->headline = $headline;
            return $this;
        } else {
            return $this->headline;
        }
    }
    /**
     * Définir ou obtenir les lignes d'introduction
     *
     * @param string|null $introLines
     * @return string|null|$this
     */
    public function introlines($introLines  = null)
    {
        if ($introLines) {
            $this->introLines[] = $introLines;
            return $this;
        } else {
            return implode('<br>', $this->introLines);
        }
    }

    /**
     * Définir ou obtenir le paragraphe d'introduction
     *
     * @param string|null $introLines
     * @return string|null|$this
     */
    public function introParagraph($introLines  = null)
    {
        if ($introLines) {
            $this->introLines[] = $introLines;
            return $this;
        } else {
            return implode('<br>', $this->introLines);
        }
    }

    /**
     * Définir l'action
     *
     * @param string $text
     * @param string $link
     * @return $this
     */
    public function setAction(string $text, $link)
    {
        $this->action['text'] = $text;
        $this->action['link'] = $link;
        return $this;
    }
    /**
     * Obtenir l'action du message ou certaines valeurs
     * @param string|null $key
     * @return array
     */
    public function getAction($key = null)
    {
        if ($key) {
            return $this->action[$key] ?? null;
        }
        return $this->action;
    }
    /**
     * Définir ou obtenir les dernières lignes
     *
     * @param string|null $lastlines
     * @return string|null|$this
     */
    public function lastlines($lastlines = null)
    {
        if (!is_null($lastlines)) {
            $this->lastlines[] = $lastlines;
            return $this;
        } else {
            return implode('<br>', $this->lastlines);
        }
    }

    /**
     * Définir ou obtenir le paragraphe final
     *
     * @param string|null $lastlines
     * @return string|null|$this
     */
    public function lastParagraph($lastlines = null)
    {
        if (!is_null($lastlines)) {
            $this->lastlines[] = $lastlines;
            return $this;
        } else {
            return implode('<br>', $this->lastlines);
        }
    }
    /**
     * Définir ou obtenir le pied de page
     *
     * @param string|null|bool $footer
     * @return string|null|$this|bool
     */
    public function footer($footer = true)
    {
        if ($footer) {
            $this->footer = $footer;
            return $this;
        } else {
            return $this->footer;
        }
    }
    /**
     * Définir ou obtenir le message de conclusion
     *
     * @param string|null $goodbye
     * @return string|null|$this
     */
    public function goodbye($goodbye = null)
    {
        if ($goodbye) {
            $this->goodbye = $goodbye;
            return $this;
        } else {
            return $this->goodbye;
        }
    }
    /**
     * Définir ou obtenir l'icône principale
     *
     * @param string|null $heroIcon
     * @return string|null|$this
     */
    public function heroIcon($heroIcon = null)
    {
        if ($heroIcon) {
            $this->heroIcon = $heroIcon;
            return $this;
        } else {
            return $this->heroIcon;
        }
    }
    /**
     * Définir ou obtenir l'objet du message
     *
     * @param string $subject
     * @return $this|string
     */
    public function subject($subject = null)
    {
        if ($subject) {
            $this->subject = $subject;
            return $this;
        } else {
            return $this->subject;
        }
    }
    /**
     * Vérifier si le mailer contient une action
     *
     * @return boolean
     */
    public function hasAction()
    {
        return isset($this->action['text']) && isset($this->action['link']);
    }

    public function build()
    {
        $mailer = clone $this;
        return $this->view('mail.global', [
            'mailer' => $mailer
        ]);
    }

    public function __clone()
    {
        $this->view = null;
    }
    /**
     * @return mixed
     */
    public function render()
    {
        return $this;
    }
}
