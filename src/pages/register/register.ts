import { Component } from '@angular/core';
import { NavController, AlertController, IonicPage, Loading, LoadingController, Platform } from 'ionic-angular';
import { Push, PushObject, PushOptions } from '@ionic-native/push';
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
 
  constructor(private nav: NavController, private auth: AuthProvider, private alertCtrl: AlertController, private loadingCtrl: LoadingController, private push: Push, public platform: Platform) {}
 
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
            if (this.createSuccess) {
              this.nav.setRoot(DiscoverPage);
              this.registerPushToken();
            }
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
              alert('New notification: ' + data.message);
              console.log(data);
            }
          }]
        });
        confirmAlert.present();
      } else {
        //if user NOT using app and push notification comes
        //TODO: Your logic on click of push notification directly
        // this.nav.push(DetailsPage, { message: data.message });
        alert('From notification: ' + data.message);
        console.log(data);
        console.log('Push notification clicked');
      }
    });

    pushObject.on('error').subscribe(error => console.error('Error with Push plugin' + error));
  }
}