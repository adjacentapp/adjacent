import { Injectable } from '@angular/core';
import { Http, Response, Headers, RequestOptions } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'

@Injectable()
export class CardProvider {
  private base_url = globs.BASE_API_URL;
  items: Array<{industry: string, pitch: string, distance: string}>;
  
  constructor (private http: Http) {}
  
  getDeck(user_id): Observable<any[]> {
    let query = '?user_id=' + user_id;
    let url = this.base_url + 'get_cards.php' + query;
    let personas = ['Compuer science student', 'ex-VC', 'Calc TA and longtime gamer', 'Recent grad with thesis in urban planning'];
    let challenges = ['UX design', 'Social media branding/marketing', 'Getting in contact with our potential users', 'Getting in front of investors'];
    let pitches = ['A recipe recommendation website that pulls in user meal preferences', 'Webseminar to help people refine their business pitch and value prop', 'Make a game(s) through which as you play you learn linear algebra or calculus. Then maybe kids might actaully be interested(excited even?) in learning', 'A crowd sourced parking spot mobile app'];
    let industries = [4,6,3,4]
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            let manip = data.map((item, i) => {
              let thing = item;
              thing.distance = (Math.round(Math.random()*50));
              if(i < 4){
                thing.industry = industries[i];
                thing.who = personas[i];
                thing.challenge = challenges[i];
                thing.pitch = pitches[i];
                thing.stage = i;
               } else {
                 thing.industry = (Math.round(Math.random()*18));
                 thing.who = "Anonymous Entrepreneur";
                 thing.challenge = "N/A";
                 thing.stage = (Math.round(Math.random()*5));
               }
               console.log(thing);
              return thing;
             });
            console.log(manip);
            return manip;
            // return [{}, ...manip];
          })
          .catch(this.handleError);
  }

  follow(data): Observable<any> {
    let url = globs.BASE_API_URL + 'bookmark.php';
    return this.http.post(url, data)
            .map(this.extractData)
            .map((data) => {
              console.log(data);
              return data;              
            })
            .catch(this.handleError);
  }

  post(data): Observable<any> {
    let url = globs.BASE_API_URL + 'post_card.php';
    return this.http.post(url, data)
            .map(this.extractData)
            .map((data) => {
              console.log(data);
              return data;
            })
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
