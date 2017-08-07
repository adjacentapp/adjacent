import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import { Card } from '../../providers/card/card';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'

@Injectable()
export class BookmarksProvider {
  items: Array<{industry: string, pitch: string, distance: string}>;
  
  constructor (private http: Http) {}
  
  getBookmarks(user_id, offset, filters): Observable<any[]> {
    let query = '?user_id=' + user_id + '&offset=' + offset;
    // query += filters
    let url = globs.BASE_API_URL + 'get_my_bookmarks.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            return data.map((card) => {
              return new Card(card.id, card.founder_id, card.pitch, card.industry, card.background, card.challenge, card.challenge_detail, card.stage, card.distance, card.comments, card.following, card.followers, card.anonymous);
             });
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
    console.error(error, errMsg);
    return Observable.throw(errMsg);
  }
}
