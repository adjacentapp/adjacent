import { Injectable }              from '@angular/core';
import { Http, Response }          from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'
import { User } from '../../providers/auth/auth';
import { Card } from '../../providers/card/card';

export class Profile {
  user: User;
  skills: string;
  bio: string;
  cards: Card[];

  constructor(user: User, skills: string, bio: string, cards: any[]) {
    this.user = user;
    this.skills = skills;
    this.bio = bio;
    this.cards = cards;
  }
}

@Injectable()
export class ProfileProvider {
  // items: Array<{pitch: string, distance: string}>;

  constructor (private http: Http) {}

  getProfile(user_id, my_id): Observable<any> {
    let query = '?user_id=' + user_id + '&my_id=' + my_id;
    let url = globs.BASE_API_URL + 'get_profile.php' + query;

    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            // console.log(data);
            let user = new User(data.user_id, data.fir_name, data.las_name, data.email, data.photo_url);
            let cards = data.cards.map((card) => {
              return new Card(card.id, card.founder_id, card.pitch, card.industry, card.background, card.challenge, card.challenge_detail, card.stage, card.distance, card.comments, card.following, card.followers, card.anonymous);
            });
            return new Profile(user, data.skills, data.bio, cards);
          })
          .catch(this.handleError);
  }

  updateProfile (data): Observable<any> {
    let url = globs.BASE_API_URL + 'post_profile.php';
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
