import { Injectable } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Card } from '../../providers/card/card';
import { User } from '../../providers/auth/auth';

export class Notification {
  title: string;
  message: string;
  card: Card;
  user?: User;

  constructor(title: string, message: string, card: any, user: any) {
    this.title = title;
    this.message = message;
    // this.card = card
    this.card = new Card(48, 1, "a service that will retrofit your dumb-car to be a self-driving car.", "Art", "Bg", "Chall", null, 0, 2, [], null, [], null);
    if(user)
      this.user = new User(1, 'Sal', 'Saia', "salulos@gmail.com", "https://graph.facebook.com/10154226234631397/picture?type=large");
  }
}

@Injectable()
export class NotificationProvider {

  constructor(public http: Http) {
    console.log('Hello NotificationProvider Provider');
  }

}
