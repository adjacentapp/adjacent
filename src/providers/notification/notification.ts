import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'
import { Card } from '../../providers/card/card';
import { User } from '../../providers/auth/auth';

export class Notification {
  id: number;
  title: string;
  message: string;
  card: Card;
  user?: User;
  unread?: boolean;

  constructor(id: number, title: string, message: string, card: any, user: any, unread?: string) {
    this.id = id;
    this.title = title;
    this.message = message;
    this.card = new Card(card);
    this.user = user ? new User(user.id, user.fir_name, user.las_name, user.email, user.photo_url) : null;
    this.unread = unread==="1";
  }
}

@Injectable()
export class NotificationProvider {
  items: Notification[];
  unreadCount: number;

  constructor(public http: Http) {}

  getNotifications(user_id, offset, limit): Observable<any> {
    let query = '?user_id=' + user_id + '&offset=' + offset + '&limit=' + limit;
    let url = globs.BASE_API_URL + 'get_notifications.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            this.items = data.map((notif) => new Notification(notif.id, notif.title, notif.message, notif.card, notif.user, notif.new));            
            this.unreadCount = this.items.filter(item => item.unread).length;
            return this.items;
          })
          .catch(this.handleError);
  }

  getNewNotifications(user_id): Observable<any> {
    let last_id = this.items.length ? this.items[0].id : null;
    let query = '?user_id=' + user_id + (last_id ? '&last_id=' + last_id : '');
    let url = globs.BASE_API_URL + 'get_notifications.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            this.unreadCount += data.length;
            let newNotifs = data.map((notif) => new Notification(notif.id, notif.title, notif.message, notif.card, notif.user, notif.new));
            this.items = newNotifs.concat(this.items);
            return newNotifs;
          })
          .catch(this.handleError);
  }

  markAsRead(user_id, card_id): Observable<any> {
    let data = {
      user_id: user_id,
      card_id: card_id
    };
    let url = globs.BASE_API_URL + 'delete_receipts.php';
    return this.http.post(url, data)
            .map(this.extractData)
            .catch(this.handleError);
  }

  markAllAsRead(user_id): Observable<any> {
    let data = {user_id: user_id};
    console.log(data);
    let url = globs.BASE_API_URL + 'delete_receipts.php';
    return this.http.post(url, data)
            .map(this.extractData)
            .map((data) => {
              console.log(data);
              return data;
            })
            .catch(this.handleError);
  }
  
  private extractData(res: Response) {
    let body = res.json();
    return body.data || body || { };
  }

  private handleError (error: Response | any) {
    // In a real world app, you might use a remote logging infrastructure
    let errMsg: string;
    if (error instanceof Response) {
      const body = error.json() || '';
      const err = body.error || JSON.stringify(body);
      errMsg = `${error.status} - ${error.statusText || ''} ${err}`;
    } else {
      errMsg = error.message ? error.message : error.toString();
    }
    console.error(errMsg);
    return Observable.throw(errMsg);
  }  

}
