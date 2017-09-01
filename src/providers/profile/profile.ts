import { Injectable }              from '@angular/core';
import { Http, Response }          from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'
import { AuthProvider, User } from '../../providers/auth/auth';

import { File } from '@ionic-native/file';
import { FileTransfer } from '@ionic-native/file-transfer';
// import { FilePath } from '@ionic-native/file-path';

declare var cordova: any;

export class Profile {
  user: User;
  skill_ids: number[];
  skill_names: string[];
  bio: string;

  constructor(user: User, skill_ids: any[], bio: string) {
    this.user = user;
    this.bio = bio;
    this.skill_ids = skill_ids;
    this.skill_names = globs.SKILLS
                        .filter(item => skill_ids.indexOf(item.id) >= 0)
                        .map(item => item.name);
  }
}

@Injectable()
export class ProfileProvider {

  constructor (private http: Http, private auth: AuthProvider, private transfer: FileTransfer, private file: File) {}

  getProfile(user_id, my_id): Observable<any> {
    let query = '?user_id=' + user_id + '&my_id=' + my_id;
    let url = globs.BASE_API_URL + 'get_profile.php' + query;

    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            let user = new User(data.id, data.fir_name, data.las_name, data.email, data.photo_url);
            return new Profile(user, data.skill_ids, data.bio);
          })
          .catch(this.handleError);
  }

  updateProfile (data): Observable<any> {
    let url = globs.BASE_API_URL + 'post_profile.php';
    return this.http.post(url, data)
            .map(this.extractData)
            .map((data) => {
              let user = this.auth.updateCurrentUser(data);
              return new Profile(user, data.skill_ids, data.bio);
            })
            .catch(this.handleError);
  }

  uploadPhoto (filename): Promise<any> {      
      let url = globs.BASE_API_URL + 'post_profile_photo.php';
      let targetPath = cordova.file.dataDirectory + filename;
      let fileTransfer = this.transfer.create();
      let options = {
        fileKey: "file",
        fileName: filename,
        chunkedMode: false,
        mimeType: "multipart/form-data",
        params : {'fileName': filename}
      };
      
      return new Promise((resolve, reject) => {
        fileTransfer.upload(targetPath, url, options)
        .then(data => {
            let resp = JSON.parse(data.response);
            resolve(resp);
        }, err => {
            this.handleError(err);
            reject(err);
        });
      });
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
