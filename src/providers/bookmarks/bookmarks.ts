import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'

@Injectable()
export class BookmarksProvider {
  private base_url = globs.BASE_API_URL;
  items: Array<{industry: string, pitch: string, distance: string}>;
  
  constructor (private http: Http) {}
  
  getBookmarks(user_id, offset, filters): Observable<any[]> {
    let query = '?user_id=' + user_id + '&offset=' + offset;
    // query += filters
    let url = this.base_url + 'get_my_bookmarks.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            let manip = data.map((item) => {
              let thing = item;
              thing.distance = (Math.round(Math.random()*50)) + ' mi.';
              thing.industry = thing.industry_string || globs.INDUSTRIES[item.id % globs.INDUSTRIES.length];
              thing.who = thing.background || "Anonymous Entrepreneur";
              thing.challenge = thing.challenge || "N/A";
              thing.challenge_detail = thing.challenge_detail || null;
              thing.stage = (Math.round(Math.random()*5));
              return thing;
             });
            console.log(manip);
            return manip;
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
