import { Component } from '@angular/core';
import { NavController, MenuController, AlertController, LoadingController, Loading, IonicPage, Platform } from 'ionic-angular';
import { AuthProvider } from '../../providers/auth/auth';
import { DiscoverPage } from '../../pages/discover/discover';
import { FtuePage } from '../../pages/ftue/ftue';
import * as globs from '../../app/globals'
import { Storage } from '@ionic/storage';
 
@IonicPage()
@Component({
  selector: 'page-login',
  templateUrl: 'login.html',
})
export class LoginPage {
  loading: Loading;
  cordova: boolean;
  registerCredentials = { email: '', password: '' };
 
  constructor(private nav: NavController, public menu: MenuController, private auth: AuthProvider, private alertCtrl: AlertController, private loadingCtrl: LoadingController, private platform: Platform, private storage: Storage) {
      this.cordova = this.platform.is('cordova');
  }

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
 
  public login() {
    this.showLoading('Please wait...');
    this.auth.login(this.registerCredentials).subscribe(
      user => {
        if (this.auth.valid) {
          if(this.auth.ftue_complete){
            // localStorage.ftue_complete = true;
            this.storage.set('ftue_complete', true)
            this.nav.setRoot(DiscoverPage);
          }
          else {
            globs.setFtueFilters();
            this.nav.setRoot(FtuePage);
          }
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

  public facebook() {
    this.showLoading('Connecting to Facebook...');
    this.auth.facebook().then(
      success => this.successPopup(),
      err => this.showError(err)
    );
  }

  showLoading(text) {
    this.loading = this.loadingCtrl.create({
      content: text,
      dismissOnPageChange: true
    });
    this.loading.present();
  }

  successPopup() {
    this.loading.dismiss();
    let alert = this.alertCtrl.create({
      title: "Success",
      subTitle: "Facebook Connected",
      buttons: [
        {
          text: "Let's Go!",
          handler: data => {
            this.nav.setRoot(DiscoverPage);
            this.nav.popToRoot();
          }
        }
      ]
    });
    alert.present();
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