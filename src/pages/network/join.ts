import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams, LoadingController, Loading, AlertController, Slides, ViewController, ToastController, Platform} from 'ionic-angular';
import * as globs from '../../app/globals'
import { AuthProvider } from '../../providers/auth/auth';

@Component({
  selector: 'join-network-page',
  templateUrl: 'join.html'
})
export class JoinNetworkPage {
  @ViewChild(Slides) slides: Slides;
  loading: Loading;
  email: string;
  code: string;
  public networks = globs.NETWORKS;

  constructor(public navCtrl: NavController, public navParams: NavParams, private auth: AuthProvider, private loadingCtrl: LoadingController, private alertCtrl: AlertController, private viewCtrl: ViewController, private toast: ToastController, private platform: Platform) {
  }

  sendEmail(){
    this.showLoading();
    this.auth.requestNetwork(this.email, null).subscribe(
      success => {
        console.log(success);
        this.loading.dismiss().then(() => {
          if(!success['network_exists']){
            let alert = this.alertCtrl.create({
              title: "Sorry",
              subTitle: "This community does not yet exist, but a request has been logged for review.",
              buttons: [{text: "Ok"}]
            });
            alert.present();
          }
          else if(success['verified']){
            let alert = this.alertCtrl.create({
              title: "Already a Member",
              subTitle: "You are already a verified member of this community.",
              buttons: [{text: "Ok"}]
            });
            alert.present();
          }
          else if(success['request_sent']){
            let alert = this.alertCtrl.create({
              title: "Success",
              subTitle: "A verification email has been sent to your address.",
              buttons: [{
                text: "Ok",
                handler: () => {
                  alert.dismiss().then(() => {
                    this.navCtrl.pop()
                  });
                  return false; // for loading.dismiss() bug
                }
              }]
            });
            alert.present();
          }
         });
      },
      error => console.log(error)
    );
  }

  showLoading() {
    this.loading = this.loadingCtrl.create({
      content: 'Please wait...',
      dismissOnPageChange: false
    });
    this.loading.present();
  }

}
