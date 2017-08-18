import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'
import { User } from '../../providers/auth/auth';
import { Card } from '../../providers/card/card';

export class Message {
  id: number;
  user: User;
  card: Card;
  text: string;
  timestamp: any;
  
  constructor (id: number, user: any, card: any, text: string, timestamp: any) {
    this.id = id;
    this.user = user ? new User(user.id, user.fir_name, user.las_name, user.email, user.photo_url) : null;
    this.card = card ? new Card(card) : null;
    this.text = text;
    this.timestamp = timestamp;
    // this.user = user;
  }
}

export class Conversation {
  id: number;
  other: User;
  card: Card;
  messages: Message[];
  timestamp: any;
  unread: boolean;
  
  constructor (id: number, other: any, card: any, messages: any[], timestamp: any, unread: any) {
    this.id = id;
    this.other = other ? new User(other.id, other.fir_name, other.las_name, other.email, other.photo_url) : null;
    this.card = card ? new Card(card) : null;
    this.timestamp = timestamp;
    this.messages = messages.map((msg) => new Message(msg.id, msg.user, msg.card, msg.text, msg.timestamp));
    this.unread = unread || false;
  }
}

@Injectable()
export class MessagesProvider {
  // items: Array<{industry: string, pitch: string, distance: string}>;
  // notifications: any[] = []; 
  unread_ids: number[] = [];

  constructor (private http: Http) {}

  getConversations(user_id, other_id?, card_id?, offset?, limit?): Observable<any> {
    let query = '?user_id=' + user_id;
    query += (other_id ? '&other_id='+other_id : '') + (card_id ? '&card_id='+card_id : '');
    query += (offset ? '&offset='+offset : '') + (limit ? '&limit='+limit : '');
    let url = globs.BASE_API_URL + 'get_conversations.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            return data.map((convo) => {
              return new Conversation(convo.conversation_id, convo.other, convo.card, convo.messages, convo.timestamp, convo.unread);
            });
          })
          .catch(this.handleError);
  }

  getMessages(convo_id, offset, limit): Observable<any> {
    let query = '?convo_id=' + convo_id + '&offset=' + offset + '&limit=' + limit;
    let url = globs.BASE_API_URL + 'get_messages.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            return data.map((msg) => {
              return new Message(msg.id, msg.user, msg.card, msg.text, msg.timestamp);
            });
          })
          .catch(this.handleError);
  }

  getNewMessages(convo_id, other_id, last_id?): Observable<any> {
    let query = '?convo_id=' + convo_id + '&other_id=' + other_id + (last_id ? '&last_id=' + last_id : '');
    let url = globs.BASE_API_URL + 'get_new_messages.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            return data.map((msg) => {
              return new Message(msg.id, msg.user, msg.card, msg.text, msg.timestamp);
            });
          })
          .catch(this.handleError);
  }

  post(data): Observable<any> {
    let url = globs.BASE_API_URL + 'post_message.php';
    return this.http.post(url, data)
            .map(this.extractData)
            .map((data) => {
              console.log(data);
              return data;
            })
            .catch(this.handleError);
  }

  getUnreadCount(user_id): Observable<any> {
    let query = '?user_id=' + user_id;
    let url = globs.BASE_API_URL + 'get_message_receipts.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            this.unread_ids = data;
            // this.unreadCount = data.length;
            return this.unread_ids;
          })
          .catch(this.handleError);
  }

  markAsRead(user_id, conversation_id): Observable<any> {
    let data = {
      user_id: user_id,
      conversation_id: conversation_id
    };
    let url = globs.BASE_API_URL + 'delete_message_receipts.php';
    return this.http.post(url, data)
            .map(this.extractData)
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
