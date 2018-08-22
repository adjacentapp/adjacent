import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams, Slides, AlertController, LoadingController, Loading } from 'ionic-angular';
import { AuthProvider } from '../../providers/auth/auth';
import { EditProfilePage } from '../profile/edit';
import { ProfileProvider, Profile } from '../../providers/profile/profile';
import { DiscoverPage } from '../../pages/discover/discover';
import { NewCardPage } from '../../pages/card/new';
import * as globs from '../../app/globals'

@Component({
	selector: 'ftue-page',
	templateUrl: 'ftue.html'
})
export class FtuePage {
	@ViewChild(Slides) slides: Slides;
  loading: Loading;
	profile: Profile;  
  track: string;
  role: string;
  network: string;
  verified: boolean = false;
  bounce: boolean = false;
  currentUser: any;
  code: string;
  email: string;
  public networks = globs.NETWORKS;

  welcomeTexts = {
    university: "",
    group: "",
    individual: "Welcome to your entreprenurial community.",
  };

	constructor(public navCtrl: NavController, public navParams: NavParams, private auth: AuthProvider, private profileProvider: ProfileProvider, private alertCtrl: AlertController, private loadingCtrl: LoadingController) {
    this.currentUser = this.auth.currentUser;
    this.getProfile()
	}

  getProfile(){
    this.profileProvider.getProfile(this.auth.currentUser.id, this.auth.currentUser.id)
      .subscribe(
        profile => this.profile = profile,
        error => console.log(<any>error)
      );
  }

  setTrack(track) {
    this.track = track;
    if (track == 'individual'){
      this.verified = true
    }

    this.goToNextSlide();
  }

  setRole(role) {
    this.role = role;
    if(role == 'founder'){
      let handleSkip = () => {
        return new Promise((resolve, reject) => {
          this.auth.completeFtue().subscribe(res => {
            globs.setFtueFilters();
            globs.setIntroQuote({ftue: true, quote: 'Find resources, join discussions, and update your followers.', hamburgerTip: 'Pitch ideas and stay connected with ideas you follow.'})
            this.navCtrl.setRoot(DiscoverPage);
            resolve();
          });
        });
      }
      let handleSubmit = () => {
        return new Promise((resolve, reject) => {
          let alert = this.alertCtrl.create({
            title: 'Congratulations!',
            message: 'Your idea is now published. You can share it with mentors to get feedback and find others to join your team.',
            buttons: [
              {
                text: 'Close',
                role: 'cancel',
                handler: () => { 
                  globs.setFtueFilters();
                  globs.setIntroQuote({ftue: true, quote: 'Find resources, join discussions, and update your followers.', hamburgerTip: 'Pitch ideas and stay connected with ideas you follow.'})
                  this.navCtrl.setRoot(DiscoverPage);
                  resolve();
                }
              },
            ]
          });
          this.auth.completeFtue().subscribe(res => {
            alert.present();
          });
        });
      }
      this.navCtrl.push(NewCardPage, {
        allowSkip: true,
        skipCallback: handleSkip,
        ftueSubmitCallback: handleSubmit,
      });
    }
    else {
      let placeholder = ''
      if(role == 'collaborator') {
        placeholder = "Fill out a profile with your skills to connect to start-ups that need your assistance."
      }
      else if(role == 'mentor'){
        placeholder = "Fill out a profile with your skills to connect to start-ups that need your assistance."
      }
      let handleUpdate = (_params) => {
        return new Promise((resolve, reject) => {
          this.auth.completeFtue().subscribe(res => {
            globs.setFtueFilters();
            globs.setIntroQuote({ftue: true, quote: 'Here you can find start-ups to work with and stay connected to resources and events.', hamburgerTip: 'Update your bio and stay connected with ideas you follow.'})
            this.navCtrl.setRoot(DiscoverPage);
            resolve();
          });
        });
      }
      this.navCtrl.push(EditProfilePage, {
        placeholder: placeholder,
        profile: this.profile,
        updateCallback: handleUpdate,
        preventCallbackNav: true,
      });
    }
  }

  goToNextSlide() {
    let nextIndex = this.slides.getActiveIndex() + 1;
    setTimeout(() => {
      this.slides.slideTo(nextIndex, 400);
    }, 300);
  }

  triggerBounce(){
    console.log('bounce')
    this.bounce = true;
    setTimeout(() => {
      this.bounce = false;
    }, 600)
  }

  showLoading() {
    this.loading = this.loadingCtrl.create({
      content: 'Please wait...',
      dismissOnPageChange: false
    });
    this.loading.present();
  }

  sendEmail(){
    this.showLoading();
    this.auth.requestNetwork(this.email, null).subscribe(
      success => {
        console.log(success);
        this.loading.dismiss().then(() => {
          if(!success['network_exists']){
            let alert = this.alertCtrl.create({
              title: "Early Adopter!",
              subTitle: "This university does not yet have a private community on Adjacent, but a request has been logged for review.",
              buttons: [{
                text: "Continue",
                handler: () => {
                  alert.dismiss().then(() => {
                    this.verified = true;
                    this.goToNextSlide();
                  });
                  return false; // for loading.dismiss() bug
                }
              }]
            });
            alert.present();
          }
          else if(success['verified']){
            let alert = this.alertCtrl.create({
              title: "Already a Member",
              subTitle: "You are already a verified member.",
              buttons: [{
                text: "Ok",
                handler: () => {
                  alert.dismiss().then(() => {
                    this.verified = true;
                    this.goToNextSlide();
                  });
                  return false; // for loading.dismiss() bug
                }
              }]
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
                    this.verified = true;
                    this.goToNextSlide();
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

  confirmCode(){
    this.showLoading();
    this.auth.requestNetwork(this.auth.currentUser.email, this.code).subscribe(
     success => {
       console.log(success);
       this.loading.dismiss().then(() => {
         if(!success['network_exists']){
           let alert = this.alertCtrl.create({
             title: "No Match Found",
             subTitle: "That code does not match any we have on record. Please confirm you've entered it correctly.",
             buttons: [{text: "Ok"}]
           });
           alert.present();
         }
         else if(success['verified']){
           let newNetwork = success['network'];
           console.log(newNetwork)
           let alert = this.alertCtrl.create({
             title: "Already a Member",
             subTitle: "You are already a verified member.",
             buttons: [{
               text: "Ok",
               handler: () => {
                 alert.dismiss().then(() => {
                   this.welcomeTexts.group = "Great! You're now a part of the " + newNetwork.name + " private group. You can share ideas with others in the group for feedback, and build your entrepreneurial community.";
                   this.verified = true;
                   this.goToNextSlide();
                 });
                 return false; // for loading.dismiss() bug
               }
             }]
           });
           alert.present();
         }
         else if(success['access_granted']){
           let newNetwork = success['network'];
           let alert = this.alertCtrl.create({
             title: "Success",
             // subTitle: "Welcome! You've been granted access to the " + success['network_name'] + "!",
             subTitle: success['msg'],
             buttons: [{
               text: "Ok",
               handler: () => {
                 alert.dismiss().then(() => {
                   this.networks.push(newNetwork);
                   globs.setNetworks(this.networks);
                   this.welcomeTexts.group = "Great! You're now a part of the " + newNetwork.name + " private group. You can share ideas with others in the group for feedback, and build your entrepreneurial community.";
                   this.verified = true;
                   this.goToNextSlide();
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


}
