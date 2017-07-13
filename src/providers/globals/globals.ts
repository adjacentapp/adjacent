import { Injectable }              from '@angular/core';
import { Http, Response }          from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'

@Injectable()
export class GlobalsProvider {

  constructor(public http: Http) {}

  getGlobs(): Observable<any> {
  // getGlobs() {
  	let url = globs.BASE_API_URL + 'get_skill_list.php';
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
          	globs.setSkills(data);
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
