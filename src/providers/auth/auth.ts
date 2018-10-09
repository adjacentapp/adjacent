import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http'
import { Platform, AlertController } from 'ionic-angular'
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/map';
import * as globs from '../../app/globals'
import { Push, PushObject, PushOptions } from '@ionic-native/push';
import { Badge } from '@ionic-native/badge';
import { Facebook, FacebookLoginResponse } from '@ionic-native/facebook';
 
export class User {
  id: number;
  fir_name: string;
  las_name: string;
  email: string;
  photo_url: string;
  token?: string;
  networks?: any;
 
  constructor(id: number, fir_name: string, las_name: string, email: string, photo_url: string, token?: string, networks?: any) {
  	this.id = id || null;
    this.fir_name = !fir_name || fir_name == 'null' || fir_name == 'NULL' ? 'Entrepreneur' : fir_name;
    this.las_name = !las_name || las_name == 'null' || las_name == 'NULL' ? '' : las_name;
    this.email = email || null;
    this.photo_url = photo_url || null;
    this.token = token || null;
    if(token){
      localStorage.token = token;
      localStorage.user_id = this.id;
      localStorage.fir_name = this.fir_name;
      localStorage.las_name = this.las_name;
      localStorage.email = this.email;
      localStorage.photo_url = this.photo_url;
      localStorage.network_ids = networks && networks.length ? networks.map(item => item.id.toString()).join(',') : '';
    }
    this.networks = networks || [];
    if(this.networks.length){
      globs.setNetworks(this.networks);
    }
  }
}
 
@Injectable()
export class AuthProvider {
  valid: boolean = false;
  currentUser: User;
  pushToken: string;
  show_all_imgs: boolean = true;
  show_title: boolean = true;
  ftue_complete: boolean = false;

  // Observables for DeepNotification redirect listeners from app-component
  private dismissCheckDN: any;
  public checkDeepNotifications: any;

  constructor (private http: Http, private platform: Platform, private push: Push, private alertCtrl: AlertController, private badge: Badge, private fb: Facebook) {

    this.dismissCheckDN = null;
    this.checkDeepNotifications = Observable.create(observer => {
        this.dismissCheckDN = observer;
    });
  }

  loginFromLocalStorage(){
    if(localStorage.network_ids)
      this.currentUser = new User(localStorage.user_id, localStorage.fir_name, localStorage.las_name, localStorage.email, localStorage.photo_url, localStorage.token, localStorage.network_ids.split(',').map(function(item){ return {id: item}}) );
    else
      this.currentUser = new User(localStorage.user_id, localStorage.fir_name, localStorage.las_name, localStorage.email, localStorage.photo_url, localStorage.token );
    this.valid = true;
    this.ftue_complete = localStorage.ftue_complete;
  }

  checkToken(token, user_id): Observable<any> {
    let url = globs.BASE_API_URL + 'get_session.php?token=' + token + '&user_id=' + user_id;
    return this.http.get(url)
            .map(this.extractData)
            .map((data) => {
              if(data.valid){
                console.log(data);
                this.currentUser = new User(data.user_id, data.fir_name, data.las_name, data.email, data.photo_url, data.token, data.networks);
                this.valid = true;
                this.ftue_complete = data.ftue_complete;
                this.badge.set(data.badge_count);
                this.registerPushToken();
              }
              else {
                this.valid = false;
              }
              return data;
            })
            .catch(this.handleError);
  }

  public login(credentials): Observable<any> {
    let url = globs.BASE_API_URL + 'login.php';
    let data = {
      email: credentials.email,
      pass: this.sha256(globs.ENCRYPTION_KEY + credentials.password)
    }
    return this.http.post(url, data)
            .map(this.extractData)
            .map((data) => {
              if(data.valid){
                this.currentUser = new User(data.user_id, data.fir_name, data.las_name, data.email, data.photo_url, data.token, data.networks);
                this.valid = true;
                this.ftue_complete = data.ftue_complete;
                this.badge.set(data.badge_count);                
                this.registerPushToken();
               }
               else
                 this.valid = false;
              return data;
            })
            .catch(this.handleError);
  }

