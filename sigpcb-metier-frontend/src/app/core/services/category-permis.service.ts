import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable, of } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { CategoryPermis } from '../interfaces/catgory-permis';

@Injectable({
  providedIn: 'root',
})
export class CategoryPermisService {
  permis = [
    {
      id: 1,
      name: 'A',
      status: true,
      validite: 10,
      age_min: 18,
      is_valid_age: true,
      montant: 50,
      montant_militaire: 25,
      montant_etranger: 100,
      note_min: 70,
      description: 'Permis de conduire moto de toutes les cylindrées.',
    },
    {
      id: 2,
      name: 'A1',
      status: true,
      validite: 10,
      age_min: 16,
      is_valid_age: true,
      montant: 30,
      montant_militaire: 15,
      montant_etranger: 70,
      note_min: 60,
      description: "Permis de conduire moto jusqu'à 125 cm³.",
    },
    {
      id: 3,
      name: 'A2',
      status: true,
      validite: 10,
      age_min: 18,
      is_valid_age: true,
      montant: 40,
      montant_militaire: 20,
      montant_etranger: 80,
      note_min: 65,
      description: "Permis de conduire moto jusqu'à 35 kW.",
    },
    {
      id: 4,
      name: 'A3',
      status: true,
      validite: 10,
      age_min: 21,
      is_valid_age: true,
      montant: 60,
      montant_militaire: 30,
      montant_etranger: 120,
      note_min: 75,
      description:
        'Permis de conduire moto de toutes les cylindrées avec une puissance maximale spécifiée.',
    },
    {
      id: 5,
      name: 'B',
      status: true,
      validite: 15,
      age_min: 18,
      is_valid_age: true,
      montant: 40,
      montant_militaire: 20,
      montant_etranger: 80,
      note_min: 70,
      description: 'Permis de conduire voiture.',
    },
    {
      id: 6,
      name: 'B1',
      status: true,
      validite: 15,
      age_min: 16,
      is_valid_age: true,
      montant: 30,
      montant_militaire: 15,
      montant_etranger: 70,
      note_min: 60,
      description: 'Permis de conduire quadricycle léger à moteur.',
    },
    {
      id: 7,
      name: 'C',
      status: true,
      validite: 10,
      age_min: 21,
      is_valid_age: true,
      montant: 80,
      montant_militaire: 40,
      montant_etranger: 160,
      note_min: 75,
      description:
        'Permis de conduire véhicules de transport de marchandises de plus de 3,5 tonnes.',
    },
    {
      id: 8,
      name: 'C1',
      status: true,
      validite: 10,
      age_min: 18,
      is_valid_age: true,
      montant: 60,
      montant_militaire: 30,
      montant_etranger: 120,
      note_min: 70,
      description:
        'Permis de conduire véhicules de transport de marchandises de moins de 3,5 tonnes.',
    },
    {
      id: 9,
      name: 'D',
      status: true,
      validite: 10,
      age_min: 21,
      is_valid_age: true,
      montant: 100,
      montant_militaire: 50,
      montant_etranger: 200,
      note_min: 75,
      description:
        'Permis de conduire véhicules de transport de personnes (autobus).',
    },
    {
      id: 10,
      name: 'D1',
      status: true,
      validite: 10,
      age_min: 18,
      is_valid_age: true,
      montant: 80,
      montant_militaire: 40,
      montant_etranger: 160,
      note_min: 70,
      description:
        'Permis de conduire véhicules de transport de personnes (minibus).',
    },
    {
      id: 11,
      name: 'E',
      status: true,
      validite: 10,
      age_min: 21,
      is_valid_age: true,
      montant: 120,
      montant_militaire: 60,
      montant_etranger: 240,
      note_min: 75,
      description:
        'Permis de conduire véhicules de transport de personnes et de remorques.',
    },
    {
      id: 12,
      name: 'F',
      status: true,
      validite: 5,
      age_min: 18,
      is_valid_age: true,
      montant: 20,
      montant_militaire: 10,
      montant_etranger: 40,
      note_min: 50,
      description: 'Permis de conduire tracteur agricole.',
    },
  ];
  constructor(private http: HttpClient) {}

  all(): Observable<ServerResponseType> {
    const url = apiUrl('/categorie-permis-base', 'recrutement-examinateur');

    return this.http.get<ServerResponseType>(url);
    // return of({
    //   status: true,
    //   data: this.permis,
    // });
  }
}
