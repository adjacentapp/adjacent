import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams, LoadingController, Loading, AlertController, Slides, ViewController, ToastController, Platform} from 'ionic-angular';
import * as globs from '../../app/globals'
import { CardProvider } from '../../providers/card/card';
import { AuthProvider } from '../../providers/auth/auth';
// import { FoundedPage } from '../../pages/founded/founded';
import { ShowCardPage } from '../../pages/card/show';
import { ProfileProvider, Profile } from '../../providers/profile/profile';

@Component({
  selector: 'new-card-page',
  templateUrl: 'new.html'
})
export class NewCardPage {
  @ViewChild(Slides) slides: Slides;
  loading: Loading;
  keyboard_visible: boolean = false;
  is_android: boolean = false;
  item: any;
  untouched_item: any;
  valid: boolean = false;
  deleteCallback: any;
  updateCallback: any;
  skipCallback: any;
  ftueSubmitCallback: any;

  public industries = globs.INDUSTRIES;
  public challenges = globs.SKILLS;
  // public stages = globs.STAGES;
  public networks = globs.NETWORKS;

  constructor(public navCtrl: NavController, public navParams: NavParams, private card: CardProvider, private auth: AuthProvider, private loadingCtrl: LoadingController, private alertCtrl: AlertController, private viewCtrl: ViewController, private toast: ToastController, private platform: Platform, private profileProvider: ProfileProvider) {
    this.deleteCallback = this.navParams.get('deleteCallback');
    this.updateCallback = this.navParams.get('updateCallback');
    this.skipCallback = navParams.get('skipCallback');
    this.ftueSubmitCallback = navParams.get('ftueSubmitCallback');
    this.item = navParams.get('item') ? {...navParams.get('item')} : {
    	industry: null,
      pitch: '',
    	details: '',
      who: '',
      challenge_ids: [],
      challenge_names: [],
      challenge_details: '',
    	// stage: 0,
      network_id: 0,
      network_names: [],
      founder_id: this.auth.currentUser.id,
    };

    if(this.platform.is('android'))
      this.is_android = true;

    this.getProfile()
  }

  getProfile(){
    this.profileProvider.getProfile(this.auth.currentUser.id, this.auth.currentUser.id)
      .subscribe(
        (profile) => {
          console.log(profile)
          this.networks = profile.networks
          globs.setNetworks(this.networks);
        },
        error => console.log(<any>error)
      );
  }

  public handleKeyboardOpen(){
    setTimeout(() => {
     this.keyboard_visible = true;
    }, 100);
  }

  public handleKeyboardClose(){
    setTimeout(() => {
      this.keyboard_visible = false;
     }, 100);
  }

  public updateChallengeNamesByIds(){
    this.item.challenge_names = globs.SKILLS
              .filter(item => this.item.challenge_ids.indexOf(item.id) >= 0)
              .map(item => item.name);
      console.log(this.item.challenge_names);
  }

  public updateNetworkNamesByIds(){
    // this.item.network_names = globs.NETWORKS
    //           .filter(item => this.item.network_ids.indexOf(item.id) >= 0)
    //           .map(item => item.name);
    //   console.log(this.item.network_names);
  }

  // public updateNamesByIds(name_array, id_array, global_array){
  //   let new_name_array = global_array
  //             .filter(item => id_array.indexOf(item.id) >= 0)
  //             .map(item => item.name);
  // //   name_array.forEach(item => item);
  //       for(var i=0; i<name_array.length; i++)
  //         name_array[i] = new_name_array[i];
  //   console.log(this.item.challenge_names);
  // }

  ionViewDidLoad() {
    this.viewCtrl.showBackButton(false);
    this.slides.lockSwipes(true);
  }

  nextSlide(e){
    e.preventDefault();
    e.stopPropagation();
    this.slides.lockSwipes(false);
    this.slides.slideNext(400);
    this.slides.lockSwipes(true);
    
    // console.log(this.slides.getActiveIndex());
    if(this.slides.getActiveIndex() === 2){
      if(this.item.id){
        let toast = this.toast.create({
          message: "Followers of this idea will be notified whenever you update, up to once every 24 hours.",
          duration: 8000,
          position: 'bottom',
          showCloseButton: true,
          closeButtonText: "OK"
        });
        toast.present();
      }
      else if(this.networks.length > 1){
        setTimeout(() => {
          let toast = this.toast.create({
            message: "Select a Community to limit the audience of your idea.",
            duration: 10000,
            position: 'bottom',
            showCloseButton: true,
            closeButtonText: "OK"
          });
          toast.present();
        }, 3000)
      }
    }
  }

  prevSlide(e){
    e.preventDefault();
    e.stopPropagation();
    this.slides.lockSwipes(false);
    this.slides.slidePrev(400);
    this.slides.lockSwipes(true);
  }

  cancel(e){
    e.preventDefault();
    if(this.item.industry || this.item.pitch.length || this.item.details.length || this.item.who.length || this.item.challenge || this.item.challenge_details.length){
        let alert = this.alertCtrl.create({
          title: this.item.id ? 'Discard Changes' : 'Discard Idea',
          message: 'Are you sure? ' + (this.item.id ? 'Changes will be lost.' : 'Information will be lost.'),
          buttons: [
            {
              text: 'Stay',
              role: 'cancel',
              handler: () => { console.log('Cancel clicked'); }
            },
            {
              text: 'Discard',
              handler: () => {
                this.navCtrl.pop();
              }
            }
          ]
      });
      alert.present();
    }
    else
      this.navCtrl.pop();
  }

  skip(){
    this.skipCallback()
  }

  submitCard(){
    this.showLoading();
    this.card.post(this.item).subscribe(
      success => {
        if (this.ftueSubmitCallback){ // ftue create
          this.ftueSubmitCallback();
        }
        else if(this.item.id){ // edit
          this.updateCallback(success).then(()=>{
            this.navCtrl.pop();
          });
        }
        else {            // create
          console.log(success);
          this.navCtrl.push(ShowCardPage, {card_id: success.id}).then(() => {
          // this.navCtrl.push(FoundedPage).then(() => {
            let index = this.navCtrl.getActive().index;
             if(index == 3) // through Founded
               this.navCtrl.remove(index - 2, 2);
             else if (index == 2) // direct from sidemenu
               this.navCtrl.remove(index - 1, 1);
           });
         }
       },
      error => console.log(error)
    );
  }

  deleteCard(e){
    e.preventDefault();
    let alert = this.alertCtrl.create({
      title: 'Delete Idea',
      message: 'Are you sure? This cannot be undone.',
      buttons: [
        {
          text: 'Cancel',
          role: 'cancel',
          handler: () => console.log('Cancel clicked')
        },
        {
          text: 'Delete',
          handler: () => {
            this.showLoading();
            this.card.delete(this.item.id).subscribe(
              success => {
                let params = {
                  remove_id: this.item.id
                };
                this.deleteCallback(params).then(()=>{
                  this.navCtrl.remove(this.navCtrl.getActive().index - 1, 1);
                  this.navCtrl.pop();
                });
              },
              error => console.log(error)
            );
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

  doNothing(e){
    console.log('nothing');
    // if(this.keyboard_visible){
      e.stopPropagation();
      e.preventDefault();
      return false;
    // }
  }
}