  public fb_login(credentials): Observable<any> {
    let url = globs.BASE_API_URL + 'login_facebook.php';
    return this.http.post(url, credentials)
            .map(this.extractData)
            .map((data) => {
              if(data.valid){
                this.currentUser = new User(data.user_id, data.fir_name, data.las_name, data.email, data.photo_url, data.token);
                this.valid = true;
                this.ftue_complete = data.ftue_complete;
                this.badge.set(data.badge_count);
                this.registerPushToken();
               }
               else
                 this.valid = false;
              return data;
            })
            .catch(this.handleError);
  }

  public register(credentials): Observable<any> {
    if (credentials.email === null || credentials.password === null) {
      return Observable.throw("Please insert credentials");
    } else {
      let url = globs.BASE_API_URL + 'signup.php';
      let data = {
        email: credentials.email,
        pass: this.sha256(globs.ENCRYPTION_KEY + credentials.password)
      };
      return this.http.post(url, data )
              .map(this.extractData)
              .map((data) => {
                console.log(data);
                if(data.valid){
                  this.currentUser = new User(data.user_id, '', '', data.email, '', data.token);
                  this.valid = true;
                  this.registerPushToken();
                  globs.setFirstSignInTrue();                
                  return this.currentUser;
                 }
                 else{
                   this.valid = false;
                   return data;
                 }
                
              })
              .catch(this.handleError);
    }
  }
 
  public getUserInfo() : User {
    return this.currentUser;
  }

  public updateCurrentUser(data) : User {
    this.currentUser = new User(data.id, data.fir_name, data.las_name, data.email, data.photo_url, this.currentUser.token); 
    return this.currentUser;
  }

  public resetPassword(email): Observable<any[]> {
    let url = globs.BASE_API_URL + 'reset_password_request.php';
    let data = {
      email: email,
      reset_code: this.sha256( Math.random().toString(36).substring(7) )
    };
    return this.http.post(url, data)
          .map(this.extractData)
          .catch(this.handleError);
  }

  public requestNetwork(email, code): Observable<any[]> {
    let url = globs.BASE_API_URL + 'request_network.php';
    return this.http.post(url, {user_id: this.currentUser.id, email: email, code: code})
          .map(this.extractData)
          .map((data) => {
            console.log(data);
            return data;
          })
          .catch(this.handleError);
  }

  public completeFtue(): Observable<any[]> {
    localStorage.ftue_complete = true;
    let url = globs.BASE_API_URL + 'complete_ftue.php';
    return this.http.post(url, {user_id: this.currentUser.id})
          .map(this.extractData)
          .map((data) => {
            console.log(data);
            return data;
          })
          .catch(this.handleError);
  }

  public logout(): Observable<any[]> {
    let data = {
      user_id: this.currentUser.id,
      push_token: this.pushToken,
      session_token: this.currentUser.token
    }
    console.log(data);
    let url = globs.BASE_API_URL + 'logout.php';
    return this.http.post(url, data )
            .map(this.extractData)
            .map((data) => {
              this.valid = false;
              this.badge.clear();
              localStorage.clear();
              this.currentUser = null;
              this.pushToken = null;
              console.log(data);
              return data;
            })
            .catch(this.handleError);
  }

  public getFollowers(card_id, offset, limit): Observable<any[]> {
    let query = '?card_id=' + card_id + '&offset=' + offset + '&limit=' + limit;
    let url = globs.BASE_API_URL + 'get_followers.php' + query;
    return this.http.get(url)
          .map(this.extractData)
          .map((data) => {
            return data.map((item, i) => {
              return new User(item.id, item.fir_name, item.las_name, item.email, item.photo_url);
             });
          })
          .catch(this.handleError);
  }

  public postPushToken(token): Observable<any[]> {
    let url = globs.BASE_API_URL + 'post_device_token.php';
    let data = {
      user_id: this.currentUser.id,
      token: token
    };
    return this.http.post(url, data)
          .map(this.extractData)
          .catch(this.handleError);
  }

