import { Injectable } from '@angular/core';
import { Http, Response, Headers, RequestOptions } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as globs from '../app/globals'

@Injectable()
export class CardService {
  private base_url = globs.BASE_API_URL;
  items: Array<{industry: string, pitch: string, distance: string}>;
  
  constructor (private http: Http) {}
  
  getDeck(): Observable<any[]> {
    let query = '?user_id=1'
    let url = this.base_url + 'get_cards.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            let manip = data.map((item, i) => {
              let thing = item;
              thing.distance = (Math.round(Math.random()*50)) + ' mi.';
              thing.industry = i;
              return thing;
             });
            console.log(manip);
            return manip;
            // return [{}, ...manip];
          })
          .catch(this.handleError);
  }

  follow(data): Observable<any> {
    // let headers = new Headers({ 'Content-Type': 'application/json' });
    let headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded'});
    let options = new RequestOptions({ headers: headers });
    let url = globs.BASE_API_URL + 'bookmark.php';
    return this.http.post(url, data, options)
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
    console.error(error, errMsg);
    return Observable.throw(errMsg);
  }
}
