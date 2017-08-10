import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { CardProvider } from '../../providers/card/card';
import { AuthProvider } from '../../providers/auth/auth';
import { NewCardPage } from '../../pages/card/new';
import { ShowMessagePage } from '../../pages/messages/show';

@Component({
  selector: 'show-card-page',
  templateUrl: 'show.html'
})
export class ShowCardPage {
  item: any;
  founder: boolean = false;
  callback: any;

  constructor(public navCtrl: NavController, public navParams: NavParams, private card: CardProvider, private auth: AuthProvider) {
    this.callback = this.navParams.get('callback');
    this.item = navParams.get('item');
    if(this.item.founder_id == this.auth.currentUser.id) this.founder = true;
  }

  iterateTapped = () => {
    let handleCallback = (_params) => {
      return new Promise((resolve, reject) => {
        this.callback(_params).then(()=>{
          resolve();
         });
      });
    }

    this.navCtrl.push(NewCardPage, {
      item: this.item,
      callback: handleCallback
    });
  }

  goToMessage(){
    this.navCtrl.push(ShowMessagePage, {
      item: {
        id: null,
        card_id: this.item.id,
        messages: [],
        other: {
          id: this.item.founder_id
        }
      }
    }); 
  }

  doRefresh (e) {
  	setTimeout(function(){
  		e.complete();
  	}, 1000);
  	// this.service.getCard()
  	// .subscribe(
  	// 	data => this.item = data.results,
  	// 	err => console.log(err),
  	// 	() => e.complete()
  	// );
  }
}
