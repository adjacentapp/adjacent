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
  industry: string;
  who: string;
  challenge: string;
  challenge_detail: string;
  stage: number;
  distance: number;
  comments: any[];
  following: boolean;
  followers: number[];
  anonymous: boolean;
  topComment?: Comment;

  constructor(id: number, founder_id: number, pitch: string, industry: string, who: string, challenge: string, challenge_detail: string, stage: number, distance: number, comments: any[], following: boolean, followers: number[], anonymous: boolean, topComment?: any) {
    this.id = id;
    this.founder_id = founder_id;
    this.pitch = pitch;
    this.industry = industry || globs.INDUSTRIES[(Math.round(Math.random()*(globs.INDUSTRIES.length-1)))];
    this.who = who || 'Anonymous Entrepreneur';
    this.challenge = challenge || 'N/A';
    this.challenge_detail = challenge_detail || '';
    this.stage = stage;
    this.distance = distance || (Math.round(Math.random()*50));
    this.following = following || null;
    this.followers = followers || [];
    this.topComment = topComment && topComment.id ? new Comment(topComment) : null;
    this.comments = comments;
    this.anonymous = anonymous;
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

    let personas = ['Compuer science student', 'ex-VC', 'Calc TA and longtime gamer', 'Recent grad with thesis in urban planning'];
    let challenges = ['UX design', 'Social media branding/marketing', 'Getting in contact with our potential users', 'Getting in front of investors'];
    let pitches = ['A recipe recommendation website that pulls in user meal preferences', 'Webseminar to help people refine their business pitch and value prop', 'Make a game(s) through which as you play you learn linear algebra or calculus. Then maybe kids might actaully be interested(excited even?) in learning', 'A crowd sourced parking spot mobile app'];
    let industries = [4,6,3,4]

    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            let manip = data.map((item, i) => {
              return new Card(item.id, item.founder_id, item.pitch, item.industry_string, item.background, item.challenge, item.challenge_detail, item.stage, item.distance, item.comments, item.following, item.followers, item.anonymous, item.topComment);
             });
            console.log(manip);
            return manip;
          })
          .catch(this.handleError);
  }

  follow(data): Observable<any> {
    let url = globs.BASE_API_URL + 'bookmark.php';
    return this.http.post(url, data)
            .map(this.extractData)
            .map((data) => {
              // console.log(data);
              return data;
            })
            .catch(this.handleError);
  }

  post(data): Observable<any> {
    let url = globs.BASE_API_URL + 'post_card.php';
    return this.http.post(url, data)
            .map(this.extractData)
            .map((item) => {
              console.log(item);
              return new Card(item.id, item.founder_id, item.pitch, item.industry_string, item.background, item.challenge, item.challenge_detail, item.stage, 0, item.comments, false, item.followers, item.anonymous);
            })
            .catch(this.handleError)
  }

  delete(card_id): Observable<any> {
    let url = globs.BASE_API_URL + 'delete_card.php';
    return this.http.post(url, {id: card_id})
            .map(this.extractData)
            // .map((item) => {
            //   console.log(item);
            //   return new Card(item.id, item.founder_id, item.pitch, item.industry_string, item.background, item.challenge, item.challenge_detail, item.stage, 0, item.comments, false, item.followers, item.anonymous);
            // })
            .catch(this.handleError)
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
    console.error(error, errMsg);
    return Observable.throw(errMsg);
  }
}
