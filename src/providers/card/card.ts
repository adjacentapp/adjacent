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
  network_id: any;
  network_name: string;
  is_announcement: boolean;

  constructor (item) {
    this.id = item.id;
    this.founder_id = item.founder_id;
    this.pitch = item.pitch;
    this.details = item.details || '';
    this.industry = item.industry || '';
    this.who = item.who || 'Anonymous Entrepreneur';
    this.challenge_ids = item.challenge_ids || [];
    this.challenge_names = item.challenge_ids && item.challenge_ids.length ? globs.SKILLS
                        .filter(item => this.challenge_ids.indexOf(item.id) >= 0)
                        .map(item => item.name) : ['General Advice'];
    this.challenge_details = item.challenge_details || '';
    this.stage = item.stage;
    this.distance = item.distance || (Math.round(Math.random()*50));
    this.followers = item.followers || [];
    this.following = item.following || null;
    this.comments = item.comments;
    this.topComment = item.topComment && item.topComment.id ? new Comment(item.topComment) : null;
    if(this.industry == 'Announcement'){
      this.who = null;
      this.challenge_names = [];
    }
    this.network_id = item.network_id || "0";
    this.network_name = item.network_name || null;
    this.is_announcement = item.active == "2";
  }
}

@Injectable()
export class CardProvider {
  private base_url = globs.BASE_API_URL;
  items: Array<{industry: string, pitch: string, distance: string}>;

  constructor (private http: Http) {}

  getDeck(user_id, offset, filters): Observable<any[]> {
    let query = '?user_id=' + user_id + '&offset=' + offset + '&get_quote=true';
    if(filters){
      query += filters.industry ? '&industry=' + filters.industry.replace(' ','_') : '';
      query += filters.network_ids ? '&network_ids=' + filters.network_ids.join(',') : '';
    }
    console.log(query);
    let url = this.base_url + 'get_cards.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            let removeQuote = data.filter((item) => {
              if(item.quote){
                if(!globs.firstSignIn)
                  globs.setIntroQuote(item)
                return false;
              }
              return true;
            });
            let manip = removeQuote.map((item, i) => {
              // item.industry = globs.INDUSTRIES[i];
              return new Card(item);
             });
            // console.log(manip);
            return manip;
          })
          .catch(this.handleError);
  }

  getCardById(card_id, user_id): Observable<any[]> {
    let query = '?card_id=' + card_id + '&user_id=' + user_id;
    let url = this.base_url + 'get_cards.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            return new Card(data[0]);
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

  share(data): Observable<any> {
    console.log('logging share', data);
    let url = globs.BASE_API_URL + 'post_share.php';
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
