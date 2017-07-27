import { Injectable }              from '@angular/core';
import { Http, Response }          from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'
import { AuthProvider } from '../../providers/auth/auth';

@Injectable()
export class ProfileProvider {
  items: Array<{pitch: string, distance: string}>;
  
  constructor (private http: Http, private auth:AuthProvider) {}
  
  getProfile(user_id, myself): Observable<any> {
    let query = '?user_id=' + user_id;
    query += myself ? '&myself=true' : ''
    let url = globs.BASE_API_URL + 'get_profile.php' + query;

    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            let profile = data;
            // let skills = data.skills.replace(/[.,'"!-]/gi,'').split(' ');
            // let skill = '';
            // for (let s of skills)
              // skill += ' #' + s;
            // profile.skills = skill;
             
             // for (let card of profile.cards)
             
            return profile;
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
