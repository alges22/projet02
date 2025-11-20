import { Chapitre } from "./chapitre";
import { Langue } from "./langue";

export interface Question {
  id?: number;
  name: string;
  langue_id?: number;
  langue?: Langue;
  chapitre_id?: number;
  chapitre?: Chapitre;
  audio: string;
  illustration: string;
  text: string;
  time: string;
  reponses: any;
  code_illustration:string;
}
