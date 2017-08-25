import { Component } from '@angular/core';
import { NavController, MenuController, AlertController, LoadingController, Loading, IonicPage } from 'ionic-angular';
import { AuthProvider } from '../../providers/auth/auth';
import { DiscoverPage } from '../../pages/discover/discover';
 
@IonicPage()
@Component({
  selector: 'page-login',
  templateUrl: 'login.html',
})
export class LoginPage {
  loading: Loading;

  registerCredentials = { email: '', password: '' };
 
  constructor(private nav: NavController, public menu: MenuController, private auth: AuthProvider, private alertCtrl: AlertController, private loadingCtrl: LoadingController) {}

  ionViewWillEnter() {
    this.menu.swipeEnable( false );
  }

  ionViewDidLeave() {
    this.menu.swipeEnable( true );
  }

  public checkPassword(){}
  public checkEmail(){}

  public goToPage(name){
    this.nav.push(name);
  }
   // public createAccount() {
  //   this.nav.push('RegisterPage');
  // }
  // public forgotPassword() {
  //   this.nav.push('ForgotPage');
  // }
 
  public login() {
    this.showLoading();
    this.auth.login(this.registerCredentials).subscribe(
      user => {
        if (this.auth.valid) {     
          this.nav.setRoot(DiscoverPage);
        } else if (user.message) {
          this.showError(user.message);
        } else {
          this.showError("Access Denied");
        }
        this.registerCredentials.password = '';
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
    if(this.loading)
      this.loading.dismiss();
 
    let alert = this.alertCtrl.create({
      title: 'Uh Oh',
      subTitle: text,
      buttons: ['OK']
    });
    alert.present(prompt);
  }
}