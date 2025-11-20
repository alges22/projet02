import { Reponse } from './reponse';

export interface Question {
  id: number;
  question: string;
  isMultiple: boolean;
  audio: string;
  responses: Reponse[];
  illustration: string;
  code_illustration: string;
  time: number;
  texte: string | null;
  type_illustration: string;
}

export type QR = {
  questionId: number;
  responses: number[];
  answers: number[];
  is_correct: boolean;
};

export interface QuestionResult {
  answers: QR[];
  correct_count: number;
  success: boolean;
  count: number;
  responses: Reponse[];
}
