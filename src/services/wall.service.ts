import { Injectable } from '@angular/core';
import { Http, Response, Headers, RequestOptions } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as globs from '../app/globals'

@Injectable()
export class WallService {
  private base_url = globs.BASE_API_URL;
  
  constructor (private http: Http) {}
  
  getWall (card_id): Observable<any[]> {
    let query = '?card_id=' + card_id;
    let url = this.base_url + 'get_card_wall.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            let manip = data.map((item, i) => {
              let thing = item;
              thing.score = 5 - i;
              return thing;
             });
            return manip;
          })
          .catch(this.handleError);
  }

  vote(item): Observable<any> {

    // $scope.likePost = function(post){
    //   if(!post.liked){
    //     cardFactory.likeWallPost(post.id, post.card_id, user.id, true);
    //     post.liked = true;
        
    //     post.likes.push(user.id);
    //   }
    //   else {
    //     cardFactory.likeWallPost(post.id, post.card_id, user.id, false);
    //     post.liked = false;

    //     var likes = [];
    //     angular.forEach(post.likes, function(like){
    //       if(like != user.id)
    //         likes.push(like);
    //     });
    //     post.likes = likes;
    //   }
    // };


    // let headers = new Headers({ 'Content-Type': 'application/json' });
    let headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded'});
    let options = new RequestOptions({ headers: headers });
    let url = globs.BASE_API_URL + 'like_wall_post.php';
    return this.http.post(url, item, options)
            .map(this.extractData)
            .catch(this.handleError);
  }


  private extractData(res: Response) {
    // console.log(res);
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
