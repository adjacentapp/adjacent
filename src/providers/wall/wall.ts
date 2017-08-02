import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'
import { User } from '../../providers/auth/auth';

export class Comment {
  id: number;
  card_id: number;
  timestamp: any;
  message: string;
  likes: number[];
  dislikes: number[];
  score: number;
  user: User;
  responses: Comment[];
  response_to: number;

  constructor (comment: any) {
    this.id = comment.id;
    this.card_id = comment.card_id;
    this.timestamp = comment.timestamp;
    this.message = comment.message;
    this.likes = comment.likes || [];
    this.dislikes = comment.dislikes || [];
    this.score = comment.score || 0;
    this.user = new User(comment.user.id, comment.user.fir_name, comment.user.las_name, comment.user.email, comment.user.photo_url);
    this.responses = !comment.responses ? [] : comment.responses.map((resp) => new Comment(resp));
    this.response_to = comment.response_to || null;
  }
}

@Injectable()
export class WallProvider {
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
              return new Comment(thing);
             });
            return manip;
          })
          .catch(this.handleError);
  }

  postComment(data): Observable<any> {
    let url = globs.BASE_API_URL + 'post_comment.php';
    return this.http.post(url, data)
            .map(this.extractData)
            .map((data) => {
              return new Comment(data);
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

    let url = globs.BASE_API_URL + 'like_wall_post.php';
    return this.http.post(url, item)
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
