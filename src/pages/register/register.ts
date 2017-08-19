import { Component } from '@angular/core';
import { NavController, AlertController, IonicPage, Loading, LoadingController } from 'ionic-angular';
import { AuthProvider } from '../../providers/auth/auth';
import { DiscoverPage } from '../../pages/discover/discover';
 
@IonicPage()
@Component({
  selector: 'page-register',
  templateUrl: 'register.html',
})
export class RegisterPage {
  loading: Loading;
  createSuccess = false;
  registerCredentials = { email: '', password: '' };
  passwordValid: boolean = false;
  errorMessages: any = [];
 
  constructor(private nav: NavController, private auth: AuthProvider, private alertCtrl: AlertController, private loadingCtrl: LoadingController) {}
 
  public register() {
    this.showLoading();
    this.auth.register(this.registerCredentials).subscribe(results => {
      console.log(results);
      if (this.auth.valid) {
        this.createSuccess = true;
        this.showPopup("Success", "Account created.");
      } else if (results.message) {
        this.showPopup("Error", results.message);
      } else {
        this.showPopup("Error", "Problem creating account.");
      }
      if(this.loading) this.loading.dismiss();
    },
      error => {
        this.showPopup("Error", error);
        if(this.loading) this.loading.dismiss();
      });
  }

  public checkPassword(){
    this.errorMessages = [];
    let password = this.registerCredentials.password;
    if(password.length < 8) 
      this.errorMessages.push('Must be at least 8 characters');
    if(!password.match(/[^a-z^A-Z]/g))
      this.errorMessages.push('Must contain at least 1 number or special character');
    this.passwordValid = this.errorMessages.length === 0;
  }
 
  showPopup(title, text) {
    let alert = this.alertCtrl.create({
      title: title,
      subTitle: text,
      buttons: [
        {
          text: 'OK',
          handler: data => {
            if (this.createSuccess)
              this.nav.setRoot(DiscoverPage);
            this.nav.popToRoot();
          }
        }
      ]
    });
    alert.present();
  }

  showLoading() {
    this.loading = this.loadingCtrl.create({
      content: 'Please wait...',
      dismissOnPageChange: true
    });
    this.loading.present();
  }
  
}