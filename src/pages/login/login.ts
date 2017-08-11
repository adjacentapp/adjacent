import { Component } from '@angular/core';
import { NavController, MenuController, AlertController, LoadingController, Loading, IonicPage, Platform } from 'ionic-angular';
import { Push, PushObject, PushOptions } from '@ionic-native/push';
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
 
  constructor(private nav: NavController, public menu: MenuController, private auth: AuthProvider, private alertCtrl: AlertController, private loadingCtrl: LoadingController, private push: Push, public platform: Platform) {
    // immediately go to discover if cookies found
    if(localStorage.token && localStorage.user_id) {
      this.auth.loginFromLocalStorage();
      this.nav.setRoot(DiscoverPage);
      // if token authentication issue, redirect back to login
      this.auth.checkToken(localStorage.token).subscribe(
        user => {
          setTimeout(() => { this.checkingSession = false; }, 500);
          if(!this.auth.valid) {
            localStorage.clear();
            this.showError("Something went wrong. Please sign in again.");
            this.nav.setRoot('LoginPage');
          }
          else
            this.registerPushToken();
        },
        error => {
            this.showError(error);
        });
    }
    else
      this.checkingSession = false;
  }

  ionViewWillEnter() {
    this.menu.swipeEnable( false );
  }

  ionViewDidLeave() {
    this.menu.swipeEnable( true );
  }

  public checkPassword(){}
  public checkEmail(){}
 
  public createAccount() {
    this.nav.push('RegisterPage');
  }
 
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

  public registerPushToken() {
    if (!this.platform.is('cordova')) {
      console.warn('Push notifications not initialized. Cordova is not available');
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
      this.auth.registerPushToken(data.registrationId).subscribe(
        success => {
          console.log('Device token successfully registered');
         },
        error => console.log(error)
      );
    });

    pushObject.on('notification').subscribe((data: any) => {
      console.log('message -> ' + data.message);
      //if user using app and push notification comes
      if (data.additionalData.foreground) {
        // if application open, show popup
        let confirmAlert = this.alertCtrl.create({
          title: 'New Notification',
          message: data.message,
          buttons: [{
            text: 'Ignore',
            role: 'cancel'
          }, {
            text: 'View',
            handler: () => {
              //TODO: Your logic here
              // this.nav.push(DetailsPage, { message: data.message });
              alert('gotothepage? ---- ' + data.message);
            }
          }]
        });
        confirmAlert.present();
      } else {
        //if user NOT using app and push notification comes
        //TODO: Your logic on click of push notification directly
        // this.nav.push(DetailsPage, { message: data.message });
        alert('Can this work as deeplinking? --- ' + data.message);
        console.log('Push notification clicked');
      }
    });

    pushObject.on('error').subscribe(error => console.error('Error with Push plugin' + error));
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