import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'
import { User } from '../../providers/auth/auth';

export class Message {
  id: number;
  user: User;
  text: string;
  timestamp: any;
  
  constructor (id: number, user: any, text: string, timestamp: any) {
    this.id = id;
    this.user = new User(user.id, user.fir_name, user.las_name, user.email, user.photo_url);
    this.text = text;
    this.timestamp = timestamp;
    // this.user = user;
  }
}

export class Conversation {
  id: number;
  other: User;
  messages: Message[];
  timestamp: any;
  
  constructor (id: number, other: any, messages: any[], timestamp: any) {
    this.id = id;
    this.other = new User(other.id, other.fir_name, other.las_name, other.email, other.photo_url);
    this.timestamp = timestamp;
    this.messages = messages.map((msg) => new Message(msg.id, msg.user, msg.text, msg.timestamp));
  }
}

@Injectable()
export class MessagesProvider {
  // items: Array<{industry: string, pitch: string, distance: string}>;

  constructor (private http: Http) {}

  getConversations(user_id): Observable<any> {
    let query = '?user_id=' + user_id;
    let url = globs.BASE_API_URL + 'get_conversations.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            return data.map((convo) => {
              return new Conversation(convo.conversation_id, convo.other, convo.messages, convo.timestamp);
            });
          })
          .catch(this.handleError);
  }

  getMessages(convo_id, offset): Observable<any> {
    let query = '?convo_id=' + convo_id + '&offset=' + offset;
    let url = globs.BASE_API_URL + 'get_messages.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            return data.map((msg) => {
              return new Message(msg.id, msg.user, msg.text, msg.timestamp);
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
