import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import {
  CurrentQuestion,
  ServerResponseType,
} from '../types/server-response.type';
import { BehaviorSubject, Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { Question, QuestionResult } from '../interfaces/question';

@Injectable({
  providedIn: 'root',
})
export class QuestionService {
  private answersSubjects = new BehaviorSubject<
    { questionId: number; responses: number[] }[]
  >([]);

  private answers$ = this.answersSubjects.asObservable();
  private answers: { questionId: number; responses: number[] }[] = [];
  constructor(private http: HttpClient) {}

  get(): Observable<
    ServerResponseType<{
      questions: Question[];
      last_reponse:
        | null
        | undefined
        | { question_id: number; answers: number[] };
      toCompose: number | null;
    }>
  > {
    let url = apiUrl('/questions');
    return this.http.get<ServerResponseType>(url);
  }

  setAnswers(question: number, answers: number[]) {
    this.answers = this.answers.map((ans) => {
      if (ans.questionId === question) {
        ans.responses = answers;
      }
      return ans;
    });

    const founded = this.answers.find((ans) => ans.questionId === question);
    //Ajoute la réponse
    if (!founded) {
      this.answers.push({
        questionId: question,
        responses: answers,
      });
    } else {
      // Au cas ou la donnée serait modifiée on prend celle courante
      answers = founded.responses;
    }
    //Stocke temporaiement sur le service
    this.answersSubjects.next(this.answers);
  }

  onAnswersd() {
    return this.answers$;
  }
  findOne(question: number) {
    return this.answers.find((ans) => ans.questionId === question) || null;
  }

  questionAnswers(): Observable<ServerResponseType<QuestionResult>> {
    let url = apiUrl('/questions-answers');
    return this.http.get<ServerResponseType<QuestionResult>>(url);
  }

  syncToServer(question: number, answers: number[]) {
    let url = apiUrl('/questions');
    return this.http.post<ServerResponseType>(url, {
      question_id: question,
      answers: answers,
    });
  }

  startCompo() {
    let url = apiUrl('/start-compo');
    return this.http.get<
      ServerResponseType<{
        toCompose: number;
        time: number;
        ready: boolean;
      }>
    >(url);
  }

  thanks() {
    let url = apiUrl('/thanks');
    return this.http.get<
      ServerResponseType<
        | {
            correct_count: number;
            success: boolean;
            count: number;
          }
        | 'no-result'
      >
    >(url);
  }

  getCurrent(): Observable<ServerResponseType<CurrentQuestion>> {
    let url = apiUrl('/questions/current');
    return this.http.get<ServerResponseType>(url);
  }
}
