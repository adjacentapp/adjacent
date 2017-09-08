import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams, LoadingController, Loading, AlertController, Slides, ViewController, ToastController, Platform} from 'ionic-angular';
import * as globs from '../../app/globals'
import { CardProvider } from '../../providers/card/card';
import { AuthProvider } from '../../providers/auth/auth';
// import { FoundedPage } from '../../pages/founded/founded';

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

  public industries = globs.INDUSTRIES;
  public challenges = globs.SKILLS;
  // private stages = globs.STAGES;
  // private networks = globs.NETWORKS;

  constructor(public navCtrl: NavController, public navParams: NavParams, private card: CardProvider, private auth: AuthProvider, private loadingCtrl: LoadingController, private alertCtrl: AlertController, private viewCtrl: ViewController, private toast: ToastController, private platform: Platform) {
    this.deleteCallback = this.navParams.get('deleteCallback');
    this.updateCallback = this.navParams.get('updateCallback');
    this.item = navParams.get('item') ? {...navParams.get('item')} : {
    	industry: null,
      pitch: '',
    	details: '',
      who: '',
      challenge_ids: [],
      challenge_names: [],
      challenge_details: '',
    	// stage: 0,
      // networks: 0,
      founder_id: this.auth.currentUser.id,
    };

    if(this.platform.is('android'))
      this.is_android = true;
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

  public updateNamesByIds(){
    this.item.challenge_names = globs.SKILLS
              .filter(item => this.item.challenge_ids.indexOf(item.id) >= 0)
              .map(item => item.name);
      console.log(this.item.challenge_names);
  }

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
    
    console.log(this.slides.getActiveIndex());
    if(this.slides.getActiveIndex() === 2)
      if(this.item.id){
        let toast = this.toast.create({
          message: "Followers of this idea will be notified whenever you update, up to once every 24 hours.",
          // duration: 6000,
          position: 'bottom',
          showCloseButton: true,
          closeButtonText: "OK"
        });
        toast.present();
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

  submitCard(){
    this.showLoading();
    this.card.post(this.item).subscribe(
      success => {
        if(this.item.id){ // edit
          this.updateCallback(success).then(()=>{
            this.navCtrl.pop();
          });
        }
        else {            // create
          this.navCtrl.push('FoundedPage').then(() => {
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
