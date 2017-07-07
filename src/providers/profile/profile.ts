import { Injectable }              from '@angular/core';
import { Http, Response }          from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'

@Injectable()
export class ProfileProvider {
  items: Array<{pitch: string, distance: string}>;
  
  constructor (private http: Http) {}
  
  getProfile(user_id): Observable<any> {
    let query = '?user_id=' + user_id
    let url = globs.BASE_API_URL + 'get_profile.php' + query;

    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            let profile = data;
            console.log(data);
            let skills = data.skills.replace(/[.,'"!-]/gi,'').split(' ');
            let skill = '';
            for (let s of skills)
              skill += ' #' + s;
            profile.skills = skill;
            return profile;
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
