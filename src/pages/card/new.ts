import { Component, Input } from '@angular/core';
import { NavController, NavParams, LoadingController, Loading } from 'ionic-angular';
import * as globs from '../../app/globals'
import { CardProvider } from '../../providers/card/card';
import { AuthProvider } from '../../providers/auth/auth';
import { ShowCardPage } from '../../pages/card/show';

@Component({
  selector: 'new-card-page',
  templateUrl: 'new.html'
})
export class NewCardPage {
  loading: Loading;
  @Input() item: any;
  valid: boolean = false;
  // @Input() item: {
  //   id?: number,
  //   founder_id: number,
  //   pitch: string,
  //   who: string,
  //   // location: boolean,
  //   // lat: number,
  //   // lon: number,
  //   anonymous: boolean,
  //   challenge: string,
  //   challenge_details: string,
  //   stage: number,
  //   networks: any
  // };
  private industries = globs.INDUSTRIES;
  private challenges = globs.SKILLS;
  private stages = globs.STAGES;
  private networks = globs.NETWORKS;

  constructor(public navCtrl: NavController, public navParams: NavParams, private card: CardProvider, private auth: AuthProvider, private loadingCtrl: LoadingController) {
    // If we navigated to this page, we will have an item available as a nav param
    this.item = {
    	industry: null,
    	pitch: '',
      who: '',
      location: true,
      // lat: 0,
      // lon: 0,
    	anonymous: false,
      challenge: null,
      challenge_details: '',
    	stage: 0,
      networks: 0,
      founder_id: this.auth.currentUser.id,
    };
  }

  submitCard(){
    this.showLoading();
    this.card.post(this.item).subscribe(
      success => this.navCtrl.push(ShowCardPage, {item: success}),
      error => console.log(error)
    );
  }

  showLoading() {
    this.loading = this.loadingCtrl.create({
      content: 'Please wait...',
      dismissOnPageChange: true
    });
    this.loading.present();
  }
}
