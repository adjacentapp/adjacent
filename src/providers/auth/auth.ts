import { Injectable } from '@angular/core';
import {Observable} from 'rxjs/Observable';
import 'rxjs/add/operator/map';
 
export class User {
  id: number;
  fir_name: string;
  las_name: string;
  email: string;
  photo_url: string;
 
  constructor(id: number, fir_name: string, las_name: string, email: string, photo_url: string) {
  	this.id = id;
    this.fir_name = fir_name;
    this.las_name = las_name;
    this.email = email;
    this.photo_url = photo_url;
  }
}
 
@Injectable()
export class AuthProvider {
  check: boolean = false;
  currentUser: User;
 
  public login(credentials) {
    if (credentials.email === null || credentials.password === null) {
      return Observable.throw("Please insert credentials");
    } else {
      return Observable.create(observer => {
        // At this point make a request to your backend to make a real check!
        let access = (credentials.password === "pass" && credentials.email === "email");
        console.log(access);
        this.currentUser = new User(9, 'Simon', 'Cotton', 'saimon@devdactic.com', '../../assets/img/anon_photo.png');
        this.check = true;
        observer.next(access);
        observer.complete();
      });
    }
  }
 
  public register(credentials) {
    if (credentials.email === null || credentials.password === null) {
      return Observable.throw("Please insert credentials");
    } else {
      // At this point store the credentials to your backend!
      return Observable.create(observer => {
        observer.next(true);
        observer.complete();
      });
    }
  }
 
  public getUserInfo() : User {
    return this.currentUser;
  }

  public test() {
  	return 'test success!';
  }
 
  public logout() {
    return Observable.create(observer => {
      this.currentUser = null;
      observer.next(true);
      observer.complete();
    });
  }
}