  public registerPushToken() {
    if (!this.platform.is('cordova')) {
      console.warn('Push notifications not initialized. Cordova is not available');
      this.dismissCheckDN.next(false);
      return;
    }
    const options: PushOptions = {
      android: {
        senderID: '432969043178'
      },
      ios: {
        alert: 'true',
        badge: 'true',
        sound: 'true'
      },
      windows: {}
    };
    const pushObject: PushObject = this.push.init(options);

    pushObject.on('registration').subscribe((data: any) => {
      console.log('device token -> ' + data.registrationId);
      setTimeout(() => {
        console.log(this.currentUser);
        this.postPushToken(data.registrationId).subscribe(
          success => {
            console.log('Device token successfully registered', data.registrationId);
            this.pushToken = data.registrationId;
           },
          error => console.log(error)
        );
      }, 1000);
    });

    pushObject.on('notification').subscribe((data: any) => {
      console.log('push notification -> ' + data);
      if (data.additionalData.foreground) { // if application open, show popup
        this.badge.set(data.count);
        // this is where in-app badges should be handled, not in setTimeouts
      } else {  // if app is backgrounded/coldstart
        let split = data.additionalData.url.split('/');
        let id = split[ split.length-1 ];
        let object = split[ split.length-2 ];
        if(object == 'idea')
          this.dismissCheckDN.next('nc');
        else if(object == 'message')
          this.dismissCheckDN.next('message');
        else
          this.dismissCheckDN.next(id);
      }
    });

    pushObject.on('error').subscribe(error => console.error('Error with Push plugin' + error));
  }

