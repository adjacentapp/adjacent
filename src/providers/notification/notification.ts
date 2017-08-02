import { Injectable } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Card } from '../../providers/card/card';

export class Notification {
  title: string;
  message: string;
  card: Card;


  constructor(title: string, message: string) { //, card: Card) {
    this.title = title;
    this.message = message;
    // this.card = card
    this.card = new Card(48, 1, "a service that will retrofit your dumb-car to be a self-driving car.", "Random", "Bg", "Chall", null, 0, 2, [], null, null);
  }
}

@Injectable()
export class NotificationProvider {

  constructor(public http: Http) {
    console.log('Hello NotificationProvider Provider');
  }

}
