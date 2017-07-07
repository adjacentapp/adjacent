import { Component } from '@angular/core';
import { NavController, AlertController, LoadingController, Loading, IonicPage } from 'ionic-angular';
import { AuthProvider } from '../../providers/auth/auth';
import { DiscoverPage } from '../../pages/discover/discover';
 
@IonicPage()
@Component({
  selector: 'page-login',
  templateUrl: 'login.html',
})
export class LoginPage {
  checkingSession: boolean = true;
  loading: Loading;

  registerCredentials = { email: '', password: '' };
 
  constructor(private nav: NavController, private auth: AuthProvider, private alertCtrl: AlertController, private loadingCtrl: LoadingController) {
    if(localStorage.token)
      this.auth.checkToken(localStorage.token).subscribe(user => {
        this.checkingSession = false;
        if(user.valid)
          this.nav.setRoot(DiscoverPage);
        else
          this.showError("failed token check");
        },
        error => {
          this.showError(error);
        }
      );
    else
      this.checkingSession = false;
  }
 
  public createAccount() {
    this.nav.push('RegisterPage');
  }
 
  public login() {
    this.showLoading();
    this.auth.login(this.registerCredentials).subscribe(allowed => {
      if (allowed) {     
        this.nav.setRoot(DiscoverPage);
      } else {
        this.showError("Access Denied");
      }
    },
      error => {
        this.showError(error);
      });
  }
 
  showLoading() {
    this.loading = this.loadingCtrl.create({
      content: 'Please wait...',
      dismissOnPageChange: true
    });
    this.loading.present();
  }
 
  showError(text) {
    this.loading.dismiss();
 
    let alert = this.alertCtrl.create({
      title: 'Fail',
      subTitle: text,
      buttons: ['OK']
    });
    alert.present(prompt);
  }
}