  public facebook(): Promise<any> {
    return new Promise((resolve, reject) => {
      this.fb.login(['public_profile', 'user_friends', 'email'])
        .then((login_res: FacebookLoginResponse) => {
          console.log('login_res:', login_res);
          return new Promise((res, rej) => {
            this.fb.api('/me?fields=id,email,first_name,last_name,picture,gender', ['public_profile', 'user_friends', 'email']).then((api_res) => {
              console.log('api_res:', api_res);
              let credentials = {
                email: api_res.email,
                facebook_hash: this.sha256(globs.ENCRYPTION_KEY + api_res.id),
                fir_name: api_res.first_name,
                las_name: api_res.last_name,
                photo_url: 'https://graph.facebook.com/' + api_res.id + '/picture?type=large'
              };
              this.fb_login(credentials).subscribe(
                success => {
                  res(success);
                  resolve(success);
                },
                err => {
                  rej(err);
                  reject(err);
                }
              );
            });
          });
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
    console.log(error);
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

  private sha256(data) {
    var rotateRight = function (n,x) {
      return ((x >>> n) | (x << (32 - n)));
    };
    var choice = function (x,y,z) {
      return ((x & y) ^ (~x & z));
    };
    function majority(x,y,z) {
      return ((x & y) ^ (x & z) ^ (y & z));
    }
    function sha256_Sigma0(x) {
      return (rotateRight(2, x) ^ rotateRight(13, x) ^ rotateRight(22, x));
    }
    function sha256_Sigma1(x) {
      return (rotateRight(6, x) ^ rotateRight(11, x) ^ rotateRight(25, x));
    }
    function sha256_sigma0(x) {
      return (rotateRight(7, x) ^ rotateRight(18, x) ^ (x >>> 3));
    }
    function sha256_sigma1(x) {
      return (rotateRight(17, x) ^ rotateRight(19, x) ^ (x >>> 10));
    }
    function sha256_expand(W, j) {
      return (W[j&0x0f] += sha256_sigma1(W[(j+14)&0x0f]) + W[(j+9)&0x0f] + 
    sha256_sigma0(W[(j+1)&0x0f]));
    }

    /* Hash constant words K: */
    var K256 = new Array(
      0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5,
      0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
      0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3,
      0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
      0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc,
      0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
      0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7,
      0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
      0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13,
      0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
      0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3,
      0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
      0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5,
      0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
      0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208,
      0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2
    );

    /* global arrays */
    var ihash, count, buffer;
    var sha256_hex_digits = "0123456789abcdef";

    /* Add 32-bit integers with 16-bit operations (bug in some JS-interpreters: 
    overflow) */
    function safe_add(x, y)
    {
      var lsw = (x & 0xffff) + (y & 0xffff);
      var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
      return (msw << 16) | (lsw & 0xffff);
    }

    /* Initialise the SHA256 computation */
    function sha256_init() {
      ihash = new Array(8);
      count = new Array(2);
      buffer = new Array(64);
      count[0] = count[1] = 0;
      ihash[0] = 0x6a09e667;
      ihash[1] = 0xbb67ae85;
      ihash[2] = 0x3c6ef372;
      ihash[3] = 0xa54ff53a;
      ihash[4] = 0x510e527f;
      ihash[5] = 0x9b05688c;
      ihash[6] = 0x1f83d9ab;
      ihash[7] = 0x5be0cd19;
    }

    /* Transform a 512-bit message block */
    function sha256_transform() {
      var a, b, c, d, e, f, g, h, T1, T2;
      var W = new Array(16);

      /* Initialize registers with the previous intermediate value */
      a = ihash[0];
      b = ihash[1];
      c = ihash[2];
      d = ihash[3];
      e = ihash[4];
      f = ihash[5];
      g = ihash[6];
      h = ihash[7];

            /* make 32-bit words */
      for(var i=0; i<16; i++)
        W[i] = ((buffer[(i<<2)+3]) | (buffer[(i<<2)+2] << 8) | (buffer[(i<<2)+1] 
    << 16) | (buffer[i<<2] << 24));

            for(var j=0; j<64; j++) {
        T1 = h + sha256_Sigma1(e) + choice(e, f, g) + K256[j];
        if(j < 16) T1 += W[j];
        else T1 += sha256_expand(W, j);
        T2 = sha256_Sigma0(a) + majority(a, b, c);
        h = g;
        g = f;
        f = e;
        e = safe_add(d, T1);
        d = c;
        c = b;
        b = a;
        a = safe_add(T1, T2);
            }

      /* Compute the current intermediate hash value */
      ihash[0] += a;
      ihash[1] += b;
      ihash[2] += c;
      ihash[3] += d;
      ihash[4] += e;
      ihash[5] += f;
      ihash[6] += g;
      ihash[7] += h;
    }

    /* Read the next chunk of data and update the SHA256 computation */
    function sha256_update(data, inputLen) {
      var i, index, curpos = 0;
      /* Compute number of bytes mod 64 */
      index = ((count[0] >> 3) & 0x3f);
            var remainder = (inputLen & 0x3f);

      /* Update number of bits */
      if ((count[0] += (inputLen << 3)) < (inputLen << 3)) count[1]++;
      count[1] += (inputLen >> 29);

      /* Transform as many times as possible */
      for(i=0; i+63<inputLen; i+=64) {
                    for(var j=index; j<64; j++)
          buffer[j] = data.charCodeAt(curpos++);
        sha256_transform();
        index = 0;
      }

      /* Buffer remaining input */
      for(var k=0; k<remainder; k++)
        buffer[k] = data.charCodeAt(curpos++);
    }

    /* Finish the computation by operations such as padding */
    function sha256_final() {
      var index = ((count[0] >> 3) & 0x3f);
            buffer[index++] = 0x80;
            if(index <= 56) {
        for(var i=index; i<56; i++)
          buffer[i] = 0;
            } else {
        for(var j=index; j<64; j++)
          buffer[j] = 0;
                    sha256_transform();
                    for(var k=0; k<56; k++)
          buffer[k] = 0;
      }
            buffer[56] = (count[1] >>> 24) & 0xff;
            buffer[57] = (count[1] >>> 16) & 0xff;
            buffer[58] = (count[1] >>> 8) & 0xff;
            buffer[59] = count[1] & 0xff;
            buffer[60] = (count[0] >>> 24) & 0xff;
            buffer[61] = (count[0] >>> 16) & 0xff;
            buffer[62] = (count[0] >>> 8) & 0xff;
            buffer[63] = count[0] & 0xff;
            sha256_transform();
    }

    /* Split the internal hash values into an array of bytes */
    // function sha256_encode_bytes() {
    //         var j=0;
    //         var output = new Array(32);
    //   for(var i=0; i<8; i++) {
    //     output[j++] = ((ihash[i] >>> 24) & 0xff);
    //     output[j++] = ((ihash[i] >>> 16) & 0xff);
    //     output[j++] = ((ihash[i] >>> 8) & 0xff);
    //     output[j++] = (ihash[i] & 0xff);
    //   }
    //   return output;
    // }

    /* Get the internal hash as a hex string */
    function sha256_encode_hex() {
      var output = '';  // var output = new String();
      for(var i=0; i<8; i++) {
        for(var j=28; j>=0; j-=4)
          output += sha256_hex_digits.charAt((ihash[i] >>> j) & 0x0f);
      }
      return output;
    }

    

    /* test if the JS-interpreter is working properly */
    // function sha256_self_test()
    // {
    //   return sha256_digest("message digest") == 
    // "f7846f55cf23e14eebeab5b4e1550cad5b509e3348fbc4efa3a1413d393cb650";
    // }

    sha256_init();
    sha256_update(data, data.length);
    sha256_final();
    return sha256_encode_hex();
  }
}