import { Component, Input } from '@angular/core';
import { NavController, NavParams, LoadingController, Loading, AlertController } from 'ionic-angular';
import * as globs from '../../app/globals'
import { CardProvider } from '../../providers/card/card';
import { AuthProvider } from '../../providers/auth/auth';
import { ShowCardPage } from '../../pages/card/show';
import { ProfilePage } from '../../pages/profile/profile';

@Component({
  selector: 'new-card-page',
  templateUrl: 'new.html'
})
export class NewCardPage {
  loading: Loading;
  item: any;
  valid: boolean = false;
  callback: any;

  private industries = globs.INDUSTRIES;
  private challenges = globs.SKILLS;
  private stages = globs.STAGES;
  private networks = globs.NETWORKS;

  constructor(public navCtrl: NavController, public navParams: NavParams, private card: CardProvider, private auth: AuthProvider, private loadingCtrl: LoadingController, private alertCtrl: AlertController) {
    this.callback = this.navParams.get('callback');
    this.item = navParams.get('item') || {
    	industry: null,
    	pitch: '',
      who: '',
      // location: true,
      // lat: 0,
      // lon: 0,
    	// anonymous: false,
      challenge: null,
      challenge_details: '',
    	stage: 0,
      networks: 0,
      founder_id: this.auth.currentUser.id,
    };

    console.log(this.item);
  }

  submitCard(){
    // this.showLoading();
    this.card.post(this.item).subscribe(
      success => {
        this.navCtrl.pop();
        this.navCtrl.push(ShowCardPage, {
          item: this.item
        });
       },
      error => console.log(error)
    );
  }

  deleteCard(e){
    e.preventDefault();
    let alert = this.alertCtrl.create({
      title: 'Delete Card',
      message: 'Are you sure? This cannot be undone.',
      buttons: [
        {
          text: 'Cancel',
          role: 'cancel',
          handler: () => {
            console.log('Cancel clicked');
          }
        },
        {
          text: 'Delete',
          handler: () => {
            this.showLoading();
            this.card.delete(this.item.id).subscribe(
              success => {
                let params = {
                  remove_id: this.item.id,
                  user_id: this.auth.currentUser.id
                };
                console.log(this);
                this.callback(params).then(()=>{
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
}
