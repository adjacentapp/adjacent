import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, LoadingController, Loading, AlertController } from 'ionic-angular';
import { AuthProvider } from '../../providers/auth/auth';

@IonicPage()
@Component({
  selector: 'page-forgot',
  templateUrl: 'forgot.html',
})
export class ForgotPage {
  loading: Loading;
  email: string;

  constructor(public nav: NavController, public navParams: NavParams, private loadingCtrl: LoadingController, private alertCtrl: AlertController, private auth: AuthProvider) {
  }

  ionViewDidLoad() {

  }

  public reset() {
    this.showLoading();
    this.auth.resetPassword(this.email).subscribe(
      user => this.success(),
      error => {
        this.showError(error);
      });
  }

  private success(){
    this.nav.pop();
    // let alert = this.alertCtrl.create({
    //   title: 'Email Sent',
    //   message: 'Please check your email address for further instructions.',
    //   buttons: [
    //     {
    //       text: 'Cancel',
    //       role: 'cancel',
    //       handler: () => console.log('Cancel clicked')
    //     },
    //     {
    //       text: 'Ok',
    //       handler: () => this.nav.pop()
    //   }]
    // });
    // alert.present();
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
