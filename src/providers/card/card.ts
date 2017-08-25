import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'
import { Comment } from '../../providers/wall/wall';

export class Card {
  id: number;
  founder_id: number;
  pitch: string;
  details: string;
  industry: string;
  who: string;
  challenge_ids: number[];
  challenge_names: string[];
  challenge_details: string;
  stage: number;
  distance: number;
  created_at: string;
  updated_at: string;
  followers: number[];
  following: boolean;
  comments: any[];
  topComment?: Comment;

  constructor (item) {
    this.id = item.id;
    this.founder_id = item.founder_id;
    this.pitch = item.pitch;
    this.details = item.details || '';
    this.industry = item.industry || globs.INDUSTRIES[(Math.round(Math.random()*(globs.INDUSTRIES.length-1)))];
    this.who = item.who || 'Anonymous Entrepreneur';
    this.challenge_ids = item.challenge_ids || [];
    this.challenge_names = item.challenge_ids ? globs.SKILLS
                        .filter(item => this.challenge_ids.indexOf(item.id) >= 0)
                        .map(item => item.name) : ['General Advice'];
    this.challenge_details = item.challenge_details || '';
    this.stage = item.stage;
    this.distance = item.distance || (Math.round(Math.random()*50));
    this.followers = item.followers || [];
    this.following = item.following || null;
    this.comments = item.comments;
    this.topComment = item.topComment && item.topComment.id ? new Comment(item.topComment) : null;
  }
}

@Injectable()
export class CardProvider {
  private base_url = globs.BASE_API_URL;
  items: Array<{industry: string, pitch: string, distance: string}>;

  constructor (private http: Http) {}

  getDeck(user_id, offset): Observable<any[]> {
    let query = '?user_id=' + user_id + '&offset=' + offset;
    let url = this.base_url + 'get_cards.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            let manip = data.map((item, i) => {
              return new Card(item);
             });
            return manip;
          })
          .catch(this.handleError);
  }

  getFounded(user_id, offset): Observable<any[]> {
    let query = '?user_id=' + user_id + '&offset=' + offset;
    let url = this.base_url + 'get_founded.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            let manip = data.map((item, i) => {
              return new Card(item);
             });
            return manip;
          })
          .catch(this.handleError);
  }

  follow(data): Observable<any> {
    console.log('following trigger', data);
    let url = globs.BASE_API_URL + 'bookmark.php';
    return this.http.post(url, data)
            .map(this.extractData)
            .catch(this.handleError);
  }

  post(data): Observable<any> {
    let url = globs.BASE_API_URL + 'post_card.php';
    return this.http.post(url, data)
            .map(this.extractData)
            .map((item) => {
              return new Card(item);
            })
            .catch(this.handleError)
  }

  delete(card_id): Observable<any> {
    let url = globs.BASE_API_URL + 'delete_card.php';
    return this.http.post(url, {id: card_id})
            .map(this.extractData)
            .catch(this.handleError)
  }


  private extractData(res: Response) {
    let body = res.json();
    return body.data || body || { };
  }

  private handleError (error: Response | any) {
    let errMsg: string;
    if (error instanceof Response) {
      const body = error.json() || '';
      const err = body.error || JSON.stringify(body);
      errMsg = `${error.status} - ${error.statusText || ''} ${err}`;
    } else {
      errMsg = error.message ? error.message : error.toString();
    }
    console.error(error, errMsg);
    return Observable.throw(errMsg);
  }
}
