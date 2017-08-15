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
  
  getBookmarks(user_id, offset): Observable<any[]> {
    let query = '?user_id=' + user_id + '&offset=' + offset;
    let url = globs.BASE_API_URL + 'get_bookmarks.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            return data.map((item) => {
              return new Card(item);
             });
          })
          .catch(this.handleError);
  }

  getContributions(user_id, offset, limit): Observable<any[]> {
    let query = '?user_id=' + user_id + '&offset=' + offset + '&limit=' + limit;
    let url = globs.BASE_API_URL + 'get_contributions.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            return data.map((item) => {
              return new Card(item);
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
